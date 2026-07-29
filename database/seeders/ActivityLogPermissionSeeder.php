<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ActivityLogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::findOrCreate('activity-logs.show');

        $administratorRole = Role::findByName('administrator');
        if ($administratorRole) {
            $administratorRole->givePermissionTo('activity-logs.show');
        }

        $this->command?->info('Activity log permissions seeded.');
    }
}
