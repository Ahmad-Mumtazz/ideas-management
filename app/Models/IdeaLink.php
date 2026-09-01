<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class IdeaLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'url',
    ];

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    /**
     * The bare host, used as a compact caption on the link chip.
     */
    public function getHostAttribute(): string
    {
        return Str::of((string) parse_url($this->url, PHP_URL_HOST))
            ->replaceStart('www.', '')
            ->toString();
    }
}
