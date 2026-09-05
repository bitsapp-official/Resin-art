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
            ['key' => 'contact_hero_badge', 'value' => 'Correspondence', 'group' => 'contact', 'label' => 'Hero Badge Text', 'type' => 'text'],
            ['key' => 'contact_hero_title', 'value' => 'Write to the atelier.', 'group' => 'contact', 'label' => 'Hero Title', 'type' => 'text'],
            ['key' => 'contact_hero_subtitle', 'value' => 'Custom orders, trade inquiries, press or simply to say hello. We answer every inquiry within 24 hours.', 'group' => 'contact', 'label' => 'Hero Subtitle', 'type' => 'textarea'],
            ['key' => 'contact_studio_address', 'value' => "14 rue des Étoiles\n33000 Bordeaux, France", 'group' => 'contact', 'label' => 'Studio Address', 'type' => 'textarea'],
            ['key' => 'contact_studio_hours', 'value' => "By appointment · Tuesday – Saturday\n10h – 18h", 'group' => 'contact', 'label' => 'Opening Hours', 'type' => 'textarea'],
            ['key' => 'contact_studio_email', 'value' => 'hello@maisonresine.co', 'group' => 'contact', 'label' => 'Contact Email', 'type' => 'text'],
            ['key' => 'contact_studio_phone', 'value' => '+33 5 56 00 00 00', 'group' => 'contact', 'label' => 'Contact Phone', 'type' => 'text'],

            // Footer Settings
            ['key' => 'footer_copyright_text', 'value' => '© 2026 MAISON RÉSINE · BORDEAUX', 'group' => 'footer', 'label' => 'Copyright Notice', 'type' => 'text'],
            ['key' => 'footer_tagline', 'value' => 'Poured by hand — One piece at a time. Natural timber and crystalline resin forged into timeless collectors works.', 'group' => 'footer', 'label' => 'Footer Tagline', 'type' => 'text'],
            ['key' => 'footer_instagram_url', 'value' => 'https://instagram.com', 'group' => 'footer', 'label' => 'Instagram Profile URL', 'type' => 'url'],
            ['key' => 'footer_youtube_url', 'value' => 'https://youtube.com', 'group' => 'footer', 'label' => 'YouTube Channel URL', 'type' => 'url'],
            ['key' => 'footer_pinterest_url', 'value' => 'https://pinterest.com', 'group' => 'footer', 'label' => 'Pinterest Profile URL', 'type' => 'url'],
            ['key' => 'footer_facebook_url', 'value' => 'https://facebook.com', 'group' => 'footer', 'label' => 'Facebook Page URL', 'type' => 'url'],
            // Global Trust Badges
            ['key' => 'global_badge_1_title', 'value' => 'Hand-Poured', 'group' => 'general', 'label' => 'Global Trust Badge 1 Title', 'type' => 'text'],
            ['key' => 'global_badge_1_subtitle', 'value' => '100% HANDMADE', 'group' => 'general', 'label' => 'Global Trust Badge 1 Subtitle', 'type' => 'text'],
            ['key' => 'global_badge_2_title', 'value' => 'Atelier Piece', 'group' => 'general', 'label' => 'Global Trust Badge 2 Title', 'type' => 'text'],
            ['key' => 'global_badge_2_subtitle', 'value' => 'ORIGINAL ART', 'group' => 'general', 'label' => 'Global Trust Badge 2 Subtitle', 'type' => 'text'],
            ['key' => 'global_badge_3_title', 'value' => 'Free Express', 'group' => 'general', 'label' => 'Global Trust Badge 3 Title', 'type' => 'text'],
            ['key' => 'global_badge_3_subtitle', 'value' => 'PAN INDIA SHIP', 'group' => 'general', 'label' => 'Global Trust Badge 3 Subtitle', 'type' => 'text'],

            // Invoice & GST Settings
            ['key' => 'invoice_brand_name', 'value' => 'Maison Résine', 'group' => 'invoice', 'label' => 'Brand Name', 'type' => 'text'],
            ['key' => 'invoice_brand_tagline', 'value' => 'Haute Résine Atelier · Art Contemporain', 'group' => 'invoice', 'label' => 'Brand Tagline', 'type' => 'text'],
            ['key' => 'invoice_address', 'value' => "14 rue des Étoiles\n33000 Bordeaux, France", 'group' => 'invoice', 'label' => 'Atelier Address', 'type' => 'textarea'],
            ['key' => 'invoice_email', 'value' => 'atelier@maisonresine.com', 'group' => 'invoice', 'label' => 'Billing Email', 'type' => 'text'],
            ['key' => 'invoice_phone', 'value' => '+91 98201 45678', 'group' => 'invoice', 'label' => 'Contact Phone', 'type' => 'text'],
            ['key' => 'invoice_website', 'value' => 'www.maisonresine.com', 'group' => 'invoice', 'label' => 'Website URL', 'type' => 'url'],
            ['key' => 'invoice_gstin', 'value' => '', 'group' => 'invoice', 'label' => 'GSTIN Number', 'type' => 'text'],
            ['key' => 'invoice_tax_rate', 'value' => '5', 'group' => 'invoice', 'label' => 'GST Tax Rate %', 'type' => 'text'],
            ['key' => 'invoice_tax_label', 'value' => 'Estimated Taxes (GST 5%)', 'group' => 'invoice', 'label' => 'Tax Display Label', 'type' => 'text'],
            ['key' => 'invoice_show_tax', 'value' => '1', 'group' => 'invoice', 'label' => 'Enable Tax', 'type' => 'text'],
            ['key' => 'invoice_footer_note', 'value' => 'Thank you for choosing Maison Résine. Handcrafted resin art and bespoke custom pieces.', 'group' => 'invoice', 'label' => 'Footer Note', 'type' => 'textarea'],
            ['key' => 'invoice_authenticity_note', 'value' => 'Every artwork is accompanied by an embossed physical Certificate of Authenticity.', 'group' => 'invoice', 'label' => 'Authenticity Note', 'type' => 'textarea'],
        ];

        foreach ($settings as $setting) {
            \App\Models\SiteSetting::withoutGlobalScopes()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
