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
            ['name' => 'Atelier', 'slug' => 'atelier', 'sort_order' => 1, 'description' => 'Essays and notes direct from our workshop floor.'],
            ['name' => 'Colour', 'slug' => 'colour', 'sort_order' => 2, 'description' => 'Pigment explorations, light lift, and ocean palettes.'],
            ['name' => 'Care', 'slug' => 'care', 'sort_order' => 3, 'description' => 'Long-term maintenance guides for resin and hardwood objects.'],
            ['name' => 'Craft', 'slug' => 'craft', 'sort_order' => 4, 'description' => 'In-depth articles on wood selection, curing times, and joinery.'],
            ['name' => 'Studio', 'slug' => 'studio', 'sort_order' => 5, 'description' => 'Behind the scenes at our Bordeaux resin art studio.'],
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
                'category_slug' => 'atelier',
                'title' => 'The first pour of the year',
                'slug' => 'the-first-pour-of-the-year',
                'excerpt' => 'January in the workshop is cold, and cold resin behaves like a stubborn animal. Here is how we coax it.',
                'content' => "<p>January in the workshop is cold, and cold resin behaves like a stubborn animal. The viscosity doubles, micro-bubbles refuse to rise to the surface, and exothermic reaction times stretch unexpectedly long.</p>\n\n<p>To prepare for our first pour of the year, we heat the atelier to a steady 22°C forty-eight hours in advance. The raw walnut planks, harvested from Aquitaine timber yards, rest on raised trestles so ambient warmth circulates evenly beneath the wood.</p>\n\n<blockquote>\"We pour slowly. That is really the whole philosophy — trusting that patience creates glass-like clarity.\"</blockquote>\n\n<p>When mixing two-part epoxies during winter, pre-warming the resin component in a warm water bath lowers surface tension. As the clear liquid merges with our hand-milled marine pigments, bubbles disperse before degassing in our vacuum chamber.</p>\n\n<p>This initial January piece requires three days of continuous curing before planar sanding begins. The result is a luminous deep ocean hue that holds morning light with remarkable depth.</p>",
                'featured_image' => 'blog/first_pour.png',
                'author_name' => 'Elène Marchand',
                'reading_time' => '5 MIN',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(12),
                'seo_title' => 'The First Pour of the Year — Maison Résine Atelier',
                'seo_description' => 'Inside our Bordeaux workshop: managing ambient temperatures and degassing resin during winter pours.',
            ],
            [
                'category_slug' => 'colour',
                'title' => 'Colour notes — the Tidal palette',
                'slug' => 'colour-notes-the-tidal-palette',
                'excerpt' => 'Four pigments, one white lift, and the moment a wave loses its shape.',
                'content' => "<p>Developing the Tidal palette took seven months of continuous pigment trial pours. Achieving the organic gradient of shallow coastal water meeting deep ocean currents cannot be accomplished with pre-mixed dye.</p>\n\n<p>We blend natural copper mica, deep cobalt oxide, and raw mineral turquoise. Just before the gel phase begins, a single drop of titanium white is lifted across the surface with a heat torch, recreating the foam of a breaking shore wave.</p>",
                'featured_image' => 'blog/tidal_palette.png',
                'author_name' => 'Atelier Colorist',
                'reading_time' => '4 MIN',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(8),
                'seo_title' => 'Colour Notes: The Tidal Palette — Maison Résine',
                'seo_description' => 'Exploring mineral pigment tinting and wave lifting techniques in luxury resin art.',
            ],
            [
                'category_slug' => 'care',
                'title' => 'Living with resin: a short guide',
                'slug' => 'living-with-resin-a-short-guide',
                'excerpt' => 'Resin is durable, not invincible. A few habits will keep a piece looking poured-yesterday for decades.',
                'content' => "<p>Hand-poured bio-resins are engineered for decades of daily enjoyment. However, like fine hardwoods and hand-blown glass, thoughtful care ensures your bespoke piece retains its optical brilliance.</p>\n\n<p>Clean regularly with a soft microfibre cloth dampened with warm water and mild organic soap. Avoid harsh chemical solvents, abrasive sponges, or direct hot cookware placed without trivets.</p>",
                'featured_image' => 'blog/living_guide.png',
                'author_name' => 'Studio Care Team',
                'reading_time' => '6 MIN',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(5),
                'seo_title' => 'Living with Resin: Care Guide — Maison Résine',
                'seo_description' => 'Essential care instructions for preserving live edge wood and resin furniture.',
            ],
            [
                'category_slug' => 'craft',
                'title' => 'Crafting a river table',
                'slug' => 'crafting-a-river-table',
                'excerpt' => 'From the first conversation to white-glove delivery — what the twelve weeks actually look like.',
                'content' => "<p>A bespoke river table order begins long before resin meets wood. It starts in our timber loft, selecting two sister slabs cut sequentially from the same fallen walnut trunk.</p>\n\n<p>Over twelve weeks, wood is kiln-dried to 8% moisture content, live edges are wire-brushed by hand, and multi-layer deep pours are executed in our climate-controlled studio.</p>",
                'featured_image' => 'blog/river_table.png',
                'author_name' => 'Master Joiner',
                'reading_time' => '7 MIN',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(3),
                'seo_title' => 'Crafting a River Table — Maison Résine',
                'seo_description' => 'Step-by-step walkthrough of our 12-week bespoke river table crafting process.',
            ],
            [
                'category_slug' => 'studio',
                'title' => 'Inside the Bordeaux atelier',
                'slug' => 'inside-the-bordeaux-atelier',
                'excerpt' => 'A photo essay through our workshop where wood dust meets crystal clear resin.',
                'content' => "<p>Step inside our Bordeaux studio, where four artisans work in quiet harmony. From hand-planing raw French oak to final mirror polishing, every stage of production takes place under one roof.</p>",
                'featured_image' => 'blog/inside_bordeaux.png',
                'author_name' => 'Elène Marchand',
                'reading_time' => '3 MIN',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDays(2),
                'seo_title' => 'Inside the Bordeaux Atelier — Maison Résine',
                'seo_description' => 'Behind the scenes at our Bordeaux resin and woodworking atelier.',
            ],
            [
                'category_slug' => 'craft',
                'title' => 'How we preserve a bouquet',
                'slug' => 'how-we-preserve-a-bouquet',
                'excerpt' => 'Botanical encapsulation requires silica dehydration, slow low-exotherm resin, and zero moisture.',
                'content' => "<p>Preserving delicate bridal bouquets or sentimental botanicals in crystal-clear resin blocks is a delicate science. Any residual moisture inside petals causes cloudiness or discoloration over time.</p>\n\n<p>We dehydrate flowers in specialized silica matrix baths for three weeks before suspending them in crystal clear UV-stabilized eco-resin.</p>",
                'featured_image' => 'blog/bouquet_preserve.png',
                'author_name' => 'Botanical Artist',
                'reading_time' => '5 MIN',
                'status' => \App\Enums\BlogPostStatus::PUBLISHED,
                'published_at' => now()->subDay(),
                'seo_title' => 'How We Preserve a Bouquet in Resin — Maison Résine',
                'seo_description' => 'The art and science of botanical flower preservation in optical resin.',
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
