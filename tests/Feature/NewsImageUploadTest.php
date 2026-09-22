<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->category = Category::create([
            'name' => 'रायपुर',
            'slug' => 'raipur',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_upload_featured_image_through_news_cms(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('ground_report.jpg', 1200, 800);

        $payload = [
            'headline' => 'रायपुर में नए फ्लाईओवर का लोकार्पण',
            'category_id' => $this->category->id,
            'short_description' => 'यातायात को सुगम बनाने के लिए नया फ्लाईओवर खोला गया।',
            'content' => '<p>इस फ्लाईओवर से शहरवासियों को बड़ी राहत मिलेगी।</p>',
            'location' => 'फाफाडीह',
            'district' => 'रायपुर',
            'state' => 'छत्तीसगढ़',
            'status' => 'published',
            'featured_image' => $image,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/news', $payload);

        $response->assertRedirect('/admin/news');

        $news = News::where('headline', 'रायपुर में नए फ्लाईओवर का लोकार्पण')->first();
        $this->assertNotNull($news);
        $this->assertNotNull($news->featured_image);

        // Verify file stored in public disk
        Storage::disk('public')->assertExists($news->featured_image);

        // Verify accessor produces correct storage URL
        $this->assertStringContainsString('storage/'.$news->featured_image, $news->featured_image_url);
        $this->assertStringContainsString('storage/'.$news->featured_image, $news->display_image);

        // Verify public detail page renders the storage URL and the fallback handler
        $detailResponse = $this->get('/news/'.$news->slug);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('storage/'.$news->featured_image);
        $detailResponse->assertSee('images/placeholder.svg');

        // Verify homepage renders the news card with the image
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('storage/'.$news->featured_image);
    }

    public function test_missing_or_null_image_falls_back_to_placeholder(): void
    {
        $news = News::create([
            'category_id' => $this->category->id,
            'author_id' => $this->admin->id,
            'headline' => 'बिना तस्वीर वाली खबर',
            'slug' => 'news-without-image',
            'short_description' => 'इस खबर में कोई तस्वीर संलग्न नहीं है।',
            'content' => '<p>विवरण।</p>',
            'featured_image' => null,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertNull($news->featured_image_url);
        $this->assertStringContainsString('images/placeholder.svg', $news->display_image);

        $response = $this->get('/news/'.$news->slug);
        $response->assertStatus(200);
        $response->assertSee('images/placeholder.svg');
    }

    public function test_external_image_url_is_preserved_without_storage_prefix(): void
    {
        $externalUrl = 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1200&q=80';

        $news = News::create([
            'category_id' => $this->category->id,
            'author_id' => $this->admin->id,
            'headline' => 'एक्सटर्नल फोटो वाली खबर',
            'slug' => 'news-with-external-image',
            'short_description' => 'इस खबर में एक्सटर्नल CDN इमेज है।',
            'content' => '<p>विवरण।</p>',
            'featured_image' => $externalUrl,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertEquals($externalUrl, $news->featured_image_url);
        $this->assertEquals($externalUrl, $news->display_image);

        $response = $this->get('/news/'.$news->slug);
        $response->assertStatus(200);
        $response->assertSee($externalUrl);
        $response->assertDontSee('storage/'.$externalUrl);
    }
}
