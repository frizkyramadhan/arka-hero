<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RecruitmentRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create recruitment permissions
        $this->createRecruitmentPermissions();

        // Assign recruitment permissions to administrator
        $this->assignRecruitmentPermissionsToAdministrator();

        $this->command->info('Recruitment roles and permissions created successfully!');
    }

    /**
     * Create recruitment permissions
     */
    private function createRecruitmentPermissions()
    {
        $permissions = [
            // FPTK Management
            'recruitment-requests.show',
            'recruitment-requests.create',
            'recruitment-requests.edit',
            'recruitment-requests.delete',
            'recruitment-requests.acknowledge',
            'recruitment-requests.approve',

            // Candidate Management
            'recruitment-candidates.show',
            'recruitment-candidates.create',
            'recruitment-candidates.edit',
            'recruitment-candidates.delete',

            // Session Management
            'recruitment-sessions.show',
            'recruitment-sessions.create',
            'recruitment-sessions.edit',
            'recruitment-sessions.advance',
            'recruitment-sessions.reject',
            'recruitment-sessions.cancel',

            // Assessment Management
            'recruitment-assessments.show',
            'recruitment-assessments.create',
            'recruitment-assessments.edit',
            'recruitment-assessments.conduct',
            'recruitment-assessments.score',

            // Interview Management
            'recruitment-interviews.show',
            'recruitment-interviews.schedule',
            'recruitment-interviews.conduct',
            'recruitment-interviews.score',

            // Offer Management
            'recruitment-offers.show',
            'recruitment-offers.create',
            'recruitment-offers.edit',
            'recruitment-offers.send',
            'recruitment-offers.withdraw',

            // Document Management
            'recruitment-documents.show',
            'recruitment-documents.upload',
            'recruitment-documents.download',
            'recruitment-documents.verify',

            // Reports
            'recruitment-reports.show',
            'recruitment-reports.export',
            'recruitment-reports.analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $this->command->info('Recruitment permissions created.');
    }

    /**
     * Assign recruitment permissions to administrator role
     */
    private function assignRecruitmentPermissionsToAdministrator()
    {
        // Find administrator role
        $administratorRole = Role::where('name', 'administrator')->first();

        if ($administratorRole) {
            // Get all recruitment permissions
            $recruitmentPermissions = Permission::where('name', 'LIKE', 'recruitment-%')->get();

            // Assign to administrator
            $administratorRole->givePermissionTo($recruitmentPermissions);

            $this->command->info('Recruitment permissions assigned to administrator role.');
        } else {
            $this->command->warn('Administrator role not found. Please run RoleAndPermissionSeeder first.');
        }
    }
}
