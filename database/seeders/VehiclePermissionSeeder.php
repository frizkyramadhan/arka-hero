<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VehiclePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'vehicles.show',
            'vehicles.create',
            'vehicles.edit',
            'vehicles.delete',
            'vehicle-documents.show',
            'vehicle-documents.create',
            'vehicle-documents.edit',
            'vehicle-documents.delete',
            'fuel-records.show',
            'fuel-records.create',
            'fuel-records.edit',
            'fuel-records.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $administratorRole = Role::findByName('administrator');
        if ($administratorRole) {
            $administratorRole->givePermissionTo($permissions);
        }

        $this->command?->info('Vehicle (GAMMA) permissions seeded.');
    }
}
