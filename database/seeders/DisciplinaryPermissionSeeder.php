<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DisciplinaryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'employee-disciplinaries.show',
            'employee-disciplinaries.create',
            'employee-disciplinaries.edit',
            'employee-disciplinaries.delete',
            'disciplinary-criteria.show',
            'disciplinary-criteria.create',
            'disciplinary-criteria.edit',
            'disciplinary-criteria.delete',
        ];

        $personalPermissions = [
            'personal.disciplinary.view-own',
        ];

        foreach (array_merge($permissions, $personalPermissions) as $permission) {
            Permission::findOrCreate($permission);
        }

        $viewOnly = [
            'employee-disciplinaries.show',
            'disciplinary-criteria.show',
        ];

        $fullAccess = $permissions;

        $administratorRole = Role::findByName('administrator');
        if ($administratorRole) {
            $administratorRole->givePermissionTo(array_merge($fullAccess, $personalPermissions));
        }

        foreach (['hr-staff', 'hr-supervisor', 'hr-manager'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($fullAccess);
            }
        }

        // Legacy HO staff role name from base seeder
        $hrStaff000h = Role::where('name', 'hr-staff-000h')->first();
        if ($hrStaff000h) {
            $hrStaff000h->givePermissionTo($fullAccess);
        }

        foreach (['project-manager', 'div-manager'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($viewOnly);
            }
        }

        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $userRole->givePermissionTo($personalPermissions);
        }

        $this->command?->info('Disciplinary (Pembinaan & SP) permissions seeded.');
    }
}
