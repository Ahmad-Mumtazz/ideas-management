<?php

namespace App\Models;

use App\Observers\CheckpointObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The observer recalculates the parent idea's status whenever a checkpoint is
 * added, completed, reopened or removed.
 */
#[ObservedBy(CheckpointObserver::class)]
class Checkpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'is_completed',
        'completed_at',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    /**
     * Flip the completion state, keeping `completed_at` in step with it.
     */
    public function toggle(): bool
    {
        $this->is_completed = ! $this->is_completed;
        $this->completed_at = $this->is_completed ? now() : null;

        return $this->save();
    }
}
