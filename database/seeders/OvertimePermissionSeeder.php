<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OvertimePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // HR / back-office (sama pola dengan leave-requests.*, flight-requests.*)
        $hrPermissions = [
            'overtime-requests.create',
            'overtime-requests.edit',
            'overtime-requests.delete',
            'overtime-requests.show',
            'overtime-requests.finish',
        ];

        // Self-service karyawan (sama pola dengan personal.leave.*, personal.flight.*, personal.official-travel.*)
        $personalPermissions = [
            'personal.overtime.view-own',
            'personal.overtime.create-own',
            'personal.overtime.edit-own',
            'personal.overtime.cancel-own',
        ];

        $allPermissions = array_merge($hrPermissions, $personalPermissions);

        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $administratorRole = Role::findByName('administrator');
        if ($administratorRole) {
            $administratorRole->givePermissionTo($allPermissions);
        }

        foreach (['hr-staff', 'hr-supervisor', 'hr-manager'] as $roleName) {
            $role = Role::findByName($roleName);
            if ($role) {
                $role->givePermissionTo($hrPermissions);
            }
        }

        $userRole = Role::findByName('user');
        if ($userRole) {
            $userRole->givePermissionTo($personalPermissions);
        }

        $this->command->info('Overtime permissions seeded.');
    }
}
