<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permissions.show')->only('index', 'getPermissions');
        $this->middleware('permission:permissions.create')->only('create');
        $this->middleware('permission:permissions.edit')->only('edit');
        $this->middleware('permission:permissions.delete')->only('destroy');
    }

    public function index()
    {
        $title = 'Permissions';
        $subtitle = 'List of Permissions';

        return view('permissions.index', compact('title', 'subtitle'));
    }

    public function getPermissions(Request $request)
    {
        $permissions = Permission::orderBy('name', 'asc');

        return datatables()->of($permissions)
            ->addIndexColumn()
            ->addColumn('name', function ($permissions) {
                return $permissions->name;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('name', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'permissions.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create()
    {
        $title = 'Permissions';
        $subtitle = 'Add Permission';

        return view('permissions.create', compact('title', 'subtitle'));
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|unique:permissions,name',
            ], [
                'name.required' => 'Permission name is required',
                'name.unique' => 'Permission name already exists'
            ]);

            DB::beginTransaction();

            Permission::create(['name' => $request->name]);

            DB::commit();

            return redirect('permissions')->with('toast_success', 'Permission added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to add permission. Please try again.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $title = 'Permissions';
        $subtitle = 'Edit Permission';
        $permission = Permission::find($id);

        return view('permissions.edit', compact('title', 'subtitle', 'permission'));
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required|unique:permissions,name,' . $id,
            ], [
                'name.required' => 'Permission name is required',
                'name.unique' => 'Permission name already exists'
            ]);

            DB::beginTransaction();

            $permission = Permission::findOrFail($id);
            $permission->update(['name' => $request->name]);

            DB::commit();

            return redirect('permissions')->with('toast_success', 'Permission updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'Permission not found.')
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to update permission. Please try again.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return redirect('permissions')->with('toast_success', 'Permission deleted successfully');
    }
}
