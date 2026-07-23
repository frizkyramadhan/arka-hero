<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoomConsumptionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $adminPermissions = [
            'meeting-rooms.show',
            'meeting-rooms.create',
            'meeting-rooms.edit',
            'meeting-rooms.delete',
            'room-consumption-requests.show',
            'room-consumption-requests.create',
            'room-consumption-requests.edit',
            'room-consumption-requests.delete',
        ];

        $personalPermissions = [
            'personal.room-consumption.view-own',
            'personal.room-consumption.create-own',
            'personal.room-consumption.edit-own',
            'personal.room-consumption.cancel-own',
        ];

        $allPermissions = array_merge($adminPermissions, $personalPermissions);

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
                $role->givePermissionTo($adminPermissions);
            }
        }

        $userRole = Role::findByName('user');
        if ($userRole) {
            $userRole->givePermissionTo($personalPermissions);
        }

        $this->command->info('Room & Consumption permissions seeded.');
    }
}
