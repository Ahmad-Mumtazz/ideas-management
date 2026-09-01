<?php

namespace Database\Factories;

use App\Models\Idea;
use App\Models\IdeaLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdeaLink>
 */
class IdeaLinkFactory extends Factory
{
    protected $model = IdeaLink::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'label' => fake()->words(2, true),
            'url' => fake()->url(),
        ];
    }
}
