<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Models\AboutValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AboutPage::clearCache();
    }

    public function test_public_about_page_returns_successful_response(): void
    {
        $aboutPage = AboutPage::create([
            'eyebrow' => 'THE HOUSE · EST. 2013',
            'hero_title' => 'A quiet atelier.',
            'hero_description' => 'Maison Résine story intro text.',
            'story_eyebrow' => 'OUR STORY',
            'story_title' => 'Twelve years, one rhythm.',
            'story_content' => 'Craft story description text.',
            'is_published' => true,
        ]);

        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('A quiet atelier.');
        $response->assertSee('Twelve years, one rhythm.');
        $response->assertSee('THE HOUSE · EST. 2013');
    }

    public function test_draft_about_page_does_not_display_content_publicly(): void
    {
        AboutPage::create([
            'hero_title' => 'Secret Draft Title',
            'is_published' => false,
        ]);

        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertDontSee('Secret Draft Title');
        $response->assertSee('Our story is being curated.');
    }

    public function test_active_values_render_in_correct_sort_order(): void
    {
        $aboutPage = AboutPage::create([
            'hero_title' => 'A quiet atelier.',
            'is_published' => true,
        ]);

        AboutValue::create([
            'about_page_id' => $aboutPage->id,
            'number' => '02',
            'title' => 'Honest',
            'description' => 'Real materials. Real hands.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        AboutValue::create([
            'about_page_id' => $aboutPage->id,
            'number' => '01',
            'title' => 'Slow',
            'description' => 'One piece at a time.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        AboutValue::create([
            'about_page_id' => $aboutPage->id,
            'number' => '03',
            'title' => 'Hidden Value',
            'description' => 'Inactive value content',
            'sort_order' => 3,
            'is_active' => false,
        ]);

        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Slow', 'Honest']);
        $response->assertDontSee('Hidden Value');
    }

    public function test_timeline_steps_and_artisans_render_dynamically(): void
    {
        $aboutPage = AboutPage::create([
            'hero_title' => 'A quiet atelier.',
            'is_published' => true,
        ]);

        \App\Models\AboutTimelineStep::create([
            'about_page_id' => $aboutPage->id,
            'year' => '2016',
            'title' => 'A kitchen table',
            'description' => 'The first pour in a rented flat',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        \App\Models\AboutArtisan::create([
            'about_page_id' => $aboutPage->id,
            'name' => 'Camille Devaux',
            'role' => 'FOUNDER · MASTER POURER',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('2016');
        $response->assertSee('A kitchen table');
        $response->assertSee('Camille Devaux');
        $response->assertSee('FOUNDER · MASTER POURER');
    }

    public function test_visit_cta_links_to_contact_page(): void
    {
        AboutPage::create([
            'hero_title' => 'A quiet atelier.',
            'is_published' => true,
        ]);

        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('/contact');
        $response->assertSee('VISIT THE ATELIER');
    }

    public function test_unauthorized_user_cannot_access_about_admin_panel(): void
    {
        $response = $this->get('/admin/about-pages');

        $response->assertRedirect('/admin/login');
    }

    public function test_authorized_admin_can_access_about_admin_panel(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/about-pages');

        $response->assertRedirect('/admin/about-pages/manage');

        $editResponse = $this->actingAs($admin)->get('/admin/about-pages/manage');
        $editResponse->assertStatus(200);
    }

    public function test_updating_about_page_clears_published_cache(): void
    {
        $aboutPage = AboutPage::create([
            'hero_title' => 'Initial Title',
            'is_published' => true,
        ]);

        // First call caches the published model
        $cached = AboutPage::getPublished();
        $this->assertEquals('Initial Title', $cached->hero_title);

        // Update title
        $aboutPage->update([
            'hero_title' => 'Updated Title',
        ]);

        // Cache must be cleared automatically
        $fresh = AboutPage::getPublished();
        $this->assertEquals('Updated Title', $fresh->hero_title);
    }

    public function test_our_story_url_redirects_to_about_and_footer_has_no_separate_our_story_link(): void
    {
        $response = $this->get('/our-story');
        $response->assertRedirect('/about');
        $response->assertStatus(301);

        $aboutResponse = $this->get('/about');
        $aboutResponse->assertStatus(200);
        $aboutResponse->assertDontSee('>Our story</a>', false);
    }
}
