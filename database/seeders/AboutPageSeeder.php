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
                'eyebrow' => 'THE MAISON · OVER TWO DECADES OF ATELIER MASTERY',
                'hero_title' => 'Twenty years shaping the brilliance of certified solitaires.',
                'hero_description' => 'For over two decades, Solitaire Fine Jewels has dedicated its craft to the art of bespoke fine jewellery. Every creation is individually cast in BIS hallmarked gold and handset with certified natural diamonds by master bench goldsmiths.',
                'hero_image' => 'about/hero_atelier.jpg',
                'hero_image_alt' => 'Senior master goldsmith with 20 years experience crafting diamond solitaire at atelier bench',

                'founder_quote' => '"Over twenty years at the bench have taught us that true luxury cannot be mass-produced. Every diamond possesses an individual soul, and every setting must be sculpted by human hands with timeless discipline."',
                'founder_name' => '— AARAV MEHTA, MASTER JEWELLER & FOUNDER (20+ YEARS ATELIER EXPERIENCE)',

                'story_eyebrow' => 'OUR 20-YEAR HERITAGE',
                'story_title' => 'Two decades of uncompromising bench craftsmanship.',
                'story_content' => "Founded over twenty years ago in 2004 by master gemologists and bench goldsmiths, Solitaire Fine Jewels emerged as an intimate private atelier dedicated to crafting custom engagement rings and heirloom jewels for connoisseurs.\n\nFor over two decades, we have held fast to one inviolable principle: zero mass manufacturing. Every solitary ring, necklace, and bracelet is crafted individually to order — cast in certified 14KT, 18KT, and 22KT hallmarked gold and handset under high magnification.\n\nWe ethically source only conflict-free, 100% certified natural diamonds with peerless clarity and cut grades. From private bridal commissions to signature solitaire bands, each piece carries an indelible mark of generational artisanal integrity.",
                'materials_content' => 'Certified authenticity: 100% natural certified diamonds (IGI / GIA certified), BIS Hallmarked 14K–22K gold, and ethically vetted conflict-free gemstones.',

                'visit_cta_text' => 'VISIT OUR SHOWROOM',
                'visit_cta_url' => '/contact',

                'seo_title' => 'About Us — 20 Years of Fine Jewellery Mastery — Solitaire Fine Jewels',
                'seo_description' => 'Discover the 20-year fine jewellery heritage, master goldsmiths, and certified diamond atelier of Solitaire Fine Jewels.',
                'is_published' => true,
            ]
        );

        // Seed 3 Editorial Values
        $values = [
            [
                'number' => '01',
                'title' => '20-Year Bench Mastery',
                'description' => 'Handcrafted by senior bench goldsmiths with over two decades of microscopic diamond setting expertise.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'number' => '02',
                'title' => '100% Certified Authenticity',
                'description' => '100% natural certified diamonds with independent IGI/GIA certificates and government BIS hallmarked gold. Zero compromise.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'number' => '03',
                'title' => 'Bespoke Made-to-Order',
                'description' => 'Strictly made to order for each patron. No warehouse mass production, no pre-owned re-stocking.',
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

        // Seed 5 Chronicle Timeline Steps (2004 - 2026, 20+ Years)
        $timelineSteps = [
            [
                'year' => '2004',
                'title' => 'The Inception Bench',
                'description' => 'Founded over 20 years ago as an artisanal bench workshop in Mumbai\'s historic jewellery quarter, crafting bespoke diamond solitaires for discerning collectors.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'year' => '2009',
                'title' => 'Gemological Purity Network',
                'description' => 'Established direct sourcing partnerships with Antwerp and Surat diamond bourses for 100% conflict-free certified natural diamonds.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'year' => '2015',
                'title' => '3D CAD & Micro-Setting Innovation',
                'description' => 'Pioneered computerized 3D parametric CAD modeling and 40x magnification microscopic prong setting for zero-tolerance security.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'year' => '2020',
                'title' => 'Mandatory BIS Hallmarking & IGI Standardization',
                'description' => 'Institutionalized 100% Bureau of Indian Standards (BIS) Hallmarking with laser-inscribed HUID and independent IGI/GIA diamond certification.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'year' => '2026',
                'title' => 'Solitaire Fine Jewels Global Maison',
                'description' => 'Serving patrons and collectors across more than 30 countries with complimentary insured worldwide shipping and bespoke made-to-order craft.',
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
                'role' => 'FOUNDER & MASTER GEMOLOGIST (20+ YRS ATELIER EXP)',
                'image_path' => 'about/artisan_aarav.jpg',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Devika Sharma',
                'role' => 'HEAD OF 3D CAD & ATELIER DESIGN',
                'image_path' => 'about/artisan_devika.jpg',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Rajesh Verma',
                'role' => 'MASTER GOLDSMITH & CHIEF STONE SETTER (20+ YRS EXP)',
                'image_path' => 'about/artisan_rajesh.jpg',
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
