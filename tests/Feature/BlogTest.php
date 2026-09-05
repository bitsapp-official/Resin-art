<?php

namespace Tests\Feature;

use App\Enums\BlogPostStatus;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BlogCategory::clearCache();
    }

    public function test_public_blog_index_returns_successful_response(): void
    {
        $category = BlogCategory::create([
            'name' => 'Atelier',
            'slug' => 'atelier',
            'is_active' => true,
        ]);

        BlogPost::create([
            'category_id' => $category->id,
            'title' => 'The first pour of the year',
            'slug' => 'the-first-pour-of-the-year',
            'excerpt' => 'January in the workshop is cold.',
            'content' => 'Full article content here.',
            'status' => BlogPostStatus::PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertSee('Notes from');
        $response->assertSee('The first pour of the year');
    }

    public function test_draft_and_future_scheduled_posts_are_hidden_from_public_listing(): void
    {
        $category = BlogCategory::create([
            'name' => 'Craft',
            'slug' => 'craft',
            'is_active' => true,
        ]);

        // Draft Post
        BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Draft Post Title',
            'slug' => 'draft-post-title',
            'excerpt' => 'Draft excerpt',
            'content' => 'Draft content',
            'status' => BlogPostStatus::DRAFT,
            'published_at' => now()->subDay(),
        ]);

        // Future Scheduled Post
        BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Future Scheduled Title',
            'slug' => 'future-scheduled-title',
            'excerpt' => 'Future excerpt',
            'content' => 'Future content',
            'status' => BlogPostStatus::PUBLISHED,
            'published_at' => now()->addDays(5),
        ]);

        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertDontSee('Draft Post Title');
        $response->assertDontSee('Future Scheduled Title');
    }

    public function test_category_filtering_works_correctly(): void
    {
        $cat1 = BlogCategory::create(['name' => 'Colour', 'slug' => 'colour', 'is_active' => true]);
        $cat2 = BlogCategory::create(['name' => 'Care', 'slug' => 'care', 'is_active' => true]);

        BlogPost::create([
            'category_id' => $cat1->id,
            'title' => 'Colour Article',
            'slug' => 'colour-article',
            'excerpt' => 'Excerpt 1',
            'content' => 'Content 1',
            'status' => BlogPostStatus::PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        BlogPost::create([
            'category_id' => $cat2->id,
            'title' => 'Care Article',
            'slug' => 'care-article',
            'excerpt' => 'Excerpt 2',
            'content' => 'Content 2',
            'status' => BlogPostStatus::PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/blog?category=colour');

        $response->assertStatus(200);
        $response->assertSee('Colour Article');
        $response->assertDontSee('Care Article');
    }

    public function test_published_article_detail_page_returns_successful_response(): void
    {
        $category = BlogCategory::create(['name' => 'Studio', 'slug' => 'studio', 'is_active' => true]);

        $post = BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Inside the Bordeaux atelier',
            'slug' => 'inside-the-bordeaux-atelier',
            'excerpt' => 'A photo essay through our workshop.',
            'content' => '<p>Detail article paragraph content.</p>',
            'author_name' => 'Elène Marchand',
            'status' => BlogPostStatus::PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get("/blog/{$post->slug}");

        $response->assertStatus(200);
        $response->assertSee('Inside the Bordeaux atelier');
        $response->assertSee('Detail article paragraph content.');
        $response->assertSee('Elène Marchand');
        $response->assertSee('schema.org'); // JSON-LD structured data script
    }

    public function test_draft_article_detail_returns_404_not_found(): void
    {
        $category = BlogCategory::create(['name' => 'Craft', 'slug' => 'craft', 'is_active' => true]);

        $post = BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Secret Unpublished Article',
            'slug' => 'secret-unpublished-article',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'status' => BlogPostStatus::DRAFT,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get("/blog/{$post->slug}");

        $response->assertStatus(404);
    }

    public function test_related_posts_excludes_current_article(): void
    {
        $category = BlogCategory::create(['name' => 'Craft', 'slug' => 'craft', 'is_active' => true]);

        $current = BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Current Post Title',
            'slug' => 'current-post-title',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'status' => BlogPostStatus::PUBLISHED,
            'published_at' => now()->subDays(2),
        ]);

        $related = BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Related Post Title',
            'slug' => 'related-post-title',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'status' => BlogPostStatus::PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get("/blog/{$current->slug}");

        $response->assertStatus(200);
        $response->assertSee('Related Post Title');
    }

    public function test_reading_time_is_calculated_automatically_on_model_save(): void
    {
        $category = BlogCategory::create(['name' => 'Care', 'slug' => 'care', 'is_active' => true]);

        // Generate 400 words content (~2 MIN read)
        $words = implode(' ', array_fill(0, 400, 'resin'));

        $post = BlogPost::create([
            'category_id' => $category->id,
            'title' => 'Long Care Guide',
            'slug' => 'long-care-guide',
            'excerpt' => 'Excerpt',
            'content' => "<p>{$words}</p>",
            'status' => BlogPostStatus::PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $this->assertEquals('2 MIN', $post->reading_time);
    }

    public function test_unauthorized_user_cannot_access_blog_cms(): void
    {
        $response1 = $this->get('/admin/blog-posts');
        $response1->assertRedirect('/admin/login');

        $response2 = $this->get('/admin/blog-categories');
        $response2->assertRedirect('/admin/login');
    }

    public function test_authorized_admin_can_access_blog_cms(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response1 = $this->actingAs($admin)->get('/admin/blog-posts');
        $response1->assertStatus(200);

        $response2 = $this->actingAs($admin)->get('/admin/blog-categories');
        $response2->assertStatus(200);
    }
}
