<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class IdeaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(fn (self $file) => $file->deleteFromStorage());
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function deleteFromStorage(): void
    {
        if ($this->path) {
            Storage::disk($this->disk ?: 'local')->delete($this->path);
        }
    }

    public function getExtensionAttribute(): string
    {
        return Str::upper(pathinfo($this->original_name, PATHINFO_EXTENSION)) ?: 'FILE';
    }

    public function getReadableSizeAttribute(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }

    public function isImage(): bool
    {
        return Str::startsWith((string) $this->mime_type, 'image/');
    }
}
