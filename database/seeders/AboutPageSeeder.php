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
                'eyebrow' => 'THE HOUSE · EST. 2013',
                'hero_title' => 'A quiet atelier.',
                'hero_description' => 'Maison Résine began in a garage in Bordeaux with a single walnut plank and a stubborn idea — that resin could hold light the way glass never quite could.',
                'hero_image' => 'about/hero_atelier.png',
                'hero_image_alt' => 'Artisan pouring vibrant translucent cyan resin onto live-edge walnut wood in atelier',

                'founder_quote' => '"We pour slowly. That is really the whole philosophy."',
                'founder_name' => '— ELÈNE MARCHAND, FOUNDER',

                'story_eyebrow' => 'OUR STORY',
                'story_title' => 'Twelve years, one rhythm.',
                'story_content' => "We are a team of four. A woodworker, two resin artists, and a quiet person who answers your letters. We work slowly on purpose — every piece is poured in one session and cured for weeks before it leaves the studio.\n\nWe only source walnut, oak and ash from responsibly managed forests in the Aquitaine region. Our resins are food-safe, low VOC, and hand-tinted with mineral pigments.",
                'materials_content' => 'Responsible sourcing & sustainable craft: sustainably harvested European hardwoods, non-toxic eco-resins, and natural organic pigments.',

                'visit_cta_text' => 'VISIT THE ATELIER',
                'visit_cta_url' => '/contact',

                'seo_title' => 'About Us — Maison Résine Atelier',
                'seo_description' => 'Discover the story, philosophy, and artisanal craftsmanship of Maison Résine in Bordeaux, France.',
                'is_published' => true,
            ]
        );

        // Seed 3 Editorial Values
        $values = [
            [
                'number' => '01',
                'title' => 'Slow',
                'description' => 'One piece at a time. No rushed pours, no shortcuts.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'number' => '02',
                'title' => 'Honest',
                'description' => 'Real materials. Real hands. Real edges.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'number' => '03',
                'title' => 'Quiet',
                'description' => 'Objects that speak softly, and last.',
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
                'year' => '2016',
                'title' => 'A kitchen table',
                'description' => 'The first pour, in a rented flat in Talence, on a table that did not survive it.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'year' => '2018',
                'title' => 'The atelier',
                'description' => 'We took the ground floor of an old wine warehouse near the Garonne. Two benches, zero heating.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'year' => '2021',
                'title' => 'First custom project abroad',
                'description' => 'A twelve-metre reception wall in Copenhagen, poured in fourteen panels over two winter months.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'year' => '2023',
                'title' => 'The drying loft',
                'description' => 'We began sourcing and drying our own European walnut and oak, two years ahead of production.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'year' => '2026',
                'title' => 'Four benches',
                'description' => 'Four makers, one signature per piece, and a waiting list we are quietly proud of.',
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
                'name' => 'Camille Devaux',
                'role' => 'FOUNDER · MASTER POURER',
                'image_path' => 'about/maker_camille.png',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Yanis Roux',
                'role' => 'TIMBER & JOINERY',
                'image_path' => 'about/maker_yanis.png',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Ines Ferreira',
                'role' => 'COLOUR & PIGMENT',
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
