<?php

namespace Database\Seeders;

use App\Models\AboutArtisan;
use App\Models\AboutPage;
use App\Models\AboutTimelineStep;
use App\Models\AboutValue;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aboutPage = AboutPage::updateOrCreate(
            ['id' => 1],
            [
                'eyebrow' => 'THE MAISON · EST. 2012',
                'hero_title' => 'The quiet brilliance of pure solitaires.',
                'hero_description' => 'Solitaire Fine Jewels was founded with a singular conviction: fine jewellery should transcend transient fashion. Crafted in hallmarked gold and certified diamonds, every jewel is shaped to illuminate life\'s greatest milestones.',
                'hero_image' => 'about/hero_atelier.png',
                'hero_image_alt' => 'Master goldsmith setting a solitaire diamond ring in atelier',

                'founder_quote' => '"We curate each stone by hand. Perfection is not hurried — it is sculpted with timeless discipline."',
                'founder_name' => '— AARAV MEHTA, MASTER JEWELLER & FOUNDER',

                'story_eyebrow' => 'OUR HERITAGE',
                'story_title' => 'Fourteen years of bespoke mastery.',
                'story_content' => "We are an intimate atelier of master gemologists, precision CAD artists, and seasoned bench goldsmiths. Every solitaire ring, necklace, and bracelet is crafted individually to order — cast in certified 14KT, 18KT, and 22KT hallmarked gold.\n\nWe ethically source only conflict-free, 100% certified natural diamonds with peerless clarity and cut grades. From private bridal commissions to signature solitaire bands, each piece carries an indelible mark of artisanal integrity.",
                'materials_content' => 'Certified authenticity: 100% natural certified diamonds (IGI / GIA certified), BIS Hallmarked 14K–22K gold, and ethically vetted conflict-free gemstones.',

                'visit_cta_text' => 'VISIT OUR SHOWROOM',
                'visit_cta_url' => '/contact',

                'seo_title' => 'About Us — Solitaire Fine Jewels Atelier',
                'seo_description' => 'Discover the story, diamond craftsmanship, and bespoke fine jewellery heritage of Solitaire Fine Jewels.',
                'is_published' => true,
            ]
        );

        // Seed 3 Editorial Values
        $values = [
            [
                'number' => '01',
                'title' => 'Authenticity',
                'description' => '100% certified natural diamonds and BIS hallmarked gold. Zero compromise.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'number' => '02',
                'title' => 'Mastery',
                'description' => 'Hand-set by master craftsmen with decades of bench goldsmithing experience.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'number' => '03',
                'title' => 'Timelessness',
                'description' => 'Heirloom creations designed to be treasured across generations.',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($values as $val) {
            AboutValue::updateOrCreate(
                ['about_page_id' => $aboutPage->id, 'number' => $val['number']],
                $val
            );
        }

        // Seed 5 Chronicle Timeline Steps
        $timelineSteps = [
            [
                'year' => '2012',
                'title' => 'The First Solitaire',
                'description' => 'A modest private bench crafting one-of-a-kind bespoke diamond rings for discerning collectors.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'year' => '2016',
                'title' => 'The Atelier Bench',
                'description' => 'Established our dedicated master crafting studio with state-of-the-art micro-setting and optical grading benches.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'year' => '2020',
                'title' => 'Certified Excellence',
                'description' => 'Standardized 100% IGI and GIA laboratory certifications for all diamond solitaires.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'year' => '2023',
                'title' => 'Bespoke Bridal Suite',
                'description' => 'Launched our private client concierge for custom bridal jewellery and customized engagement creations.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'year' => '2026',
                'title' => 'Solitaire Fine Jewels',
                'description' => 'Serving global collectors and families celebrating life’s most profound milestones.',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($timelineSteps as $step) {
            AboutTimelineStep::updateOrCreate(
                ['about_page_id' => $aboutPage->id, 'sort_order' => $step['sort_order']],
                $step
            );
        }

        // Seed 3 Artisans / Craft Team Members
        $artisans = [
            [
                'name' => 'Aarav Mehta',
                'role' => 'FOUNDER & MASTER GEMOLOGIST',
                'image_path' => 'about/maker_camille.png',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Devika Sharma',
                'role' => 'HEAD CAD & DESIGN ATELIER',
                'image_path' => 'about/maker_yanis.png',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Rajesh Verma',
                'role' => 'MASTER GOLDSMITH & STONE SETTER',
                'image_path' => 'about/maker_ines.png',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($artisans as $artisan) {
            AboutArtisan::updateOrCreate(
                ['about_page_id' => $aboutPage->id, 'sort_order' => $artisan['sort_order']],
                $artisan
            );
        }

        AboutPage::clearCache();
    }
}
