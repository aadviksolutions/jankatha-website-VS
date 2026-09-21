<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CitizenSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'headline',
        'description',
        'category_id',
        'location',
        'district',
        'state',
        'event_date',
        'event_time',
        'contributor_name',
        'mobile',
        'email',
        'source_information',
        'video_url',
        'consent_at',
        'status',
        'editor_note',
        'reviewed_by',
        'reviewed_at',
        'published_news_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'event_time' => 'datetime:H:i',
            'consent_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function publishedNews(): BelongsTo
    {
        return $this->belongsTo(News::class, 'published_news_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(SubmissionMedia::class, 'submission_id')->orderBy('sort_order');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(SubmissionStatusHistory::class, 'submission_id')->latest('created_at');
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id && in_array($this->status, ['pending', 'needs_more_information'], true);
    }
}
