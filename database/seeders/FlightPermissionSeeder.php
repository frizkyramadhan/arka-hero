<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FlightPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Flight Management
        $permissions = [
            // Flight Requests Management
            'flight-requests.show',
            'flight-requests.create',
            'flight-requests.edit',
            'flight-requests.delete',

            // Flight Request Issuances (LG - Letter of Guarantee)
            'flight-issuances.show',
            'flight-issuances.create',
            'flight-issuances.edit',
            'flight-issuances.delete',

            // Business Partners Management
            'business-partners.show',
            'business-partners.create',
            'business-partners.edit',
            'business-partners.delete',

            // Personal Flight Requests (User Self-Service)
            'personal.flight.view-own',
            'personal.flight.create-own',
            'personal.flight.edit-own',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Assign permissions to existing roles

        // Administrator Role - gets all permissions
        $administratorRole = Role::where('name', 'administrator')->first();
        if ($administratorRole) {
            $administratorRole->givePermissionTo($permissions);
            $this->command->info('Flight permissions assigned to administrator role.');
        } else {
            $this->command->warn('Administrator role not found. Please run RoleAndPermissionSeeder first.');
        }

        $this->command->info('Flight Management permissions seeded successfully!');
        $this->command->info('Total permissions created: ' . count($permissions));
    }
}
