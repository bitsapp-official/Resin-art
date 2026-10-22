<?php

namespace Tests\Feature;

use App\Models\PolicyPage;
use Database\Seeders\PolicyPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PolicyPageSeeder::class);
    }

    public function test_shipping_policy_page_loads_and_has_free_worldwide_shipping_content(): void
    {
        $response = $this->get(route('legal.shipping'));

        $response->assertStatus(200);
        $response->assertSee('Shipping &amp; Delivery Policy', false);
        $response->assertSee('COMPLIMENTARY WORLDWIDE DELIVERY');
        $response->assertSee('100% Free Worldwide Insured Shipping');
        $response->assertSee('Bespoke Made-to-Order Production Timeline');
        $response->assertSee('100% Door-to-Door Transit Insurance');
        $response->assertSee('Mandatory Adult Signature & OTP Verification', false);
    }

    public function test_return_policy_page_loads_and_has_strict_made_to_order_no_exchange_policy(): void
    {
        $response = $this->get(route('legal.return'));

        $response->assertStatus(200);
        $response->assertSee('Return, Exchange &amp; Cancellation Policy', false);
        $response->assertSee('BESPOKE MADE-TO-ORDER POLICY');
        $response->assertSee('Bespoke Made-to-Order Standard (Sell-Only / Final Sale)');
        $response->assertSee('WE DO NOT ACCEPT RETURNS, EXCHANGES, OR REFUNDS. ALL SALES ARE FINAL.');
        $response->assertSee('pure sell-only model');
        $response->assertSee('3 Hours');
        $response->assertSee('Mandatory Unboxing Video Requirement');
        $response->assertSee('Lifetime Authenticity & BIS Hallmarking Guarantee', false);
    }

    public function test_privacy_and_terms_pages_load_successfully(): void
    {
        $resPrivacy = $this->get(route('legal.privacy'));
        $resPrivacy->assertStatus(200);
        $resPrivacy->assertSee('Privacy &amp; Client Confidentiality', false);
        $resPrivacy->assertSee('Bank-Grade Transaction Security');

        $resTerms = $this->get(route('legal.terms'));
        $resTerms->assertStatus(200);
        $resTerms->assertSee('Terms &amp; Conditions', false);
        $resTerms->assertSee('Bespoke Made-to-Order Nature of Goods');
    }
}
