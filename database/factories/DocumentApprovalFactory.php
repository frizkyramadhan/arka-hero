<?php

namespace Database\Factories;

use App\Models\DocumentApproval;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentApproval>
 */
class DocumentApprovalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DocumentApproval::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_type' => $this->faker->randomElement([
                'officialtravel',
                'recruitment_request',
                'employee_registration',
                'test_document',
            ]),
            'document_id' => $this->faker->uuid(),
            'approval_flow_id' => ApprovalFlow::factory(),
            'current_stage_id' => ApprovalStage::factory(),
            'overall_status' => $this->faker->randomElement([
                'pending',
                'approved',
                'rejected',
                'cancelled',
            ]),
            'submitted_by' => User::factory(),
            'submitted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'metadata' => [
                'title' => $this->faker->sentence(),
                'description' => $this->faker->paragraph(),
                'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            ],
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the approval is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'overall_status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the approval is approved.
     */
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'overall_status' => 'approved',
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the approval is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'overall_status' => 'rejected',
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the approval is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'overall_status' => 'cancelled',
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Set a specific document type.
     */
    public function documentType(string $documentType): static
    {
        return $this->state(fn(array $attributes) => [
            'document_type' => $documentType,
        ]);
    }
}
