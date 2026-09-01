<?php

namespace App\Models;

use App\Enums\IdeaPriority;
use App\Enums\IdeaStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Idea extends Model
{
    use HasFactory;

    /**
     * `user_id` is deliberately NOT fillable — ownership is only ever set by
     * creating through the authenticated user's relationship.
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category',
        'tags',
        'due_date',
        'cover_image',
    ];

    protected function casts(): array
    {
        return [
            'status' => IdeaStatus::class,
            'priority' => IdeaPriority::class,
            'tags' => 'array',
            'due_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // DB-level cascades remove the child rows but not the stored files,
        // so the physical files are cleaned up here before the idea goes.
        static::deleting(function (self $idea) {
            foreach ($idea->files()->get() as $file) {
                $file->deleteFromStorage();
            }

            $idea->deleteCoverImage();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(Checkpoint::class)->orderBy('position')->orderBy('id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(IdeaLink::class)->latest('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(IdeaFile::class)->latest('id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(IdeaActivity::class)->latest('id');
    }

    /**
     * Record a human-readable entry in the idea's history.
     */
    public function recordActivity(string $type, string $description, ?User $user = null): IdeaActivity
    {
        return $this->activities()->create([
            'user_id' => ($user ?? auth()->user())?->id,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function deleteCoverImage(): void
    {
        if ($this->cover_image) {
            Storage::disk('local')->delete($this->cover_image);
        }
    }

    /**
     * The cover image URL, or null when there is no cover.
     *
     * The `v` parameter is derived from the stored path, which is regenerated
     * on every upload. Without it the URL never changes, so a browser holding a
     * cached copy keeps showing the *previous* image after a replacement — the
     * image looks like it did not update at all.
     */
    public function coverImageUrl(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        return route('ideas.cover', [
            'idea' => $this,
            'v' => substr(md5($this->cover_image), 0, 8),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Progress
    |--------------------------------------------------------------------------
    */

    public function totalCheckpoints(): int
    {
        return $this->checkpoints_count ?? $this->checkpoints()->count();
    }

    public function completedCheckpoints(): int
    {
        return $this->completed_checkpoints_count
            ?? $this->checkpoints()->where('is_completed', true)->count();
    }

    /**
     * Completion percentage derived from checkpoints. An idea with no
     * checkpoints is 100% when marked completed, otherwise 0%.
     */
    public function getProgressAttribute(): int
    {
        $total = $this->totalCheckpoints();

        if ($total === 0) {
            return $this->status === IdeaStatus::Completed ? 100 : 0;
        }

        return (int) round(($this->completedCheckpoints() / $total) * 100);
    }

    /**
     * The progress bar warms from rose through amber to green as it fills.
     */
    public function getProgressColorAttribute(): string
    {
        return match (true) {
            $this->progress >= 100 => 'bg-emerald-500',
            $this->progress >= 75 => 'bg-lime-500',
            $this->progress >= 50 => 'bg-amber-500',
            $this->progress >= 25 => 'bg-orange-500',
            $this->progress > 0 => 'bg-rose-500',
            default => 'bg-slate-300 dark:bg-slate-600',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Status automation
    |--------------------------------------------------------------------------
    |
    | Once an idea has checkpoints, its status is a function of their
    | completion and is never set by hand. This is the single place that
    | mapping lives; CheckpointObserver calls it whenever a checkpoint is
    | added, toggled or removed, so status and progress cannot drift apart.
    |
    | An idea with no checkpoints has nothing to derive from, so it keeps
    | whatever status the owner chose (new ideas default to Pending).
    |
    */

    public function hasCheckpoints(): bool
    {
        return $this->checkpoints()->exists();
    }

    /**
     * The status implied by the current checkpoint completion:
     * none complete → Pending, some → In Progress, all → Completed.
     *
     * Counts are queried fresh rather than read from eager-loaded values, which
     * may be stale at the moment a checkpoint changes.
     */
    public function deriveStatus(): IdeaStatus
    {
        $total = $this->checkpoints()->count();

        if ($total === 0) {
            return $this->status;
        }

        $completed = $this->checkpoints()->where('is_completed', true)->count();

        return match (true) {
            $completed === 0 => IdeaStatus::Pending,
            $completed >= $total => IdeaStatus::Completed,
            default => IdeaStatus::InProgress,
        };
    }

    /**
     * Recalculate and persist the status from checkpoint completion.
     *
     * Returns true when the status actually changed.
     */
    public function syncStatusFromCheckpoints(): bool
    {
        $derived = $this->deriveStatus();

        if ($derived === $this->status) {
            return false;
        }

        $previous = $this->status;

        $this->forceFill(['status' => $derived])->save();

        $this->recordActivity(
            'status',
            'Status changed from '.$previous->label().' to '.$derived->label().' (from checkpoint progress)'
        );

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | State helpers
    |--------------------------------------------------------------------------
    */

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->status !== IdeaStatus::Completed
            && $this->due_date->isBefore(Carbon::today());
    }

    public function isDueSoon(int $days = 7): bool
    {
        return $this->due_date !== null
            && $this->status !== IdeaStatus::Completed
            && $this->due_date->betweenIncluded(Carbon::today(), Carbon::today()->addDays($days));
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today())
            ->where('status', '!=', IdeaStatus::Completed->value);
    }

    public function scopeDueWithin(Builder $query, int $days): Builder
    {
        return $query->whereNotNull('due_date')
            ->whereDate('due_date', '>=', Carbon::today())
            ->whereDate('due_date', '<=', Carbon::today()->addDays($days))
            ->where('status', '!=', IdeaStatus::Completed->value);
    }

    /**
     * Attach checkpoint counts so progress never triggers an N+1 query.
     */
    public function scopeWithProgress(Builder $query): Builder
    {
        return $query->withCount([
            'checkpoints',
            'checkpoints as completed_checkpoints_count' => fn (Builder $q) => $q->where('is_completed', true),
        ]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhere('tags', 'like', $like);
        });
    }

    /**
     * Portable ordering — the raw expressions avoid MySQL-only functions so the
     * same query runs under SQLite in the test suite.
     */
    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        $completed = '(select count(*) from checkpoints where checkpoints.idea_id = ideas.id and checkpoints.is_completed = 1)';
        $total = '(select count(*) from checkpoints where checkpoints.idea_id = ideas.id)';

        return match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'updated' => $query->orderByDesc('updated_at'),
            'title' => $query->orderBy('title'),
            'due_date' => $query->orderByRaw('due_date is null')->orderBy('due_date'),
            'priority' => $query->orderByRaw(
                "case priority when 'high' then 3 when 'medium' then 2 else 1 end desc"
            )->orderByDesc('created_at'),
            'progress' => $query->orderByRaw("($completed * 1.0) / nullif($total, 0) desc")
                ->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }

    /** @return array<string, string> */
    public static function sortOptions(): array
    {
        return [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'updated' => 'Recently updated',
            'due_date' => 'Due date',
            'priority' => 'Priority',
            'progress' => 'Progress',
            'title' => 'Title (A–Z)',
        ];
    }
}
