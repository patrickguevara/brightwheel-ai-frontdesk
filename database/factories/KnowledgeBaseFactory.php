<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KnowledgeBase>
 */
class KnowledgeBaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'hours', 'tuition', 'enrollment', 'health', 'meals',
            'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general',
        ];

        return [
            'category' => fake()->randomElement($categories),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'keywords' => [fake()->word(), fake()->word(), fake()->word()],
            'is_active' => true,
            'is_seasonal' => false,
            'effective_date' => null,
            'expiry_date' => null,
        ];
    }
}
