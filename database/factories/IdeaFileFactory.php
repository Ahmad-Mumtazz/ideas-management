<?php

namespace Database\Factories;

use App\Models\Idea;
use App\Models\IdeaFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdeaFile>
 */
class IdeaFileFactory extends Factory
{
    protected $model = IdeaFile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'disk' => 'local',
            'path' => 'idea-files/test/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 500000),
        ];
    }
}
