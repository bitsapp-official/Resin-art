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
                'description' => 'We hand-select individual certified natural and lab solitaires based on superior 4C parameters — assessing cut symmetry, optical brilliance, and fire under magnification.',
                'image_path' => 'process/conversation.png',
                'image_alt' => 'Gemologist inspecting certified diamond solitaire with precision loupe',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'step_number' => '02',
                'title' => 'Concept Sketch & 3D CAD Modeling',
                'description' => 'Proportions are modeled down to fractions of a millimeter. Every prong, bezel, and gallery rail is engineered to maximize light transmission into each gemstone.',
                'image_path' => 'process/palette_study.png',
                'image_alt' => '3D CAD rendering and design blueprints of bespoke solitaire engagement ring',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'step_number' => '03',
                'title' => 'Precious Metal Casting',
                'description' => 'Cast in solid 14KT, 18KT, or 22KT hallmarked gold and platinum using vacuum investment casting for flawless metallurgical density and strength.',
                'image_path' => 'process/timber_selection.png',
                'image_alt' => 'Molten hallmarked gold being poured into precision lost-wax jewellery mold',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'step_number' => '04',
                'title' => 'Master Stone Setting',
                'description' => 'Our master setter microscopically seats and burnishes each prong around the stone. Zero movement, seamless symmetry, and maximum brilliance.',
                'image_path' => 'process/the_pour.png',
                'image_alt' => 'Bench goldsmith setting brilliant solitaire diamond under high-magnification microscope',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'step_number' => '05',
                'title' => 'High-Lustre Finishing & Hallmarking',
                'description' => 'Multi-stage polishing with diamond rouge and ultrasonic cleansing brings the gold to a mirror finish. Each piece is BIS hallmarked for guaranteed purity.',
                'image_path' => 'process/finish.png',
                'image_alt' => 'Hand polishing finished gold ring with jewellers rouge to a mirror lustre',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'step_number' => '06',
                'title' => 'Certification & Luxury Presentation',
                'description' => 'Every finished creation is verified with an official gemological laboratory certificate (IGI / GIA) and packaged in our velvet-lined keepsake case with insured white-glove courier.',
                'image_path' => 'process/delivery.png',
                'image_alt' => 'Luxury velvet presentation box with diamond ring and laboratory certification card',
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
