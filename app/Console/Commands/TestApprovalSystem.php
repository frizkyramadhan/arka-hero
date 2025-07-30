<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApprovalStage;
use App\Models\ApprovalPlan;
use App\Models\Officialtravel;
use App\Models\RecruitmentRequest;
use App\Models\User;
use App\Models\Project;
use App\Models\Department;

class TestApprovalSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:test {--setup : Setup test data} {--cleanup : Cleanup test data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the approval system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('setup')) {
            $this->setupTestData();
        } elseif ($this->option('cleanup')) {
            $this->cleanupTestData();
        } else {
            $this->testApprovalSystem();
        }
    }

    private function setupTestData()
    {
        $this->info('Setting up test data for approval system...');

        // Create test users if they don't exist
        $approver = User::firstOrCreate(
            ['email' => 'approver@test.com'],
            [
                'name' => 'Test Approver',
                'password' => bcrypt('password'),
            ]
        );

        $this->info("Created test approver: {$approver->name}");

        // Get first project and department
        $project = Project::first();
        $department = Department::first();

        if (!$project || !$department) {
            $this->error('No projects or departments found. Please create them first.');
            return;
        }

        // Create approval stages
        $stages = [
            ['document_type' => 'officialtravel'],
            ['document_type' => 'recruitment_request'],
        ];

        foreach ($stages as $stage) {
            ApprovalStage::firstOrCreate([
                'project' => $project->project_code,
                'department_id' => $department->id,
                'approver_id' => $approver->id,
                'document_type' => $stage['document_type'],
            ]);
        }

        $this->info('Test approval stages created successfully!');
        $this->info('Test data setup complete.');
    }

    private function cleanupTestData()
    {
        $this->info('Cleaning up test data...');

        // Remove test approval stages
        ApprovalStage::where('approver_id', function ($query) {
            $query->select('id')->from('users')->where('email', 'approver@test.com');
        })->delete();

        // Remove test approval plans
        ApprovalPlan::where('approver_id', function ($query) {
            $query->select('id')->from('users')->where('email', 'approver@test.com');
        })->delete();

        // Remove test user
        User::where('email', 'approver@test.com')->delete();

        $this->info('Test data cleaned up successfully!');
    }

    private function testApprovalSystem()
    {
        $this->info('Testing approval system...');

        // Test 1: Check if approval stages exist
        $stages = ApprovalStage::count();
        $this->info("Found {$stages} approval stages");

        // Test 2: Check if approval plans exist
        $plans = ApprovalPlan::count();
        $this->info("Found {$plans} approval plans");

        // Test 3: Check pending approvals
        $pending = ApprovalPlan::where('status', 0)->where('is_open', 1)->count();
        $this->info("Found {$pending} pending approvals");

        // Test 4: Check approved documents
        $approved = ApprovalPlan::where('status', 1)->count();
        $this->info("Found {$approved} approved plans");

        // Test 5: Check rejected documents
        $rejected = ApprovalPlan::where('status', 3)->count();
        $this->info("Found {$rejected} rejected plans");

        $this->info('Approval system test completed!');
    }
}
