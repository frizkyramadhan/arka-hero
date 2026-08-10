<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FuelBotPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'fuel-bot-subscribers.show',
            'fuel-bot-subscribers.create',
            'fuel-bot-subscribers.edit',
            'fuel-bot-subscribers.delete',
            'fuel-bot-logs.show',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $administratorRole = Role::findByName('administrator');
        if ($administratorRole) {
            $administratorRole->givePermissionTo($permissions);
        }

        $this->command?->info('Fuel bot subscriber permissions seeded.');
    }
}
