<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DebugController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:administrator');
    }

    public function index()
    {
        $title = 'Debug Tools';
        $subtitle = 'Database Management Tools';

        return view('debug.index', compact('title', 'subtitle'));
    }

    public function truncateEmployees()
    {
        try {
            DB::table('employees')->truncate();
            return redirect()->back()->with('toast_success', 'Employees table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate employees table: ' . $e->getMessage());
        }
    }

    public function truncateAdministrations()
    {
        try {
            DB::table('administrations')->truncate();
            return redirect()->back()->with('toast_success', 'Administrations table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate administrations table: ' . $e->getMessage());
        }
    }

    public function truncateEmployeebanks()
    {
        try {
            DB::table('employeebanks')->truncate();
            return redirect()->back()->with('toast_success', 'Employee banks table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate employee banks table: ' . $e->getMessage());
        }
    }

    public function truncateTaxidentifications()
    {
        try {
            DB::table('taxidentifications')->truncate();
            return redirect()->back()->with('toast_success', 'Tax identifications table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate tax identifications table: ' . $e->getMessage());
        }
    }

    public function truncateInsurances()
    {
        try {
            DB::table('insurances')->truncate();
            return redirect()->back()->with('toast_success', 'Insurances table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate insurances table: ' . $e->getMessage());
        }
    }

    public function truncateLicenses()
    {
        try {
            DB::table('licenses')->truncate();
            return redirect()->back()->with('toast_success', 'Licenses table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate licenses table: ' . $e->getMessage());
        }
    }

    public function truncateFamilies()
    {
        try {
            DB::table('families')->truncate();
            return redirect()->back()->with('toast_success', 'Families table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate families table: ' . $e->getMessage());
        }
    }

    public function truncateEducations()
    {
        try {
            DB::table('educations')->truncate();
            return redirect()->back()->with('toast_success', 'Educations table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate educations table: ' . $e->getMessage());
        }
    }

    public function truncateCourses()
    {
        try {
            DB::table('courses')->truncate();
            return redirect()->back()->with('toast_success', 'Courses table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate courses table: ' . $e->getMessage());
        }
    }

    public function truncateJobexperiences()
    {
        try {
            DB::table('jobexperiences')->truncate();
            return redirect()->back()->with('toast_success', 'Job experiences table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate job experiences table: ' . $e->getMessage());
        }
    }

    public function truncateOperableunits()
    {
        try {
            DB::table('operableunits')->truncate();
            return redirect()->back()->with('toast_success', 'Operable units table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate operable units table: ' . $e->getMessage());
        }
    }

    public function truncateEmrgcalls()
    {
        try {
            DB::table('emrgcalls')->truncate();
            return redirect()->back()->with('toast_success', 'Emergency calls table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate emergency calls table: ' . $e->getMessage());
        }
    }

    public function truncateAdditionaldatas()
    {
        try {
            DB::table('additionaldatas')->truncate();
            return redirect()->back()->with('toast_success', 'Additional datas table truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate additional datas table: ' . $e->getMessage());
        }
    }

    public function truncateAll()
    {
        try {
            $tables = [
                'administrations',
                'employeebanks',
                'taxidentifications',
                'insurances',
                'licenses',
                'families',
                'educations',
                'courses',
                'jobexperiences',
                'operableunits',
                'emrgcalls',
                'additionaldatas',
                'employees'
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            return redirect()->back()->with('toast_success', 'All specified tables truncated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Failed to truncate tables: ' . $e->getMessage());
        }
    }
}
