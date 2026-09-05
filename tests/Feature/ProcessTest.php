<?php

namespace Tests\Feature;

use App\Enums\ProcessPageStatus;
use App\Models\ProcessPage;
use App\Models\ProcessStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ProcessPage::clearCache();
    }

    public function test_public_our_process_page_returns_successful_response(): void
    {
        $page = ProcessPage::create([
            'eyebrow' => 'OUR PROCESS',
            'title' => 'Six weeks, one object.',
            'description' => 'From timber selection to the final hand-polish.',
            'cta_title' => 'Have a custom piece in mind?',
            'cta_button_text' => 'SUBMIT YOUR REQUIREMENTS',
            'cta_url' => '/custom',
            'status' => ProcessPageStatus::PUBLISHED,
        ]);

        ProcessStep::create([
            'process_page_id' => $page->id,
            'step_number' => '01',
            'title' => 'Conversation',
            'description' => 'Dimensions and timber species discussion.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/our-process');

        $response->assertStatus(200);
        $response->assertSee('OUR PROCESS');
        $response->assertSee('Six weeks, one object.');
        $response->assertSee('Conversation');
        $response->assertSee('SUBMIT YOUR REQUIREMENTS');
    }

    public function test_draft_process_page_is_hidden_from_public(): void
    {
        ProcessPage::create([
            'title' => 'Unpublished Draft Process',
            'status' => ProcessPageStatus::DRAFT,
        ]);

        $response = $this->get('/our-process');

        $response->assertStatus(200);
        $response->assertDontSee('Unpublished Draft Process');
    }

    public function test_inactive_process_steps_are_hidden(): void
    {
        $page = ProcessPage::create([
            'title' => 'Main Process Page',
            'status' => ProcessPageStatus::PUBLISHED,
        ]);

        ProcessStep::create([
            'process_page_id' => $page->id,
            'title' => 'Active Step One',
            'description' => 'Active desc',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ProcessStep::create([
            'process_page_id' => $page->id,
            'title' => 'Inactive Hidden Step',
            'description' => 'Hidden desc',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $response = $this->get('/our-process');

        $response->assertStatus(200);
        $response->assertSee('Active Step One');
        $response->assertDontSee('Inactive Hidden Step');
    }

    public function test_process_steps_are_sorted_by_sort_order(): void
    {
        $page = ProcessPage::create([
            'title' => 'Main Process Page',
            'status' => ProcessPageStatus::PUBLISHED,
        ]);

        $step2 = ProcessStep::create([
            'process_page_id' => $page->id,
            'title' => 'Step B Second',
            'description' => 'Desc B',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $step1 = ProcessStep::create([
            'process_page_id' => $page->id,
            'title' => 'Step A First',
            'description' => 'Desc A',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $activeSteps = $page->fresh()->activeSteps;

        $this->assertEquals('Step A First', $activeSteps->first()->title);
        $this->assertEquals('Step B Second', $activeSteps->last()->title);
    }

    public function test_step_formatted_number_accessor(): void
    {
        $step = new ProcessStep(['step_number' => '3', 'sort_order' => 1]);
        $this->assertEquals('03', $step->formatted_step_number);

        $step2 = new ProcessStep(['step_number' => null, 'sort_order' => 5]);
        $this->assertEquals('05', $step2->formatted_step_number);
    }

    public function test_cta_links_to_custom_type(): void
    {
        $page = ProcessPage::create([
            'cta_url' => '/custom',
            'status' => ProcessPageStatus::PUBLISHED,
        ]);

        $response = $this->get('/our-process');

        $response->assertStatus(200);
        $response->assertSee('/custom');
    }

    public function test_unauthorized_user_cannot_access_process_cms(): void
    {
        $response = $this->get('/admin/process-pages');
        $response->assertRedirect('/admin/login');
    }

    public function test_authorized_admin_can_access_process_cms(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/process-pages');

        $response->assertRedirect('/admin/process-pages/manage');

        $editResponse = $this->actingAs($admin)->get('/admin/process-pages/manage');
        $editResponse->assertStatus(200);
    }

    public function test_updating_process_page_clears_cache(): void
    {
        $page = ProcessPage::create([
            'title' => 'Initial Title',
            'status' => ProcessPageStatus::PUBLISHED,
        ]);

        // Access page to populate cache
        ProcessPage::getPublishedPage();

        $page->update(['title' => 'Updated Process Title']);

        $response = $this->get('/our-process');
        $response->assertSee('Updated Process Title');
    }
}
