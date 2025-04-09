<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for CRUD operations
        $permissions = [
            // Dashboard
            'dashboard.show',

            // User Management
            'users.show',
            'users.create',
            'users.edit',
            'users.delete',

            // Role Management
            'roles.show',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Permission Management
            'permissions.show',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // Employee Management
            'employees.show',
            'employees.create',
            'employees.edit',
            'employees.delete',

            // Official Travel Management
            'official-travels.show',
            'official-travels.create',
            'official-travels.edit',
            'official-travels.delete',
            'official-travels.approve',
            'official-travels.recommend',
            'official-travels.edit-approval',
            'official-travels.edit-recommendation',

            // Master Data Management
            'master-data.show',
            'master-data.create',
            'master-data.edit',
            'master-data.delete',

            // Project Based Permissions
            'project.000h',
            'project.001h',
            'project.017c',
            'project.021c',
            'project.022c',
            'project.023c',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions

        // Administrator Role
        $administratorRole = Role::findOrCreate('administrator');
        $administratorRole->givePermissionTo(Permission::all());

        // HR Staff Role
        $hrStaffRole = Role::findOrCreate('hr-staff-000h');
        $hrStaffRole->givePermissionTo([
            'dashboard.show',
            'employees.show',
            'employees.create',
            'employees.edit',
            'official-travels.show',
            'official-travels.create',
            'official-travels.edit',
            'project.000h',
        ]);

        // HR Supervisor Role
        $hrSupervisorRole = Role::findOrCreate('hr-supervisor');
        $hrSupervisorRole->givePermissionTo([
            'dashboard.show',
            'employees.show',
            'employees.create',
            'employees.edit',
            'employees.delete',
            'official-travels.show',
            'official-travels.create',
            'official-travels.edit',
            'official-travels.delete',
            'official-travels.approve',
            'master-data.show',
            'master-data.create',
            'master-data.edit',
            'project.000h',
            'project.001h',
            'project.017c',
            'project.021c',
            'project.022c',
            'project.023c',
        ]);

        // HR Manager Role
        $hrManagerRole = Role::findOrCreate('hr-manager');
        $hrManagerRole->givePermissionTo([
            'dashboard.show',
            'employees.show',
            'employees.create',
            'employees.edit',
            'employees.delete',
            'official-travels.show',
            'official-travels.create',
            'official-travels.edit',
            'official-travels.delete',
            'official-travels.approve',
            'master-data.show',
            'master-data.create',
            'master-data.edit',
            'project.000h',
            'project.001h',
            'project.017c',
            'project.021c',
            'project.022c',
            'project.023c',
        ]);

        // Project Manager Role
        $projectManagerRole = Role::findOrCreate('project-manager');
        $projectManagerRole->givePermissionTo([
            'dashboard.show',
            'employees.show',
            'official-travels.show',
            'official-travels.recommend',
        ]);

        // Division Manager Role
        $divManagerRole = Role::findOrCreate('div-manager');
        $divManagerRole->givePermissionTo([
            'dashboard.show',
            'employees.show',
            'official-travels.show',
            'official-travels.recommend',
        ]);
    }
}
