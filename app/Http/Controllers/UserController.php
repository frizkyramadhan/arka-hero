<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Department;
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
        $projects = Project::where('project_status', 1)->orderBy('project_code', 'asc')->get();
        $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();
        $employees = Employee::whereDoesntHave('user')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', 1)
            ->select('employees.*', 'administrations.nik')
            ->orderBy('administrations.nik', 'asc')
            ->get();
        $stats = [
            'users' => User::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
        ];
        $rolesSummary = Role::withCount('users', 'permissions')->orderBy('name', 'asc')->get();
        $permissionsSummary = Permission::withCount('roles')->orderBy('name', 'asc')->get();
        return view('users.index', compact('title', 'subtitle', 'roles', 'projects', 'departments', 'employees', 'stats', 'rolesSummary', 'permissionsSummary'));
    }

    public function getUserDetails($id)
    {
        $user = User::with(['roles', 'projects', 'departments'])->findOrFail($id);
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
        $users = User::with(['roles', 'projects', 'departments', 'employee'])->orderBy('name', 'asc');
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
                if ($roles->isEmpty()) {
                    return '<span class="text-muted">-</span>';
                }
                $html = '<div class="d-flex flex-column">';
                foreach ($roles as $role) {
                    $badgeClass = in_array($role, ['administrator']) ? 'badge-danger' : 'badge-primary';
                    $html .= '<span class="badge ' . $badgeClass . ' mb-1 d-inline-block" style="width: fit-content;">' . $role . '</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('projects', function ($model) {
                $projects = $model->projects->pluck('project_name', 'project_code');
                if ($projects->isEmpty()) {
                    return '<span class="text-muted">-</span>';
                }
                $html = '<div class="d-flex flex-column">';
                foreach ($projects as $projectCode => $projectName) {
                    $html .= '<span class="badge badge-info mb-1 d-inline-block" style="width: fit-content;">' . $projectCode . ' : ' . $projectName . '</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('departments', function ($model) {
                $departments = $model->departments->pluck('department_name');
                if ($departments->isEmpty()) {
                    return '<span class="text-muted">-</span>';
                }
                $html = '<div class="d-flex flex-column">';
                foreach ($departments as $department) {
                    $html .= '<span class="badge badge-warning mb-1 d-inline-block" style="width: fit-content;">' . $department . '</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('employee', function ($model) {
                if ($model->employee) {
                    return '<span class="badge badge-success">' . $model->employee->fullname . '</span>';
                }
                return '<span class="badge badge-secondary">No Employee</span>';
            })
            ->addColumn('user_status', function ($model) {
                $statusClass = $model->user_status == '1' ? 'badge-success' : 'badge-danger';
                $statusText = $model->user_status == '1' ? 'Active' : 'Inactive';
                return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
            })
            ->filter(function ($instance) use ($request) {
                // General search (from DataTables search box)
                $searchValue = null;
                if ($request->has('search')) {
                    $search = $request->get('search');
                    if (is_array($search) && !empty($search['value'])) {
                        $searchValue = $search['value'];
                    } elseif (is_string($search) && !empty($search)) {
                        $searchValue = $search;
                    }
                }

                if (!empty($searchValue)) {
                    $instance->where(function ($w) use ($searchValue) {
                        $w->orWhere('name', 'LIKE', "%$searchValue%")
                            ->orWhere('email', 'LIKE', "%$searchValue%")
                            ->orWhereHas('projects', function ($q) use ($searchValue) {
                                $q->where('project_code', 'LIKE', "%$searchValue%")
                                    ->orWhere('project_name', 'LIKE', "%$searchValue%");
                            })
                            ->orWhereHas('departments', function ($q) use ($searchValue) {
                                $q->where('department_name', 'LIKE', "%$searchValue%")
                                    ->orWhere('slug', 'LIKE', "%$searchValue%");
                            })
                            ->orWhereHas('roles', function ($q) use ($searchValue) {
                                $q->where('name', 'LIKE', "%$searchValue%");
                            });
                    });
                }

                // Filter by name
                if (!empty($request->get('filter_name'))) {
                    $instance->where('name', 'LIKE', '%' . $request->get('filter_name') . '%');
                }

                // Filter by email
                if (!empty($request->get('filter_email'))) {
                    $instance->where('email', 'LIKE', '%' . $request->get('filter_email') . '%');
                }

                // Filter by employee
                if (!empty($request->get('filter_employee'))) {
                    $instance->whereHas('employee', function ($q) use ($request) {
                        $q->where('fullname', 'LIKE', '%' . $request->get('filter_employee') . '%');
                    });
                }

                // Filter by project
                if (!empty($request->get('filter_project'))) {
                    $instance->whereHas('projects', function ($q) use ($request) {
                        $q->where('projects.id', $request->get('filter_project'));
                    });
                }

                // Filter by department
                if (!empty($request->get('filter_department'))) {
                    $instance->whereHas('departments', function ($q) use ($request) {
                        $q->where('departments.id', $request->get('filter_department'));
                    });
                }

                // Filter by role
                if (!empty($request->get('filter_role'))) {
                    $instance->whereHas('roles', function ($q) use ($request) {
                        $q->where('roles.id', $request->get('filter_role'));
                    });
                }

                // Filter by status
                if ($request->get('filter_status') !== null && $request->get('filter_status') !== '') {
                    $instance->where('user_status', $request->get('filter_status'));
                }
            })
            ->addColumn('action', function ($model) use ($roles) {
                return view('users.action', compact('model', 'roles'))->render();
            })
            ->rawColumns(['roles', 'projects', 'departments', 'employee', 'user_status', 'action'])
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
        $projects = Project::where('project_status', 1)->orderBy('project_code', 'asc')->get();
        $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();
        $employees = Employee::whereDoesntHave('user')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', 1)
            ->select('employees.*', 'administrations.nik')
            ->orderBy('administrations.nik', 'asc')
            ->get();
        $title = 'Create User';
        return view('users.create', compact('roles', 'projects', 'departments', 'employees', 'title'));
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
                'username' => 'required|unique:users|alpha_dash|min:3|max:255',
                'email' => 'nullable|email:dns|unique:users|ends_with:@arka.co.id',
                'password' => 'required|min:5',
                'user_status' => 'required',
                'employee_id' => 'nullable|exists:employees,id|unique:users,employee_id',
                'roles' => 'required|array|min:1',
                'projects' => 'nullable|array',
                'departments' => 'nullable|array',
            ], [
                'name.required' => 'Name is required',
                'username.required' => 'Username is required',
                'username.unique' => 'Username already exists',
                'username.alpha_dash' => 'Username can only contain letters, numbers, dashes and underscores',
                'username.min' => 'Username must be at least 3 characters',
                'email.email' => 'Email must be a valid email address',
                'email.unique' => 'Email already exists',
                'email.ends_with' => 'Email must end with @arka.co.id',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 5 characters',
                'employee_id.exists' => 'Selected employee does not exist',
                'employee_id.unique' => 'This employee already has a user account',
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
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'employee_id' => $request->employee_id,
                'user_status' => $request->user_status
            ]);

            // Ensure 'user' role is always assigned
            $roles = $request->roles;
            $userRole = Role::firstOrCreate(['name' => 'user']);

            // Check if user role is already in the roles array (by ID or name)
            $hasUserRole = false;
            foreach ($roles as $role) {
                if ($role == $userRole->id || $role == 'user' || $role == $userRole->name) {
                    $hasUserRole = true;
                    break;
                }
            }

            // Add user role if not already present
            if (!$hasUserRole) {
                // Check if roles array contains IDs or names
                if (is_numeric($roles[0] ?? null)) {
                    $roles[] = $userRole->id;
                } else {
                    $roles[] = 'user';
                }
            }

            $user->syncRoles($roles);

            // Sync projects and departments
            if ($request->has('projects')) {
                $user->projects()->sync($request->projects);
            }

            if ($request->has('departments')) {
                $user->departments()->sync($request->departments);
            }

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
        $user = User::with(['roles', 'projects', 'departments', 'employee.administrations.position', 'employee.administrations.level'])->findOrFail($id);
        $roles = Role::with('permissions')->orderBy('name', 'asc')->get();
        $projects = Project::where('project_status', 1)->orderBy('project_code', 'asc')->get();
        $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();
        // Permissions yang didapat user (dari role dan direct)
        $permissions = $user->getAllPermissions();
        $title = 'User Details';
        return view('users.show', compact('user', 'roles', 'projects', 'departments', 'permissions', 'title'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::with(['roles', 'projects', 'departments', 'employee'])->findOrFail($id);
        $roles = Role::with('permissions')->orderBy('name', 'asc')->get();
        $projects = Project::where('project_status', 1)->orderBy('project_code', 'asc')->get();
        $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();
        $employees = Employee::whereDoesntHave('user')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.is_active', 1)
            ->orWhere('employees.id', $user->employee_id)
            ->select('employees.*', 'administrations.nik')
            ->orderBy('administrations.nik', 'asc')
            ->get();
        $userRoleNames = $user->roles->pluck('name')->toArray();
        $userProjectIds = $user->projects->pluck('id')->toArray();
        $userDepartmentIds = $user->departments->pluck('id')->toArray();
        $title = 'Edit User';
        return view('users.edit', compact('user', 'roles', 'projects', 'departments', 'employees', 'userRoleNames', 'userProjectIds', 'userDepartmentIds', 'title'));
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
                'username' => 'required|alpha_dash|min:3|max:255|unique:users,username,' . $id,
                'email' => 'nullable|email:dns|ends_with:@arka.co.id|unique:users,email,' . $id,
                'employee_id' => 'nullable|exists:employees,id|unique:users,employee_id,' . $id,
                'user_status' => 'required',
                'roles' => 'required|array|min:1',
                'user_status' => 'required',
                'projects' => 'nullable|array',
                'departments' => 'nullable|array',
            ], [
                'name.required' => 'Name is required',
                'username.required' => 'Username is required',
                'username.unique' => 'Username already exists',
                'username.alpha_dash' => 'Username can only contain letters, numbers, dashes and underscores',
                'username.min' => 'Username must be at least 3 characters',
                'email.email' => 'Email must be a valid email address',
                'email.unique' => 'Email already exists',
                'email.ends_with' => 'Email must end with @arka.co.id',
                'employee_id.exists' => 'Selected employee does not exist',
                'employee_id.unique' => 'This employee already has a user account',
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

            // Sync projects and departments
            if ($request->has('projects')) {
                $user->projects()->sync($request->projects);
            } else {
                $user->projects()->detach();
            }

            if ($request->has('departments')) {
                $user->departments()->sync($request->departments);
            } else {
                $user->departments()->detach();
            }

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
