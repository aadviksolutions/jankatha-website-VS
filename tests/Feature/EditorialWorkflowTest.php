<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CitizenSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    private User $citizen;

    private Category $category;

    private CitizenSubmission $submission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->editor = User::factory()->create([
            'role' => 'editor',
            'status' => 'active',
        ]);

        $this->citizen = User::factory()->create([
            'role' => 'citizen',
            'status' => 'active',
        ]);

        $this->category = Category::create([
            'name' => 'स्थानीय समाचार',
            'slug' => 'local-news-cat',
            'status' => 'active',
        ]);

        $this->submission = CitizenSubmission::create([
            'user_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'headline' => 'चौक पर स्ट्रीट लाइट बंद',
            'description' => 'रात के समय अंधेरे के कारण असामाजिक तत्वों का जमावड़ा लगा रहता है।',
            'location' => 'गोलबाजार',
            'district' => 'बिलासपुर',
            'state' => 'छत्तीसगढ़',
            'contributor_name' => 'आलोक शर्मा',
            'mobile' => '9876543210',
            'email' => 'alok@example.com',
            'consent_at' => now(),
            'status' => 'pending',
        ]);
    }

    public function test_editor_can_view_submissions_list(): void
    {
        $response = $this->actingAs($this->editor)->get('/admin/submissions');
        $response->assertStatus(200);
        $response->assertSee('चौक पर स्ट्रीट लाइट बंद');
    }

    public function test_editor_can_transition_submission_through_workflow(): void
    {
        // 1. start review: pending -> under_review
        $response = $this->actingAs($this->editor)->post("/admin/submissions/{$this->submission->id}/start-review", [
            'note' => 'Review started by desk.',
        ]);
        $response->assertRedirect();
        $this->assertEquals('under_review', $this->submission->fresh()->status);

        // 2. verify: under_review -> verified
        $response = $this->actingAs($this->editor)->post("/admin/submissions/{$this->submission->id}/verify", [
            'note' => 'Location and issue verified.',
        ]);
        $response->assertRedirect();
        $this->assertEquals('verified', $this->submission->fresh()->status);

        // 3. approve: verified -> approved
        $response = $this->actingAs($this->editor)->post("/admin/submissions/{$this->submission->id}/approve", [
            'note' => 'Approved for portal publication.',
        ]);
        $response->assertRedirect();
        $this->assertEquals('approved', $this->submission->fresh()->status);

        // 4. publish: approved -> published (creates News record)
        $response = $this->actingAs($this->editor)->post("/admin/submissions/{$this->submission->id}/publish", [
            'note' => 'Published live.',
        ]);
        $response->assertRedirect();
        $this->assertEquals('published', $this->submission->fresh()->status);
        $this->assertNotNull($this->submission->fresh()->published_news_id);

        $this->assertDatabaseHas('news', [
            'headline' => 'चौक पर स्ट्रीट लाइट बंद',
            'status' => 'published',
        ]);
    }
}
