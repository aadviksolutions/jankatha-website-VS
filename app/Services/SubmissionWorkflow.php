<?php

namespace App\Services;

use App\Models\CitizenSubmission;
use App\Models\News;
use App\Models\SubmissionStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionWorkflow
{
    private const TRANSITIONS = [
        'pending' => ['under_review', 'rejected'],
        'under_review' => ['needs_more_information', 'verified', 'rejected'],
        'needs_more_information' => ['pending', 'under_review', 'rejected'],
        'verified' => ['approved', 'rejected'],
        'approved' => ['published', 'rejected'],
        'published' => [],
        'rejected' => [],
    ];

    public function createPending(CitizenSubmission $submission, User $user): void
    {
        SubmissionStatusHistory::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'old_status' => null,
            'new_status' => 'pending',
            'note' => 'Submission received.',
        ]);
    }

    public function transition(CitizenSubmission $submission, string $newStatus, User $user, ?string $note = null): CitizenSubmission
    {
        return DB::transaction(function () use ($submission, $newStatus, $user, $note): CitizenSubmission {
            $submission = CitizenSubmission::query()->lockForUpdate()->findOrFail($submission->id);
            $oldStatus = $submission->status;

            if (! in_array($newStatus, self::TRANSITIONS[$oldStatus] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => "The submission cannot move from {$oldStatus} to {$newStatus}.",
                ]);
            }

            if ($newStatus === 'published') {
                $this->publish($submission, $user, $note);
                return $submission->refresh();
            }

            $submission->update([
                'status' => $newStatus,
                'editor_note' => $note,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            SubmissionStatusHistory::create([
                'submission_id' => $submission->id,
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => $note,
            ]);

            return $submission->refresh();
        });
    }

    private function publish(CitizenSubmission $submission, User $reviewer, ?string $note): void
    {
        if ($submission->published_news_id) {
            return;
        }

        $image = $submission->media()->where('media_type', 'image')->first();
        $video = $submission->media()->where('media_type', 'video')->first();
        $news = News::create([
            'category_id' => $submission->category_id,
            'author_id' => $submission->user_id,
            'headline' => $submission->headline,
            'slug' => $this->uniqueNewsSlug($submission->headline),
            'short_description' => $submission->description,
            'content' => $submission->description,
            'featured_image' => $image?->file_path,
            'location' => $submission->location,
            'district' => $submission->district,
            'state' => $submission->state,
            'video_url' => $submission->video_url ?: ($video ? Storage::disk('public')->url($video->file_path) : null),
            'status' => 'published',
            'published_at' => now(),
            'seo_title' => $submission->headline,
            'seo_description' => $submission->description,
        ]);

        $submission->update([
            'status' => 'published',
            'published_news_id' => $news->id,
            'editor_note' => $note,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        SubmissionStatusHistory::create([
            'submission_id' => $submission->id,
            'user_id' => $reviewer->id,
            'old_status' => 'approved',
            'new_status' => 'published',
            'note' => $note,
        ]);
    }

    private function uniqueNewsSlug(string $headline): string
    {
        $base = Str::slug($headline) ?: 'citizen-news';
        $slug = $base;
        $counter = 2;

        while (News::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
