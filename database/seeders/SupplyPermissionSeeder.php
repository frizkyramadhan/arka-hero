<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SupplyPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $adminPermissions = [
            'supplies.dashboard.show',
            'supplies.reports.show',
            'supplies.item-categories.show',
            'supplies.item-categories.create',
            'supplies.item-categories.edit',
            'supplies.item-categories.delete',
            'supplies.catalog.show',
            'supplies.catalog.create',
            'supplies.catalog.edit',
            'supplies.catalog.delete',
            'supplies.stock-in.show',
            'supplies.stock-in.create',
            'supplies.stock-in.edit',
            'supplies.stock-in.delete',
            'supplies.stock-out.show',
            'supplies.stock-out.create',
            'supplies.stock-out.edit',
            'supplies.stock-out.delete',
            'supplies.orders.show',
            'supplies.orders.create',
            'supplies.orders.edit',
            'supplies.orders.delete',
            'supplies.orders.close',
        ];

        $personalPermissions = [
            'personal.supplies.orders.view-own',
            'personal.supplies.orders.create-own',
            'personal.supplies.orders.edit-own',
            'personal.supplies.orders.cancel-own',
        ];

        $allPermissions = array_merge($adminPermissions, $personalPermissions);

        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findByName('administrator')?->givePermissionTo($allPermissions);

        // foreach (['hr-staff', 'hr-supervisor', 'hr-manager'] as $roleName) {
        //     Role::findByName($roleName)?->givePermissionTo($adminPermissions);
        // }

        Role::findByName('user')?->givePermissionTo($personalPermissions);

        $this->command?->info('Supplies permissions seeded.');
    }
}
