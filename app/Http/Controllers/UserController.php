<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    // Define protected administrator roles
    private $protectedRoles = ['administrator'];

    public function __construct()
    {
        $this->middleware('permission:users.show')->only('index', 'show');
        $this->middleware('permission:users.create')->only('create');
        $this->middleware('permission:users.edit')->only('edit');
        $this->middleware('permission:users.delete')->only('destroy');
    }

    /**
     * Check if current user is administrator
     */
    private function isAdministrator()
    {
        return auth()->user()->hasRole('administrator');
    }

    /**
     * Check if role is protected (administrator role)
     */
    private function isProtectedRole($roleName)
    {
        return in_array($roleName, $this->protectedRoles);
    }

    /**
     * Validate administrator role assignment
     */
    private function validateAdministratorRoleAssignment($requestedRoles)
    {
        if (!$this->isAdministrator()) {
            foreach ($requestedRoles as $roleName) {
                if ($this->isProtectedRole($roleName)) {
                    throw new \Exception('Only administrators can assign administrator roles to users.');
                }
            }
        }
    }

    public function index()
    {
        $title = 'Users';
        $subtitle = 'List of Users';
        $roles = Role::orderBy('name', 'asc')->get();
        $stats = [
            'users' => User::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
        ];
        $rolesSummary = Role::withCount('users', 'permissions')->orderBy('name', 'asc')->get();
        $permissionsSummary = Permission::withCount('roles')->orderBy('name', 'asc')->get();
        return view('users.index', compact('title', 'subtitle', 'roles', 'stats', 'rolesSummary', 'permissionsSummary'));
    }

    public function getUserDetails($id)
    {
        $user = User::with(['roles'])->findOrFail($id);
        $permissions = $user->getAllPermissions()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return response()->json([
            'user' => $user,
            'permissions' => $permissions,
        ]);
    }

    public function getUsers(Request $request)
    {
        $users = User::with('roles')->orderBy('name', 'asc');
        $roles = Role::orderBy('name', 'asc')->get();

        return datatables()->of($users)
            ->addIndexColumn()
            ->addColumn('name', function ($model) {
                return $model->name;
            })
            ->addColumn('email', function ($model) {
                return $model->email;
            })
            ->addColumn('roles', function ($model) {
                $roles = $model->roles->pluck('name');
                $html = '';
                foreach ($roles as $role) {
                    $badgeClass = in_array($role, ['administrator']) ? 'badge-danger' : 'badge-primary';
                    $html .= '<span class="badge ' . $badgeClass . ' mr-1">' . $role . '</span>';
                }
                return $html;
            })
            ->addColumn('user_status', function ($model) {
                $statusClass = $model->user_status == '1' ? 'badge-success' : 'badge-danger';
                $statusText = $model->user_status == '1' ? 'Active' : 'Inactive';
                return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
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
        $roles = Role::with('permissions')->orderBy('name', 'asc')->get();
        $title = 'Create User';
        return view('users.create', compact('roles', 'title'));
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

            // Validate administrator role assignment
            try {
                $this->validateAdministratorRoleAssignment($request->roles);
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('toast_error', $e->getMessage())
                    ->withInput();
            }

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
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::with('permissions')->orderBy('name', 'asc')->get();
        // Permissions yang didapat user (dari role dan direct)
        $permissions = $user->getAllPermissions();
        $title = 'User Details';
        return view('users.show', compact('user', 'roles', 'permissions', 'title'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::with('permissions')->orderBy('name', 'asc')->get();
        $userRoleNames = $user->roles->pluck('name')->toArray();
        $title = 'Edit User';
        return view('users.edit', compact('user', 'roles', 'userRoleNames', 'title'));
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

            // Validate administrator role assignment
            try {
                $this->validateAdministratorRoleAssignment($request->roles);
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('toast_error', $e->getMessage())
                    ->withInput();
            }

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

            // Check if trying to delete a user with administrator role
            if ($user->hasRole('administrator') && !$this->isAdministrator()) {
                return redirect()->back()
                    ->with('toast_error', 'Only administrators can delete users with administrator roles.');
            }

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

    /**
     * Get users list for approval actions
     */
    public function getUsersList(): JsonResponse
    {
        try {
            $users = User::select('id', 'name', 'email')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users'
            ], 500);
        }
    }
}
