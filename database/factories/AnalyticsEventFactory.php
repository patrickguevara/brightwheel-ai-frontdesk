<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnalyticsEvent>
 */
class AnalyticsEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_type' => fake()->randomElement([
                'question_asked',
                'answer_given',
                'escalated',
                'feedback_given',
                'knowledge_updated',
            ]),
            'category' => fake()->randomElement([
                'enrollment',
                'billing',
                'schedules',
                'policies',
                'general',
                null,
            ]),
            'metadata' => [
                'source' => fake()->randomElement(['chat', 'api', 'dashboard']),
                'user_agent' => fake()->userAgent(),
            ],
        ];
    }
}
