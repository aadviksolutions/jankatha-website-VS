<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsSource extends Model
{
    use HasFactory;

    protected $table = 'news_sources';

    protected $fillable = [
        'name',
        'url',
        'feed_url',
        'source_type',
        'category_id',
        'state',
        'district',
        'city',
        'language',
        'is_active',
        'fetch_frequency_minutes',
        'priority',
        'logo_url',
        'attribution_text',
        'last_fetched_at',
        'last_error',
        'items_fetched_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'fetch_frequency_minutes' => 'integer',
            'priority' => 'integer',
            'items_fetched_count' => 'integer',
            'last_fetched_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'source_id');
    }

    public function fetchLogs(): HasMany
    {
        return $this->hasMany(NewsFetchLog::class, 'news_source_id');
    }
}
