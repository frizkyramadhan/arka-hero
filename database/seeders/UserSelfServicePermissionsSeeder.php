<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSelfServicePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder adds self-service permissions to the 'user' role
     * to enable employee self-service features
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Personal leave management permissions
        $leavePermissions = [
            'personal.leave.view-own',
            'personal.leave.create-own',
            'personal.leave.edit-own',
            'personal.leave.cancel-own',
            'personal.leave.view-entitlements',
        ];

        // Personal official travel permissions
        $officialTravelPermissions = [
            'personal.official-travel.view-own',
            'personal.official-travel.create-own',
            'personal.official-travel.edit-own',
            'personal.official-travel.cancel-own',
        ];

        // Personal recruitment request permissions (FPTK)
        $recruitmentPermissions = [
            'personal.recruitment.view-own',
            'personal.recruitment.create-own',
            'personal.recruitment.edit-own',
            'personal.recruitment.cancel-own',
        ];

        // Personal approval permissions (for users who are approvers)
        $approvalPermissions = [
            'personal.approval.view-pending',
            'personal.approval.process',
        ];

        // Personal profile permissions
        $profilePermissions = [
            'personal.profile.view-own',
            'personal.profile.update-own',
            'personal.profile.change-password',
        ];

        // Personal dashboard permissions
        $dashboardPermissions = [
            'personal.dashboard.view',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $leavePermissions,
            $officialTravelPermissions,
            $recruitmentPermissions,
            $approvalPermissions,
            $profilePermissions,
            $dashboardPermissions
        );

        // Create permissions if they don't exist
        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Find or create user role and assign permissions
        $userRole = Role::findOrCreate('user');
        $userRole->givePermissionTo($allPermissions);

        $this->command->info('User self-service permissions have been assigned to the "user" role.');
        $this->command->info('Total permissions added: ' . count($allPermissions));
    }
}
