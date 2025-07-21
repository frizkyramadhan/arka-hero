<?php

namespace Database\Factories;

use App\Models\ApprovalStage;
use App\Models\ApprovalFlow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApprovalStage>
 */
class ApprovalStageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApprovalStage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approval_flow_id' => ApprovalFlow::factory(),
            'stage_name' => $this->faker->randomElement([
                'Review',
                'Approval',
                'Final Approval',
                'HR Review',
                'Manager Approval',
                'Director Approval',
            ]),
            'stage_order' => $this->faker->numberBetween(1, 5),
            'stage_type' => $this->faker->randomElement(['sequential', 'parallel']),
            'is_mandatory' => $this->faker->boolean(90),
            'auto_approve_conditions' => null,
            'escalation_hours' => $this->faker->numberBetween(24, 168),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Set a specific stage order.
     */
    public function stageOrder(int $order): static
    {
        return $this->state(fn(array $attributes) => [
            'stage_order' => $order,
        ]);
    }

    /**
     * Set sequential stage type.
     */
    public function sequential(): static
    {
        return $this->state(fn(array $attributes) => [
            'stage_type' => 'sequential',
        ]);
    }

    /**
     * Set parallel stage type.
     */
    public function parallel(): static
    {
        return $this->state(fn(array $attributes) => [
            'stage_type' => 'parallel',
        ]);
    }
}
