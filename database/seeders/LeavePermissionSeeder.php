<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LeavePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Leave Management
        $permissions = [
            // Leave Types Management
            'leave-types.show',
            'leave-types.create',
            'leave-types.edit',
            'leave-types.delete',

            // Leave Entitlements Management
            'leave-entitlements.show',
            'leave-entitlements.create',
            'leave-entitlements.edit',
            'leave-entitlements.delete',

            // Leave Requests Management
            'leave-requests.show',
            'leave-requests.create',
            'leave-requests.edit',
            'leave-requests.delete',

            // Leave Reports Management
            'leave-reports.show',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Assign permissions to existing roles

        // Administrator Role - gets all permissions automatically
        $administratorRole = Role::findByName('administrator');
        if ($administratorRole) {
            $administratorRole->givePermissionTo($permissions);
        }

        // HR Staff Role
        $hrStaffRole = Role::findByName('hr-staff');
        if ($hrStaffRole) {
            $hrStaffRole->givePermissionTo([
                'leave-types.show',
                'leave-types.create',
                'leave-types.edit',
                'leave-entitlements.show',
                'leave-entitlements.create',
                'leave-entitlements.edit',
                'leave-requests.show',
                'leave-requests.create',
                'leave-requests.edit',
                'leave-reports.show',
            ]);
        }

        // HR Supervisor Role
        $hrSupervisorRole = Role::findByName('hr-supervisor');
        if ($hrSupervisorRole) {
            $hrSupervisorRole->givePermissionTo([
                'leave-types.show',
                'leave-types.create',
                'leave-types.edit',
                'leave-types.delete',
                'leave-entitlements.show',
                'leave-entitlements.create',
                'leave-entitlements.edit',
                'leave-entitlements.delete',
                'leave-requests.show',
                'leave-requests.create',
                'leave-requests.edit',
                'leave-requests.delete',
                'leave-reports.show',
            ]);
        }

        // HR Manager Role
        $hrManagerRole = Role::findByName('hr-manager');
        if ($hrManagerRole) {
            $hrManagerRole->givePermissionTo([
                'leave-types.show',
                'leave-types.create',
                'leave-types.edit',
                'leave-types.delete',
                'leave-entitlements.show',
                'leave-entitlements.create',
                'leave-entitlements.edit',
                'leave-entitlements.delete',
                'leave-requests.show',
                'leave-requests.create',
                'leave-requests.edit',
                'leave-requests.delete',
                'leave-reports.show',
            ]);
        }

        // Project Manager Role - limited access
        $projectManagerRole = Role::findByName('project-manager');
        if ($projectManagerRole) {
            $projectManagerRole->givePermissionTo([
                'leave-requests.show',
            ]);
        }

        // Division Manager Role - limited access
        $divManagerRole = Role::findByName('div-manager');
        if ($divManagerRole) {
            $divManagerRole->givePermissionTo([
                'leave-requests.show',
            ]);
        }

        // User Role - basic access
        $userRole = Role::findByName('user');
        if ($userRole) {
            $userRole->givePermissionTo([
                'leave-requests.show',
                'leave-requests.create',
            ]);
        }

        $this->command->info('Leave permissions seeded successfully!');
    }
}
