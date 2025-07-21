<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use Spatie\Permission\Models\Role;
use App\Models\Department;

class OfficialTravelApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Official Travel approval flow
        $approvalFlow = ApprovalFlow::create([
            'name' => 'Official Travel Approval Flow',
            'description' => 'Standard approval flow for official travel requests with recommendation and approval stages',
            'document_type' => 'officialtravel',
            'is_active' => true,
            'created_by' => 1 // Assuming admin user ID is 1
        ]);

        // Create Recommendation Stage (Stage 1)
        $recommendationStage = ApprovalStage::create([
            'approval_flow_id' => $approvalFlow->id,
            'stage_name' => 'Recommendation',
            'stage_order' => 1,
            'stage_type' => 'sequential',
            'is_mandatory' => true,
            'auto_approve_conditions' => null,
            'escalation_hours' => 72
        ]);

        // Create Approval Stage (Stage 2)
        $approvalStage = ApprovalStage::create([
            'approval_flow_id' => $approvalFlow->id,
            'stage_name' => 'Approval',
            'stage_order' => 2,
            'stage_type' => 'sequential',
            'is_mandatory' => true,
            'auto_approve_conditions' => null,
            'escalation_hours' => 72
        ]);

        // Assign approvers for Recommendation Stage
        // Get roles for recommendation
        $hrRole = Role::where('name', 'HR')->first();
        $supervisorRole = Role::where('name', 'Supervisor')->first();

        if ($hrRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $recommendationStage->id,
                'approver_type' => 'role',
                'approver_id' => $hrRole->id,
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if ($supervisorRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $recommendationStage->id,
                'approver_type' => 'role',
                'approver_id' => $supervisorRole->id,
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        // Assign approvers for Approval Stage
        // Get roles for approval
        $managerRole = Role::where('name', 'Manager')->first();
        $directorRole = Role::where('name', 'Director')->first();

        if ($managerRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $approvalStage->id,
                'approver_type' => 'role',
                'approver_id' => $managerRole->id,
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if ($directorRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $approvalStage->id,
                'approver_type' => 'role',
                'approver_id' => $directorRole->id,
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        // If no roles found, assign to admin user
        if (!$hrRole && !$supervisorRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $recommendationStage->id,
                'approver_type' => 'user',
                'approver_id' => 1, // Admin user
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        if (!$managerRole && !$directorRole) {
            ApprovalStageApprover::create([
                'approval_stage_id' => $approvalStage->id,
                'approver_type' => 'user',
                'approver_id' => 1, // Admin user
                'is_backup' => false,
                'approval_condition' => null
            ]);
        }

        $this->command->info('Official Travel approval flow created successfully!');
        $this->command->info('Flow ID: ' . $approvalFlow->id);
        $this->command->info('Stages: Recommendation (ID: ' . $recommendationStage->id . '), Approval (ID: ' . $approvalStage->id . ')');
    }
}
