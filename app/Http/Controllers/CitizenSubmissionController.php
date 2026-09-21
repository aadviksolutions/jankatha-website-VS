<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CitizenSubmission;
use App\Services\SubmissionWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CitizenSubmissionController extends Controller
{
    public function __construct(private readonly SubmissionWorkflow $workflow)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CitizenSubmission::class);
        $user = $request->user();
        $query = $this->isModerator($user) ? CitizenSubmission::query() : $user->submissions();

        return view('submissions.index', [
            'submissions' => $query->with('category')->latest()->paginate(15),
            'stats' => [
                'total' => (clone $query)->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'under_review' => (clone $query)->where('status', 'under_review')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'published' => (clone $query)->where('status', 'published')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CitizenSubmission::class);

        return view('submissions.form', [
            'submission' => new CitizenSubmission(),
            'categories' => Category::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CitizenSubmission::class);
        $validated = $this->validated($request);

        $submission = DB::transaction(function () use ($request, $validated): CitizenSubmission {
            $submission = CitizenSubmission::create([
                ...$validated,
                'user_id' => $request->user()->id,
                'consent_at' => now(),
                'status' => 'pending',
            ]);
            $this->storeMedia($request, $submission);
            $this->workflow->createPending($submission, $request->user());

            return $submission;
        });

        return redirect()->route('my-submissions.show', $submission)->with('status', 'आपकी खबर समीक्षा के लिए भेज दी गई है।');
    }

    public function show(Request $request, CitizenSubmission $submission): View
    {
        $this->authorize('view', $submission);
        $submission->load(['category', 'user', 'reviewer', 'media', 'statusHistory.user', 'publishedNews']);

        return view('submissions.show', compact('submission'));
    }

    public function edit(Request $request, CitizenSubmission $submission): View
    {
        $this->authorize('update', $submission);

        return view('submissions.form', [
            'submission' => $submission,
            'categories' => Category::query()->where('status', 'active')->orWhereKey($submission->category_id)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, CitizenSubmission $submission): RedirectResponse
    {
        $this->authorize('update', $submission);
        $validated = $this->validated($request);

        DB::transaction(function () use ($request, $submission, $validated): void {
            $submission->update([...$validated, 'editor_note' => null]);
            $this->storeMedia($request, $submission);
            if ($submission->status === 'needs_more_information') {
                $this->workflow->transition($submission, 'pending', $request->user(), 'Contributor supplied updated information.');
            }
        });

        return redirect()->route('my-submissions.show', $submission)->with('status', 'आपकी अतिरिक्त जानकारी भेज दी गई है।');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'headline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:100000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date', 'before_or_equal:today'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'contributor_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'source_information' => ['nullable', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'consent' => ['accepted'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:10240'],
            'videos' => ['nullable', 'array', 'max:3'],
            'videos.*' => ['file', 'mimes:mp4,mov,webm', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:51200'],
            'documents' => ['nullable', 'array', 'max:3'],
            'documents.*' => ['file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
        ]);
    }

    private function storeMedia(Request $request, CitizenSubmission $submission): void
    {
        $sets = [
            'photos' => 'image',
            'videos' => 'video',
            'documents' => 'document',
        ];
        $sortOrder = $submission->media()->max('sort_order') ?? 0;

        foreach ($sets as $field => $mediaType) {
            foreach ($request->file($field, []) as $file) {
                $sortOrder++;
                $path = $file->store("submissions/{$submission->id}", 'public');
                $originalName = basename($file->getClientOriginalName());
                $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $originalName) ?: "upload-{$sortOrder}";

                $submission->media()->create([
                    'file_path' => $path,
                    'file_name' => Str::limit($safeName, 255, ''),
                    'file_type' => strtolower($file->getClientOriginalExtension()),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize(),
                    'media_type' => $mediaType,
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    private function isModerator($user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'editor');
    }
}
