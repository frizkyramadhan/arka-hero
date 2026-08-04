<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FuelWorkflowPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $officePermissions = [
            'fuel-records.verify',
            'fuel-claims.show',
            'fuel-claims.create',
            'fuel-claims.edit',
            'fuel-claims.delete',
            'fuel-claims.ready',
        ];

        $personalPermissions = [
            'personal.fuel.view-own',
            'personal.fuel.create-own',
            'personal.fuel.edit-own',
        ];

        foreach (array_merge($officePermissions, $personalPermissions) as $permission) {
            Permission::findOrCreate($permission);
        }

        $administratorRole = Role::findByName('administrator');
        if ($administratorRole) {
            $administratorRole->givePermissionTo(array_merge($officePermissions, $personalPermissions));
        }

        $vehicleUserRole = Role::findOrCreate('vehicle-user');
        $vehicleUserRole->givePermissionTo($personalPermissions);

        $this->command?->info('Fuel workflow permissions seeded (including vehicle-user).');
    }
}
