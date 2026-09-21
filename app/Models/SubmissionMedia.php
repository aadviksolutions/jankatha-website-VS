<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionMedia extends Model
{
    protected $table = 'submission_media';

    protected $fillable = [
        'submission_id',
        'file_path',
        'file_name',
        'file_type',
        'mime_type',
        'file_size',
        'media_type',
        'sort_order',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CitizenSubmission::class, 'submission_id');
    }
}
