<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use Spatie\Permission\Models\Role;
use App\Models\Department;

class EmployeeRegistrationApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Employee Registration approval flow
        $approvalFlow = ApprovalFlow::create([
            'name' => 'Employee Registration Approval Flow',
            'description' => 'Simple approval flow for employee registration with admin review',
            'document_type' => 'employee_registration',
            'is_active' => true,
            'created_by' => 1 // Assuming admin user ID is 1
        ]);

        // Create Admin Review Stage (Single Stage)
        $adminStage = ApprovalStage::create([
            'approval_flow_id' => $approvalFlow->id,
            'stage_name' => 'Admin Review',
            'stage_order' => 1,
            'stage_type' => 'sequential',
            'is_mandatory' => true,
            'auto_approve_conditions' => null,
            'escalation_hours' => 48
        ]);

        // Assign approvers for Admin Review Stage
        $adminRole = Role::where('name', 'administrator')->first();
        $hrRole = Role::where('name', 'HR')->first();
        $hrManagerRole = Role::where('name', 'HR Manager')->first();

        if ($adminRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $adminStage->id,
                'approver_type' => 'role',
                'approver_id' => $adminRole->id,
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if ($hrManagerRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $adminStage->id,
                'approver_type' => 'role',
                'approver_id' => $hrManagerRole->id,
                'is_backup' => true,
                'approval_condition' => null
            ]);
        }

        if ($hrRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $adminStage->id,
                'approver_type' => 'role',
                'approver_id' => $hrRole->id,
                'is_backup' => true,
                'approval_condition' => null
            ]);
        }

        // If no roles found, assign to admin user
        if (!$adminRole && !$hrManagerRole && !$hrRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $adminStage->id,
                'approver_type' => 'user',
                'approver_id' => 1, // Admin user
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        $this->command->info('Employee Registration approval flow created successfully!');
        $this->command->info('Flow ID: ' . $approvalFlow->id);
        $this->command->info('Stage: Admin Review (ID: ' . $adminStage->id . ')');
    }
}
