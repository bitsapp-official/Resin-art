<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Contact Page Settings
            ['key' => 'contact_hero_badge', 'value' => 'Jewellery Concierge', 'group' => 'contact', 'label' => 'Hero Badge Text', 'type' => 'text'],
            ['key' => 'contact_hero_title', 'value' => 'Connect with our master jewellers.', 'group' => 'contact', 'label' => 'Hero Title', 'type' => 'text'],
            ['key' => 'contact_hero_subtitle', 'value' => 'Bespoke solitaires, bulk wholesale inquiries, diamond certifications or showroom appointments. We answer every inquiry promptly.', 'group' => 'contact', 'label' => 'Hero Subtitle', 'type' => 'textarea'],
            ['key' => 'contact_studio_address', 'value' => "Delhi Flagship Showroom\nConnaught Place, New Delhi, India", 'group' => 'contact', 'label' => 'Studio Address', 'type' => 'textarea'],
            ['key' => 'contact_studio_hours', 'value' => "Monday – Sunday\n11:00 AM – 8:30 PM", 'group' => 'contact', 'label' => 'Opening Hours', 'type' => 'textarea'],
            ['key' => 'contact_studio_email', 'value' => 'care@solitairefinejewels.com', 'group' => 'contact', 'label' => 'Contact Email', 'type' => 'text'],
            ['key' => 'contact_studio_phone', 'value' => '+91 98201 45678', 'group' => 'contact', 'label' => 'Contact Phone', 'type' => 'text'],

            // Footer Settings
            ['key' => 'footer_copyright_text', 'value' => '© 2026 SOLITAIRE & FINE JEWELS · ALL RIGHTS RESERVED', 'group' => 'footer', 'label' => 'Copyright Notice', 'type' => 'text'],
            ['key' => 'footer_tagline', 'value' => '100% certified natural diamonds, solitaire engagement rings, and BIS hallmarked fine gold jewellery crafted with timeless perfection.', 'group' => 'footer', 'label' => 'Footer Tagline', 'type' => 'text'],
            ['key' => 'footer_instagram_url', 'value' => 'https://instagram.com', 'group' => 'footer', 'label' => 'Instagram Profile URL', 'type' => 'url'],
            ['key' => 'footer_youtube_url', 'value' => 'https://youtube.com', 'group' => 'footer', 'label' => 'YouTube Channel URL', 'type' => 'url'],
            ['key' => 'footer_pinterest_url', 'value' => 'https://pinterest.com', 'group' => 'footer', 'label' => 'Pinterest Profile URL', 'type' => 'url'],
            ['key' => 'footer_facebook_url', 'value' => 'https://facebook.com', 'group' => 'footer', 'label' => 'Facebook Page URL', 'type' => 'url'],
            
            // Global Trust Badges
            ['key' => 'global_badge_1_title', 'value' => '100% Certified', 'group' => 'general', 'label' => 'Global Trust Badge 1 Title', 'type' => 'text'],
            ['key' => 'global_badge_1_subtitle', 'value' => 'NATURAL DIAMONDS', 'group' => 'general', 'label' => 'Global Trust Badge 1 Subtitle', 'type' => 'text'],
            ['key' => 'global_badge_2_title', 'value' => 'BIS Hallmarked', 'group' => 'general', 'label' => 'Global Trust Badge 2 Title', 'type' => 'text'],
            ['key' => 'global_badge_2_subtitle', 'value' => '14K - 22K GOLD', 'group' => 'general', 'label' => 'Global Trust Badge 2 Subtitle', 'type' => 'text'],
            ['key' => 'global_badge_3_title', 'value' => 'Free Insured', 'group' => 'general', 'label' => 'Global Trust Badge 3 Title', 'type' => 'text'],
            ['key' => 'global_badge_3_subtitle', 'value' => 'PAN INDIA DISPATCH', 'group' => 'general', 'label' => 'Global Trust Badge 3 Subtitle', 'type' => 'text'],

            // Invoice & GST Settings
            ['key' => 'invoice_brand_name', 'value' => 'Solitaire Fine Jewels', 'group' => 'invoice', 'label' => 'Brand Name', 'type' => 'text'],
            ['key' => 'invoice_brand_tagline', 'value' => 'Certified Natural Solitaires & Fine Gold Jewellery', 'group' => 'invoice', 'label' => 'Brand Tagline', 'type' => 'text'],
            ['key' => 'invoice_address', 'value' => "Flagship Showroom, Delhi, India", 'group' => 'invoice', 'label' => 'Atelier Address', 'type' => 'textarea'],
            ['key' => 'invoice_email', 'value' => 'care@solitairefinejewels.com', 'group' => 'invoice', 'label' => 'Billing Email', 'type' => 'text'],
            ['key' => 'invoice_phone', 'value' => '+91 98201 45678', 'group' => 'invoice', 'label' => 'Contact Phone', 'type' => 'text'],
            ['key' => 'invoice_website', 'value' => 'www.solitairefinejewels.com', 'group' => 'invoice', 'label' => 'Website URL', 'type' => 'url'],
            ['key' => 'invoice_gstin', 'value' => '', 'group' => 'invoice', 'label' => 'GSTIN Number', 'type' => 'text'],
            ['key' => 'invoice_tax_rate', 'value' => '3', 'group' => 'invoice', 'label' => 'GST Tax Rate %', 'type' => 'text'],
            ['key' => 'invoice_tax_label', 'value' => 'GST (3% Jewellery)', 'group' => 'invoice', 'label' => 'Tax Display Label', 'type' => 'text'],
            ['key' => 'invoice_show_tax', 'value' => '1', 'group' => 'invoice', 'label' => 'Enable Tax', 'type' => 'text'],
            ['key' => 'invoice_footer_note', 'value' => 'Thank you for choosing Solitaire Fine Jewels. Every diamond is certified and BIS hallmarked.', 'group' => 'invoice', 'label' => 'Footer Note', 'type' => 'textarea'],
            ['key' => 'invoice_authenticity_note', 'value' => 'Accompanied by an official laboratory diamond certification & BIS Hallmarking guarantee.', 'group' => 'invoice', 'label' => 'Authenticity Note', 'type' => 'textarea'],
        ];

        foreach ($settings as $setting) {
            \App\Models\SiteSetting::withoutGlobalScopes()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
