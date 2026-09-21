<?php

namespace App\Http\Controllers;

use App\Models\CitizenSubmission;
use App\Services\SubmissionWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubmissionModerationController extends Controller
{
    public function __construct(private readonly SubmissionWorkflow $workflow)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CitizenSubmission::class);
        $submissions = CitizenSubmission::query()->with(['category', 'user'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.submissions.index', compact('submissions'));
    }

    public function show(CitizenSubmission $submission): View
    {
        $this->authorize('view', $submission);
        $submission->load(['category', 'user', 'reviewer', 'media', 'statusHistory.user', 'publishedNews']);

        return view('admin.submissions.show', compact('submission'));
    }

    public function startReview(Request $request, CitizenSubmission $submission): RedirectResponse
    {
        return $this->transition($request, $submission, 'under_review');
    }

    public function requestInformation(Request $request, CitizenSubmission $submission): RedirectResponse
    {
        return $this->transition($request, $submission, 'needs_more_information');
    }

    public function verify(Request $request, CitizenSubmission $submission): RedirectResponse
    {
        return $this->transition($request, $submission, 'verified');
    }

    public function approve(Request $request, CitizenSubmission $submission): RedirectResponse
    {
        return $this->transition($request, $submission, 'approved');
    }

    public function reject(Request $request, CitizenSubmission $submission): RedirectResponse
    {
        return $this->transition($request, $submission, 'rejected');
    }

    public function publish(Request $request, CitizenSubmission $submission): RedirectResponse
    {
        $this->authorize('moderate', $submission);

        if ($submission->published_news_id) {
            return back()->with('status', 'This submission is already published.');
        }

        $validated = $request->validate(['note' => ['nullable', 'string', 'max:5000']]);
        $this->workflow->transition($submission, 'published', $request->user(), $validated['note'] ?? null);

        return back()->with('status', 'Submission published as news.');
    }

    private function transition(Request $request, CitizenSubmission $submission, string $status): RedirectResponse
    {
        $this->authorize('moderate', $submission);
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:5000'],
        ]);
        $this->workflow->transition($submission, $status, $request->user(), $validated['note'] ?? null);

        return back()->with('status', 'Submission status updated.');
    }
}
