<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.show')->only('index', 'show');
        $this->middleware('permission:users.create')->only('create');
        $this->middleware('permission:users.edit')->only('edit');
        $this->middleware('permission:users.delete')->only('destroy');
    }

    public function index()
    {
        $title = 'Users';
        $subtitle = 'List of Users';
        $roles = Role::orderBy('name', 'asc')->get();

        return view('users.index', compact('title', 'subtitle', 'roles'));
    }

    public function getUsers(Request $request)
    {
        $users = User::with('roles')->orderBy('name', 'asc');
        $roles = Role::orderBy('name', 'asc')->get();

        return datatables()->of($users)
            ->addIndexColumn()
            ->addColumn('name', function ($user) {
                return $user->name;
            })
            ->addColumn('email', function ($user) {
                return $user->email;
            })
            ->addColumn('roles', function ($user) {
                $roles = $user->roles->pluck('name')->toArray();
                $html = '';

                // Show first 4 roles as badges
                $count = 0;
                foreach ($roles as $role) {
                    if ($count < 4) {
                        $html .= '<span class="badge badge-info mr-1">' . $role . '</span>';
                    }
                    $count++;
                }

                // If more than 4 roles, show more+ badge with tooltip
                if ($count > 4) {
                    $remaining = array_slice($roles, 4);
                    $tooltip = collect($remaining)->map(function ($role) {
                        return "• " . $role;
                    })->implode("\n");
                    $html .= '<span class="badge badge-secondary" data-toggle="tooltip" data-html="true" title="' . e($tooltip) . '">+' . ($count - 4) . ' more</span>';
                }

                return $html;
            })
            ->addColumn('user_status', function ($user) {
                if ($user->user_status == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($user->user_status == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('name', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%")
                            ->orWhereHas('roles', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            });
                    });
                }
            })
            ->addColumn('action', function ($model) use ($roles) {
                return view('users.action', compact('model', 'roles'))->render();
            })
            ->rawColumns(['roles', 'user_status', 'action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email:dns|unique:users|ends_with:@arka.co.id',
                'password' => 'required|min:5',
                'user_status' => 'required',
                'roles' => 'required|array|min:1',
            ], [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.unique' => 'Email already exists',
                'email.ends_with' => 'Email must end with @arka.co.id',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 5 characters',
                'roles.required' => 'Please select at least one role',
                'roles.min' => 'Please select at least one role'
            ]);

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_status' => $request->user_status
            ]);

            $user->syncRoles($request->roles);

            DB::commit();

            return redirect('users')->with('toast_success', 'User added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to add user. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email:dns|ends_with:@arka.co.id|unique:users,email,' . $id,
                'roles' => 'required|array|min:1',
                'user_status' => 'required'
            ], [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.unique' => 'Email already exists',
                'email.ends_with' => 'Email must end with @arka.co.id',
                'roles.required' => 'Please select at least one role',
                'roles.min' => 'Please select at least one role'
            ]);

            DB::beginTransaction();

            $input = $request->all();
            $user = User::findOrFail($id);

            if (!empty($input['password'])) {
                $this->validate($request, [
                    'password' => 'required|min:5'
                ], [
                    'password.min' => 'Password must be at least 5 characters'
                ]);
                $input['password'] = Hash::make($input['password']);
            } else {
                $input = Arr::except($input, array('password'));
            }

            $user->update($input);
            $user->syncRoles($request->roles);

            DB::commit();

            return redirect('users')->with('toast_success', 'User updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'User not found.')
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to update user. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $user->roles()->detach(); // Remove all roles first
            $user->delete();

            DB::commit();

            return redirect('users')->with('toast_success', 'User deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'User not found.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to delete user. Please try again.');
        }
    }
}
