<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // Define protected administrator roles
    private $protectedRoles = ['administrator'];

    public function __construct()
    {
        $this->middleware('permission:roles.show')->only('index', 'getRoles');
        $this->middleware('permission:roles.create')->only('create');
        $this->middleware('permission:roles.edit')->only('edit');
        $this->middleware('permission:roles.delete')->only('destroy');
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
     * Validate administrator role access
     */
    private function validateAdministratorAccess($roleName, $action = 'modify')
    {
        if ($this->isProtectedRole($roleName) && !$this->isAdministrator()) {
            throw new \Exception("Only administrators can {$action} administrator roles.");
        }
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

        // Filter permissions for non-administrator users
        if (!$this->isAdministrator()) {
            $permissions = $permissions->filter(function ($permission) {
                return !str_starts_with($permission->name, 'permissions.');
            });
        }

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

            // Check if trying to create administrator role
            if ($this->isProtectedRole($request->name) && !$this->isAdministrator()) {
                return redirect()->back()
                    ->with('toast_error', 'Only administrators can create administrator roles.')
                    ->withInput();
            }

            // Validate permission assignments for non-administrators
            if (!$this->isAdministrator()) {
                foreach ($request->permissions as $permissionName) {
                    if (str_starts_with($permissionName, 'permissions.')) {
                        return redirect()->back()
                            ->with('toast_error', 'Only administrators can assign permission management roles.')
                            ->withInput();
                    }
                }
            }

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

        // Check if trying to edit administrator role
        if ($this->isProtectedRole($role->name) && !$this->isAdministrator()) {
            return redirect('roles')->with('toast_error', 'Only administrators can edit administrator roles.');
        }

        $permissions = Permission::orderBy('name', 'asc')->get();

        // Filter permissions for non-administrator users
        if (!$this->isAdministrator()) {
            $permissions = $permissions->filter(function ($permission) {
                return !str_starts_with($permission->name, 'permissions.');
            });
        }

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

            // Check if trying to modify administrator role
            if ($this->isProtectedRole($role->name) && !$this->isAdministrator()) {
                return redirect()->back()
                    ->with('toast_error', 'Only administrators can modify administrator roles.')
                    ->withInput();
            }

            // Check if trying to rename to administrator role
            if ($this->isProtectedRole($request->name) && !$this->isAdministrator()) {
                return redirect()->back()
                    ->with('toast_error', 'Only administrators can create administrator roles.')
                    ->withInput();
            }

            // Validate permission assignments for non-administrators
            if (!$this->isAdministrator()) {
                foreach ($request->permissions as $permissionName) {
                    if (str_starts_with($permissionName, 'permissions.')) {
                        return redirect()->back()
                            ->with('toast_error', 'Only administrators can assign permission management roles.')
                            ->withInput();
                    }
                }
            }

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
        try {
            $role = Role::findOrFail($id);

            // Check if trying to delete administrator role
            if ($this->isProtectedRole($role->name) && !$this->isAdministrator()) {
                return redirect('roles')->with('toast_error', 'Only administrators can delete administrator roles.');
            }

            // Check if role is being used
            if ($role->users()->count() > 0) {
                return redirect('roles')->with('toast_error', 'Role cannot be deleted because it is being used by users');
            }

            $role->delete();
            return redirect('roles')->with('toast_success', 'Role deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('roles')->with('toast_error', 'Role not found.');
        } catch (\Exception $e) {
            return redirect('roles')->with('toast_error', 'Failed to delete role. Please try again.');
        }
    }
}
