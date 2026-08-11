<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VehicleAssignmentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'vehicle-assignments.show',
            'vehicle-assignments.create',
            'vehicle-assignments.edit',
            'vehicle-assignments.delete',
            'vehicle-assignments.issue',
            'vehicle-assignments.print',
            'vehicle-assignments.cancel',
            'personal.vehicle-assignments.view-own',
            'personal.vehicle-assignments.update-trip',
            'personal.vehicle-assignments.close-own',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $administratorRole = Role::findByName('administrator');
        if ($administratorRole) {
            $administratorRole->givePermissionTo($permissions);
        }

        // $userRole = Role::findByName('user');
        // if ($userRole) {
        //     $userRole->givePermissionTo([
        //         'personal.vehicle-assignments.view-own',
        //         'personal.vehicle-assignments.update-trip',
        //         'personal.vehicle-assignments.close-own',
        //     ]);
        // }

        $this->command?->info('Vehicle Assignment (FOA) permissions seeded.');
    }
}
