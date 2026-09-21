<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'submission_status_history';

    protected $fillable = [
        'submission_id',
        'user_id',
        'old_status',
        'new_status',
        'note',
        'created_at',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CitizenSubmission::class, 'submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
