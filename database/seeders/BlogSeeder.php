<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            ['name' => 'Diamonds & Gemstones', 'slug' => 'diamonds-gemstones', 'sort_order' => 1, 'description' => 'Deep dives into certified 4Cs, optical brilliance, and gemstone selection.'],
            ['name' => 'Gold & Metallurgy', 'slug' => 'gold-metallurgy', 'sort_order' => 2, 'description' => 'Exploring 14KT, 18KT, and 22KT hallmarked alloys, durability and tones.'],
            ['name' => 'Care & Preservation', 'slug' => 'care-preservation', 'sort_order' => 3, 'description' => 'Essential guides for keeping your diamond jewellery and precious metals radiant forever.'],
            ['name' => 'Atelier Craft', 'slug' => 'atelier-craft', 'sort_order' => 4, 'description' => 'Behind the jeweler bench: micro-setting, CAD design, and lost-wax casting.'],
            ['name' => 'Bridal & Bespoke', 'slug' => 'bridal-bespoke', 'sort_order' => 5, 'description' => 'Inspirations, ring sizing, and commissioning one-of-a-kind bespoke heirlooms.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = \App\Models\BlogCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        $postsData = [
            [
                'category_slug' => 'diamonds-gemstones',
                'title' => 'The Anatomy of a Flawless Solitaire',
                'slug' => 'the-anatomy-of-a-flawless-solitaire',
                'excerpt' => 'Beyond the 4Cs: how light return, optical symmetry, and table percentages define a truly breathtaking diamond.',
                'content' => "<p>While carat weight and clarity grades often dominate initial conversations, the true life of a diamond lives in its cut. A masterfully cut diamond acts as a miniature hall of mirrors, capturing light from all angles and returning it in a fireworks display of brilliance and spectral fire.</p>\n\n<p>At Solitaire Fine Jewels, our gemologists inspect table percentages, crown angles, and pavilion depths under 40x magnification. When proportions align harmoniously, zero light leaks through the pavilion facets — producing maximum optical scintillation.</p>\n\n<blockquote>\"A diamond is not merely a gemstone; it is an enduring repository of light, memory, and personal milestone.\"</blockquote>\n\n<p>Every solitaire in our collection is independently certified by international laboratories (IGI or GIA), guaranteeing authenticity and uncompromised grading rigor.</p>",
                'featured_image' => 'images/catalog/diamond_solitaire_ring.jpg',
                'author_name' => 'Aarav Mehta',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(12),
                'seo_title' => 'The Anatomy of a Flawless Solitaire — Solitaire Fine Jewels',
                'seo_description' => 'Understanding diamond cut, symmetry, and light return in bespoke solitaire rings.',
            ],
            [
                'category_slug' => 'gold-metallurgy',
                'title' => 'Gold Purity Demystified: 14KT vs 18KT vs 22KT',
                'slug' => 'gold-purity-demystified-14k-vs-18k-vs-22k',
                'excerpt' => 'Which gold alloy is right for your lifestyle? Comparing durability, rich luster, and BIS hallmarking.',
                'content' => "<p>Choosing between 14KT, 18KT, and 22KT gold is one of the most important decisions when curating fine jewellery. Pure 24KT gold is inherently soft and prone to deformation, which is why master goldsmiths alloy it with precious silver, copper, and palladium to create durable, heirloom-grade metals.</p>\n\n<p><strong>18KT Gold (75.0% pure gold):</strong> The international luxury standard for engagement rings and solitaire jewels. It delivers an exquisite warm glow with superior scratch resistance for daily wear.</p>\n\n<p><strong>14KT Gold (58.5% pure gold):</strong> Exceptionally durable and ideal for high-impact everyday pieces, active lifestyles, and delicate micro-pavé settings.</p>\n\n<p><strong>22KT Gold (91.6% pure gold):</strong> Revered in traditional heirloom and bridal suites for its rich, unmistakable sun-drenched golden warmth. Every piece from our atelier carries a laser-engraved BIS hallmark verifying its exact purity.</p>",
                'featured_image' => 'images/catalog/temple_gold_bangle.jpg',
                'author_name' => 'Rajesh Verma',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(8),
                'seo_title' => '14KT vs 18KT vs 22KT Gold Guide — Solitaire Fine Jewels',
                'seo_description' => 'Everything you need to know about BIS hallmarked gold purities and choosing the best alloy for fine jewellery.',
            ],
            [
                'category_slug' => 'care-preservation',
                'title' => 'Essential Care for Your Diamond Fine Jewels',
                'slug' => 'essential-care-guide-for-diamond-jewellery',
                'excerpt' => 'Diamonds are resilient, but fine prongs and gold settings deserve gentle care. Here is how to keep them brilliant.',
                'content' => "<p>Diamonds are the hardest known mineral on earth, yet daily exposure to hand lotions, cooking oils, and environmental dust can coat facet junctions with a translucent film that dulls light transmission.</p>\n\n<p>To restore your jewellery’s showroom brilliance at home:</p>\n<ul>\n<li>Submerge your piece in warm water with a few drops of mild pH-neutral soap for 15 minutes.</li>\n<li>Gently brush behind the diamond basket using a soft-bristled baby toothbrush.</li>\n<li>Rinse thoroughly in lukewarm running water and pat dry with a lint-free microfibre jewellery cloth.</li>\n</ul>\n<p>We recommend bringing your solitaire pieces to our atelier annually for a complimentary ultrasonic cleanse and prong tightness inspection.</p>",
                'featured_image' => 'images/catalog/diamond_ring_box.jpg',
                'author_name' => 'Atelier Concierge',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(5),
                'seo_title' => 'Diamond & Gold Jewellery Care Guide — Solitaire Fine Jewels',
                'seo_description' => 'How to clean, inspect, and preserve certified diamond rings and gold jewellery at home.',
            ],
            [
                'category_slug' => 'atelier-craft',
                'title' => 'The Art of Micro-Prong Stone Setting',
                'slug' => 'the-art-of-micro-prong-stone-setting',
                'excerpt' => 'Working under 40x magnification: how our setters secure pavé diamonds with seamless symmetry.',
                'content' => "<p>Micro-prong setting is one of the most demanding disciplines at the jeweller's bench. The artisan operates entirely through stereoscopic binocular microscopes, carving miniature bead claws from solid hallmarked gold.</p>\n\n<p>Each miniature melee diamond must sit at precisely the same depth and table angle. If one stone tilts by even a fraction of a millimeter, the optical continuity is disrupted. Our bench craftsmen spend hours burnishing each claw to ensure a snag-free, mirror-polished touch that glides smoothly against silk.</p>",
                'featured_image' => 'images/catalog/diamond_eternity_band.jpg',
                'author_name' => 'Rajesh Verma',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(3),
                'seo_title' => 'Inside the Bench: Micro-Prong Setting — Solitaire Fine Jewels',
                'seo_description' => 'Behind the scenes: the precision engineering behind pavé diamond setting and prong security.',
            ],
            [
                'category_slug' => 'bridal-bespoke',
                'title' => 'Commissioning a Bespoke Engagement Ring',
                'slug' => 'commissioning-a-bespoke-engagement-ring',
                'excerpt' => 'From initial rough sketches to the final velvet box — what the six-week bespoke journey looks like.',
                'content' => "<p>A custom engagement ring is a deeply intimate collaboration. The process begins with an exploratory conversation exploring personal aesthetics, diamond shapes (round brilliant, emerald cut, oval, or pear), and lifestyle preferences.</p>\n\n<p>Our design team creates high-resolution 3D CAD renders allowing you to examine the ring from every conceivable viewpoint. Once approved, the piece is vacuum cast in your choice of gold purity and hand-finished by our master goldsmiths over four to six weeks.</p>",
                'featured_image' => 'images/catalog/emerald_cut_ring.jpg',
                'author_name' => 'Devika Sharma',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(2),
                'seo_title' => 'Bespoke Engagement Ring Commissioning — Solitaire Fine Jewels',
                'seo_description' => 'Step-by-step guide to designing a custom diamond engagement ring with our master jewellers.',
            ],
            [
                'category_slug' => 'atelier-craft',
                'title' => 'Inside the Solitaire Atelier',
                'slug' => 'inside-the-solitaire-atelier',
                'excerpt' => 'A photo essay through our benches where centuries of goldsmithing heritage meet modern laser precision.',
                'content' => "<p>Step inside the Solitaire Fine Jewels atelier in Mumbai. Here, traditional hand-drawn goldsmithing benches sit alongside state-of-the-art laser welders and optical grading laboratories.</p>\n\n<p>Every piece in our permanent collection and every custom commission is created in-house. We maintain complete custody of each diamond from certified raw parcel to the final stamped hallmark, ensuring absolute ethical transparency and uncompromising quality.</p>",
                'featured_image' => 'images/catalog/diamond_tennis_bracelet.jpg',
                'author_name' => 'Aarav Mehta',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDay(),
                'seo_title' => 'Inside the Solitaire Atelier — Solitaire Fine Jewels',
                'seo_description' => 'Behind the scenes at our master fine jewellery and certified diamond crafting studio.',
            ],
        ];

        foreach ($postsData as $post) {
            $catSlug = $post['category_slug'];
            unset($post['category_slug']);
            $post['category_id'] = $categories[$catSlug]->id;

            \App\Models\BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }

        \App\Models\BlogCategory::clearCache();
    }
}
