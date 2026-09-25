<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsFetchLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'news_fetch_logs';

    protected $fillable = [
        'news_source_id',
        'status',
        'items_found',
        'items_imported',
        'items_skipped_duplicate',
        'http_status',
        'error_message',
        'execution_time_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'items_found' => 'integer',
            'items_imported' => 'integer',
            'items_skipped_duplicate' => 'integer',
            'http_status' => 'integer',
            'execution_time_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
