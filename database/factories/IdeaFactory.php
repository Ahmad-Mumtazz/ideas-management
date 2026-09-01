<?php

namespace Database\Factories;

use App\Enums\IdeaPriority;
use App\Enums\IdeaStatus;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Idea>
 */
class IdeaFactory extends Factory
{
    protected $model = Idea::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(IdeaStatus::cases()),
            'priority' => fake()->randomElement(IdeaPriority::cases()),
            'category' => fake()->randomElement(['Product', 'Research', 'Marketing', null]),
            'tags' => fake()->randomElements(['design', 'api', 'growth', 'infra'], 2),
            'due_date' => null,
            'cover_image' => null,
            'archived_at' => null,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['archived_at' => now()]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->subDays(3),
            'status' => IdeaStatus::InProgress,
        ]);
    }

    public function status(IdeaStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
