<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    private News $news;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->category = Category::create([
            'name' => 'छत्तीसगढ़',
            'slug' => 'chhattisgarh',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $this->news = News::create([
            'category_id' => $this->category->id,
            'author_id' => $this->admin->id,
            'headline' => 'बिलासपुर में नए फ्लाई-ओवर का उद्घाटन',
            'slug' => 'bilaspur-new-flyover-opening',
            'short_description' => 'यातायात को सुगम बनाने के लिए नए फ्लाई-ओवर का लोकार्पण किया गया।',
            'content' => '<p>बिलासपुर शहर के यातायात में सुधार के लिए यह कदम मील का पत्थर साबित होगा।</p>',
            'location' => 'मंगला चौक',
            'district' => 'बिलासपुर',
            'state' => 'छत्तीसगढ़',
            'status' => 'published',
            'is_breaking' => true,
            'is_featured' => true,
            'published_at' => now(),
        ]);
    }

    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Jankatha');
        $response->assertSee('बिलासपुर में नए फ्लाई-ओवर का उद्घाटन');
    }

    public function test_category_page_loads_successfully(): void
    {
        $response = $this->get('/category/chhattisgarh');
        $response->assertStatus(200);
        $response->assertSee('छत्तीसगढ़');
        $response->assertSee('बिलासपुर में नए फ्लाई-ओवर का उद्घाटन');
    }

    public function test_news_show_page_loads_successfully(): void
    {
        $response = $this->get('/news/bilaspur-new-flyover-opening');
        $response->assertStatus(200);
        $response->assertSee('बिलासपुर में नए फ्लाई-ओवर का उद्घाटन');
        $response->assertSee('मंगला चौक');
    }

    public function test_local_news_page_loads_successfully(): void
    {
        $response = $this->get('/local-news');
        $response->assertStatus(200);
        $response->assertSee('Local News');
    }

    public function test_search_works(): void
    {
        $response = $this->get('/search?q=फ्लाई-ओवर');
        $response->assertStatus(200);
        $response->assertSee('बिलासपुर में नए फ्लाई-ओवर का उद्घाटन');
    }

    public function test_static_pages_load(): void
    {
        $this->get('/about')->assertStatus(200)->assertSee('About Jankatha.com');
        $this->get('/contact')->assertStatus(200)->assertSee('Contact');
        $this->get('/privacy-policy')->assertStatus(200)->assertSee('Privacy Policy');
        $this->get('/terms')->assertStatus(200)->assertSee('Terms & Conditions');
        $this->get('/disclaimer')->assertStatus(200)->assertSee('Editorial Disclaimer');
    }

    public function test_guest_can_access_submit_news_page(): void
    {
        $response = $this->get('/submit-news');
        $response->assertStatus(200);
        $response->assertSee('अपनी खबर भेजें');
    }

    public function test_guest_can_submit_citizen_news(): void
    {
        Storage::fake('public');

        $payload = [
            'headline' => 'वार्ड 5 में पाइपलाइन लीकेज से पेयजल संकट',
            'description' => 'पिछले 3 दिनों से लगातार सड़क पर पानी बह रहा है और घरों में गंदा पानी आ रहा है।',
            'category_id' => $this->category->id,
            'location' => 'सरोना',
            'district' => 'रायपुर',
            'state' => 'छत्तीसगढ़',
            'contributor_name' => 'सुरेश कुमार',
            'mobile' => '9827198271',
            'email' => 'suresh@example.com',
            'consent' => '1',
            'photos' => [
                UploadedFile::fake()->image('leak.jpg', 640, 480),
            ],
        ];

        $response = $this->post('/submit-news', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('citizen_submissions', [
            'headline' => 'वार्ड 5 में पाइपलाइन लीकेज से पेयजल संकट',
            'contributor_name' => 'सुरेश कुमार',
            'status' => 'pending',
            'user_id' => null,
        ]);
        $this->assertDatabaseHas('submission_status_history', [
            'new_status' => 'pending',
            'note' => 'Submission received.',
        ]);
    }
}
