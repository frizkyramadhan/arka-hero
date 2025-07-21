<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use Spatie\Permission\Models\Role;
use App\Models\Department;

class RecruitmentRequestApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Recruitment Request approval flow
        $approvalFlow = ApprovalFlow::create([
            'name' => 'Recruitment Request Approval Flow',
            'description' => '3-stage approval flow for recruitment requests: HR Acknowledgment → PM Approval → Director Approval',
            'document_type' => 'recruitment_request',
            'is_active' => true,
            'created_by' => 1 // Assuming admin user ID is 1
        ]);

        // Create HR Acknowledgment Stage (Stage 1)
        $hrStage = ApprovalStage::create([
            'approval_flow_id' => $approvalFlow->id,
            'stage_name' => 'HR Acknowledgment',
            'stage_order' => 1,
            'stage_type' => 'sequential',
            'is_mandatory' => true,
            'auto_approve_conditions' => null,
            'escalation_hours' => 48
        ]);

        // Create PM Approval Stage (Stage 2)
        $pmStage = ApprovalStage::create([
            'approval_flow_id' => $approvalFlow->id,
            'stage_name' => 'Project Manager Approval',
            'stage_order' => 2,
            'stage_type' => 'sequential',
            'is_mandatory' => true,
            'auto_approve_conditions' => null,
            'escalation_hours' => 72
        ]);

        // Create Director Approval Stage (Stage 3)
        $directorStage = ApprovalStage::create([
            'approval_flow_id' => $approvalFlow->id,
            'stage_name' => 'Director Approval',
            'stage_order' => 3,
            'stage_type' => 'sequential',
            'is_mandatory' => true,
            'auto_approve_conditions' => null,
            'escalation_hours' => 72
        ]);

        // Assign approvers for HR Acknowledgment Stage
        $hrRole = Role::where('name', 'HR')->first();
        $hrManagerRole = Role::where('name', 'HR Manager')->first();

        if ($hrRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $hrStage->id,
                'approver_type' => 'role',
                'approver_id' => $hrRole->id,
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if ($hrManagerRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $hrStage->id,
                'approver_type' => 'role',
                'approver_id' => $hrManagerRole->id,
                'is_backup' => true,
                'approval_condition' => null
            ]);
        }

        // Assign approvers for PM Approval Stage
        $pmRole = Role::where('name', 'Project Manager')->first();
        $managerRole = Role::where('name', 'Manager')->first();

        if ($pmRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $pmStage->id,
                'approver_type' => 'role',
                'approver_id' => $pmRole->id,
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if ($managerRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $pmStage->id,
                'approver_type' => 'role',
                'approver_id' => $managerRole->id,
                'is_backup' => true,
                'approval_condition' => null
            ]);
        }

        // Assign approvers for Director Approval Stage
        $directorRole = Role::where('name', 'Director')->first();
        $ceoRole = Role::where('name', 'CEO')->first();

        if ($directorRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $directorStage->id,
                'approver_type' => 'role',
                'approver_id' => $directorRole->id,
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if ($ceoRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $directorStage->id,
                'approver_type' => 'role',
                'approver_id' => $ceoRole->id,
                'is_backup' => true,
                'approval_condition' => null
            ]);
        }

        // If no roles found, assign to admin user for each stage
        if (!$hrRole && !$hrManagerRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $hrStage->id,
                'approver_type' => 'user',
                'approver_id' => 1, // Admin user
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if (!$pmRole && !$managerRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $pmStage->id,
                'approver_type' => 'user',
                'approver_id' => 1, // Admin user
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if (!$directorRole && !$ceoRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $directorStage->id,
                'approver_type' => 'user',
                'approver_id' => 1, // Admin user
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        $this->command->info('Recruitment Request approval flow created successfully!');
        $this->command->info('Flow ID: ' . $approvalFlow->id);
        $this->command->info('Stages: HR Acknowledgment (ID: ' . $hrStage->id . '), PM Approval (ID: ' . $pmStage->id . '), Director Approval (ID: ' . $directorStage->id . ')');
    }
}
