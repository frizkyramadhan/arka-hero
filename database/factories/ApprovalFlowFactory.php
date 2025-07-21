<?php

namespace Database\Factories;

use App\Models\ApprovalFlow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApprovalFlow>
 */
class ApprovalFlowFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApprovalFlow::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'document_type' => $this->faker->randomElement([
                'officialtravel',
                'recruitment_request',
                'employee_registration',
                'test_document',
            ]),
            'is_active' => $this->faker->boolean(80),
            'created_by' => User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the flow is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the flow is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
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
