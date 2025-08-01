<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Department::create([
            'department_name' => 'Accounting',
            'slug' => 'acc',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Corporate Secretary',
            'slug' => 'corsec',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Design & Construction',
            'slug' => 'dnc',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Finance',
            'slug' => 'fin',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Human Capital & Support',
            'slug' => 'hcs',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Internal Audit & System',
            'slug' => 'ias',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Information Technology',
            'slug' => 'ity',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Logistic',
            'slug' => 'log',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Management',
            'slug' => 'mgm',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Operation',
            'slug' => 'ops',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Plant',
            'slug' => 'plt',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Procurement',
            'slug' => 'proc',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Production',
            'slug' => 'prod',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Relation & Coordination',
            'slug' => 'rnc',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Research & Development',
            'slug' => 'rnd',
            'department_status' => '1'
        ]);
        Department::create([
            'department_name' => 'Safety, Health & Environment',
            'slug' => 'she',
            'department_status' => '1'
        ]);
    }
}
