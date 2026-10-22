<?php

namespace Database\Seeders;

use App\Enums\ProcessPageStatus;
use App\Models\ProcessPage;
use App\Models\ProcessStep;
use Illuminate\Database\Seeder;

class ProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $page = ProcessPage::updateOrCreate(
            ['id' => 1],
            [
                'eyebrow' => 'ATELIER MASTERY',
                'title' => 'The art of fine jewellery.',
                'description' => 'From initial diamond grading and hand sketching to 3D CAD modeling, gold casting, precision stone-setting, and high-lustre polish — every jewel is an uncompromising work of art.',
                'cta_title' => 'Commission a bespoke diamond creation',
                'cta_button_text' => 'BEGIN YOUR INQUIRY',
                'cta_url' => '/custom',
                'status' => ProcessPageStatus::PUBLISHED,
                'seo_title' => 'Our Jewellery Crafting Process — Solitaire Fine Jewels',
                'seo_description' => 'Discover our step-by-step master jewellery process: diamond selection, 3D CAD modeling, BIS hallmarked gold casting, precision stone setting, and certification.',
            ]
        );

        $stepsData = [
            [
                'step_number' => '01',
                'title' => 'Diamond & Gemstone Curation',
                'description' => 'Every solitaire begins with the gemological loupe. Backed by over 20 years of diamond evaluation expertise, our master gemologists hand-select certified natural solitaires based on superior cut symmetry, fire, and microscopic optical clarity.',
                'image_path' => 'process/diamond_curation.jpg',
                'image_alt' => 'Master gemologist inspecting certified diamond solitaire with precision loupe',
                'image_caption' => '40x Magnification optical grading bench',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'step_number' => '02',
                'title' => 'Concept Sketch & 3D CAD Blueprint',
                'description' => 'Proportions are modeled down to fractions of a millimeter. Every prong claw, under-gallery rail, and tapered band is precision-engineered in 3D CAD to maximize light entry while guaranteeing heirloom structural strength.',
                'image_path' => 'process/cad_blueprint.jpg',
                'image_alt' => '3D CAD rendering and design blueprints of bespoke solitaire engagement ring',
                'image_caption' => 'Sub-millimeter parametric CAD engineering',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'step_number' => '03',
                'title' => 'Precious Metal Lost-Wax Casting',
                'description' => 'Cast in solid 14KT, 18KT, 22KT hallmarked gold and platinum using state-of-the-art lost-wax vacuum investment casting. Molten alloys achieve optimal density, zero porosity, and radiant golden warmth.',
                'image_path' => 'process/gold_casting.jpg',
                'image_alt' => 'Molten hallmarked gold being poured into precision lost-wax jewellery mold',
                'image_caption' => 'Vacuum-assisted 18KT gold investment casting',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'step_number' => '04',
                'title' => 'Microscopic Master Stone Setting',
                'description' => 'Under high-magnification stereo microscopes, our senior bench goldsmiths seat and burnish each gold prong securely around the gemstone. Zero movement, seamless symmetry, and maximum light refraction.',
                'image_path' => 'process/microscope_setting.jpg',
                'image_alt' => 'Master goldsmith setting brilliant solitaire diamond under high-magnification microscope',
                'image_caption' => 'High-magnification microscope prong burnishing',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'step_number' => '05',
                'title' => 'High-Lustre Mirror Polish & BIS Hallmarking',
                'description' => 'Five sequential stages of hand polishing with diamond rouge buffing bring the precious metal to a mirror finish. Each piece is then laser-inscribed with the official government BIS Hallmark and unique HUID purity seal.',
                'image_path' => 'process/hand_polishing.jpg',
                'image_alt' => 'Hand polishing finished gold ring with jewellers rouge to a mirror lustre',
                'image_caption' => '5-Stage mirror rouge polish & BIS laser hallmarking',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'step_number' => '06',
                'title' => 'Certification & Luxury Presentation',
                'description' => 'Every completed jewel is cross-verified against its independent laboratory certificate (IGI / GIA) and packaged in our velvet-lined keepsake case with numbered tamper-evident vault seal for 100% insured delivery worldwide.',
                'image_path' => 'process/certification_box.jpg',
                'image_alt' => 'Luxury velvet presentation box with diamond ring and laboratory certification card',
                'image_caption' => 'Independent IGI/GIA certificate & insured vault packaging',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($stepsData as $step) {
            ProcessStep::updateOrCreate(
                [
                    'process_page_id' => $page->id,
                    'sort_order' => $step['sort_order'],
                ],
                $step
            );
        }

        ProcessPage::clearCache();
    }
}
