<?php

namespace Database\Factories;

use App\Models\ApprovalStageApprover;
use App\Models\ApprovalStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApprovalStageApprover>
 */
class ApprovalStageApproverFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApprovalStageApprover::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approval_stage_id' => ApprovalStage::factory(),
            'approver_type' => $this->faker->randomElement(['user', 'role', 'department']),
            'approver_id' => User::factory(),
            'is_backup' => $this->faker->boolean(20),
            'approval_condition' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Set user approver type.
     */
    public function userApprover(): static
    {
        return $this->state(fn(array $attributes) => [
            'approver_type' => 'user',
            'approver_id' => User::factory(),
        ]);
    }

    /**
     * Set role approver type.
     */
    public function roleApprover(): static
    {
        return $this->state(fn(array $attributes) => [
            'approver_type' => 'role',
            'approver_id' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Set department approver type.
     */
    public function departmentApprover(): static
    {
        return $this->state(fn(array $attributes) => [
            'approver_type' => 'department',
            'approver_id' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Set as backup approver.
     */
    public function backup(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_backup' => true,
        ]);
    }

    /**
     * Set as primary approver.
     */
    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_backup' => false,
        ]);
    }
}
