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
        return [
            'category' => 'general',
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'keywords' => [$this->faker->word(), $this->faker->word()],
            'is_active' => true,
            'is_seasonal' => false,
        ];
    }
}
