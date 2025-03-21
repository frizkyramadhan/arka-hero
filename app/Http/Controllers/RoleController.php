<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.show')->only('index', 'getRoles');
        $this->middleware('permission:roles.create')->only('create');
        $this->middleware('permission:roles.edit')->only('edit');
        $this->middleware('permission:roles.delete')->only('destroy');
    }

    public function index()
    {
        $title = 'Roles';
        $subtitle = 'List of Roles';

        return view('roles.index', compact('title', 'subtitle'));
    }

    public function getRoles(Request $request)
    {
        $roles = Role::with('permissions')->orderBy('name', 'asc');

        return datatables()->of($roles)
            ->addIndexColumn()
            ->addColumn('name', function ($role) {
                return $role->name;
            })
            ->addColumn('permissions', function ($role) {
                $permissions = $role->permissions->sortBy('name')->pluck('name');
                $html = '';

                // Show first 5 permissions as badges
                $count = 0;
                foreach ($permissions as $permission) {
                    if ($count < 5) {
                        $html .= '<span class="badge badge-info mr-1">' . $permission . '</span>';
                    }
                    $count++;
                }

                // If more than 5 permissions, show more+ badge with tooltip
                if ($count > 5) {
                    $remaining = $permissions->slice(5);
                    $tooltip = $remaining->map(function ($permission) {
                        return "• " . $permission;
                    })->implode("\n");
                    $html .= '<span class="badge badge-secondary" data-toggle="tooltip" data-html="true" title="' . e($tooltip) . '">+' . ($count - 5) . ' more</span>';
                }

                return $html;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('name', 'LIKE', "%$search%")
                            ->orWhereHas('permissions', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            });
                    });
                }
            })
            ->addColumn('action', 'roles.action')
            ->rawColumns(['action', 'permissions'])
            ->toJson();
    }

    public function create()
    {
        $title = 'Roles';
        $subtitle = 'Add Role';
        $permissions = Permission::orderBy('name', 'asc')->get();

        return view('roles.create', compact('title', 'subtitle', 'permissions'));
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|unique:roles,name',
                'permissions' => 'required',
            ], [
                'name.required' => 'Role name is required',
                'name.unique' => 'Role name already exists',
                'permissions.required' => 'Please select at least one permission'
            ]);

            DB::beginTransaction();

            $role = Role::create(['name' => $request->name]);
            $role->syncPermissions($request->permissions);

            DB::commit();

            return redirect('roles')->with('toast_success', 'Role added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to add role. Please try again.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $title = 'Roles';
        $subtitle = 'Edit Role';
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name', 'asc')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('title', 'subtitle', 'role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required|unique:roles,name,' . $id,
                'permissions' => 'required',
            ], [
                'name.required' => 'Role name is required',
                'name.unique' => 'Role name already exists',
                'permissions.required' => 'Please select at least one permission'
            ]);

            DB::beginTransaction();

            $role = Role::findOrFail($id);
            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->permissions);

            DB::commit();

            return redirect('roles')->with('toast_success', 'Role updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'Role not found.')
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to update role. Please try again.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Cek apakah role sedang digunakan
        if ($role->users()->count() > 0) {
            return redirect('roles')->with('toast_error', 'Role cannot be deleted because it is being used by users');
        }

        $role->delete();
        return redirect('roles')->with('toast_success', 'Role deleted successfully');
    }
}
