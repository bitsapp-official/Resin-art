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
                'eyebrow' => 'OUR PROCESS',
                'title' => 'Six weeks, one object.',
                'description' => 'From timber selection to the final hand-polish, nothing here is hurried.',
                'cta_title' => 'Have a custom piece in mind?',
                'cta_button_text' => 'SUBMIT YOUR REQUIREMENTS',
                'cta_url' => '/custom',
                'status' => ProcessPageStatus::PUBLISHED,
                'seo_title' => 'Our Process — Maison Résine Atelier',
                'seo_description' => 'Six weeks, one object. From timber selection to the final hand-polish, discover our slow artisan resin craft.',
            ]
        );

        $stepsData = [
            [
                'step_number' => '01',
                'title' => 'Conversation',
                'description' => 'Dimensions, space, timber species, resin clarity, palette. We establish the outline before any timber is cut.',
                'image_path' => 'process/conversation.png',
                'image_alt' => 'Design conversation and resin swatch study in atelier',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'step_number' => '02',
                'title' => 'Palette study',
                'description' => 'Small-scale pours to test pigment saturation, light lift, and warmth against your chosen wood. Poured and posted to you.',
                'image_path' => 'process/palette_study.png',
                'image_alt' => 'Resin color pigment petri dish studies with gold mica',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'step_number' => '03',
                'title' => 'Timber selection',
                'description' => 'Slabs are chosen from our drying loft. The live edge decides the shape of the pour.',
                'image_path' => 'process/timber_selection.png',
                'image_alt' => 'Raw live edge French walnut slabs resting in timber drying loft',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'step_number' => '04',
                'title' => 'The pour',
                'description' => 'Slow-exotherm resin, poured in climate-controlled conditions over three to five days. Zero air, absolute clarity.',
                'image_path' => 'process/the_pour.png',
                'image_alt' => 'Craftsman pouring crystal clear turquoise resin onto walnut slab',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'step_number' => '05',
                'title' => 'Finish',
                'description' => 'Planed flat, shaped by hand, and polished through progressively finer grits to a quiet, natural lustre.',
                'image_path' => 'process/finish.png',
                'image_alt' => 'Hand polishing cured resin edge with organic wax oil',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'step_number' => '06',
                'title' => 'Delivery',
                'description' => 'Cloth-boxed, or white-glove for larger works — installed in the room of your choice.',
                'image_path' => 'process/delivery.png',
                'image_alt' => 'White-glove delivery setup of live edge resin table in modern living room',
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
