<?php

namespace Database\Seeders;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            [
                'name' => 'Tables',
                'slug' => 'tables',
                'description' => 'Custom poured river tables, dining, and console furniture.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Wall Art',
                'slug' => 'wall-art',
                'description' => 'Large scale resin panels, relief artworks, and diptychs.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Interiors',
                'slug' => 'interiors',
                'description' => 'Architectural resin installations, counters, and spatial features.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Objects',
                'slug' => 'objects',
                'description' => 'Sculptural resin clocks, trays, coasters, and studio vessels.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Studio',
                'slug' => 'studio',
                'description' => 'Behind the scenes at the atelier — pouring, curing, and polishing.',
                'sort_order' => 5,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $catData) {
            $categories[$catData['slug']] = GalleryCategory::updateOrCreate(
                ['slug' => $catData['slug']],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                    'sort_order' => $catData['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $itemsTemplate = [
            [
                'category_slug' => 'tables',
                'title' => 'Sègre — River N°04',
                'slug' => 'segre-river-n04',
                'location' => 'Bordeaux, France',
                'image_path' => 'gallery/segre_river_table.png',
                'image_alt' => 'Sègre River N°04 Resin Table',
            ],
            [
                'category_slug' => 'wall-art',
                'title' => 'Mira — Wall N°02',
                'slug' => 'mira-wall-n02',
                'location' => 'Biarritz, France',
                'image_path' => 'gallery/mira_wall.png',
                'image_alt' => 'Mira Wall N°02 Resin Panel Art',
            ],
            [
                'category_slug' => 'interiors',
                'title' => 'Bordeaux Apartment Counter',
                'slug' => 'bordeaux-apartment-counter',
                'location' => 'Bordeaux, France',
                'image_path' => 'gallery/bordeaux_counter.png',
                'image_alt' => 'Bordeaux Apartment Custom Resin Counter',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Nocturne Clock',
                'slug' => 'nocturne-clock',
                'location' => 'Paris Studio',
                'image_path' => 'gallery/nocturne_clock.png',
                'image_alt' => 'Nocturne Resin Clock',
            ],
            [
                'category_slug' => 'tables',
                'title' => 'Bench Three, Morning',
                'slug' => 'bench-three-morning',
                'location' => 'Lyon, France',
                'image_path' => 'gallery/bench_three.png',
                'image_alt' => 'Bench Three Resin Live Edge Furniture',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Adriatique Coasters',
                'slug' => 'adriatique-coasters',
                'location' => 'Atelier Craft',
                'image_path' => 'gallery/coasters.png',
                'image_alt' => 'Adriatique Resin Coasters Set',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Estuaire Tray',
                'slug' => 'estuaire-tray',
                'location' => 'Private Residence',
                'image_path' => 'gallery/tray.png',
                'image_alt' => 'Estuaire Resin Serving Tray',
            ],
            [
                'category_slug' => 'studio',
                'title' => 'The Pouring Process',
                'slug' => 'the-pouring-process',
                'location' => 'Maison Résine Studio',
                'image_path' => 'gallery/the_pour.png',
                'image_alt' => 'Artisan pouring liquid resin',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Botanica Pendant Light',
                'slug' => 'botanica-pendant-light',
                'location' => 'Geneva, Switzerland',
                'image_path' => 'gallery/botanica.png',
                'image_alt' => 'Botanica Resin Pendant Lamp',
            ],
            [
                'category_slug' => 'wall-art',
                'title' => 'Seuil Threshold Plate',
                'slug' => 'seuil-threshold-plate',
                'location' => 'Paris, France',
                'image_path' => 'gallery/seuil_threshold.png',
                'image_alt' => 'Seuil Resin Threshold Plate Artwork',
            ],
            [
                'category_slug' => 'tables',
                'title' => 'Rhône — Dining Table N°01',
                'slug' => 'rhone-dining-table-n01',
                'location' => 'Marseille Villa',
                'image_path' => 'gallery/segre_river_table.png',
                'image_alt' => 'Rhône Dining Table Resin',
            ],
            [
                'category_slug' => 'wall-art',
                'title' => 'Azur Horizon Panel',
                'slug' => 'azur-horizon-panel',
                'location' => 'Nice Penthouse',
                'image_path' => 'gallery/mira_wall.png',
                'image_alt' => 'Azur Horizon Resin Wall Art',
            ],
            [
                'category_slug' => 'interiors',
                'title' => 'Monolith Bar Feature',
                'slug' => 'monolith-bar-feature',
                'location' => 'Lille Lounge',
                'image_path' => 'gallery/bordeaux_counter.png',
                'image_alt' => 'Monolith Bar Resin Feature',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Soleil Brass & Resin Vessel',
                'slug' => 'soleil-brass-vessel',
                'location' => 'Atelier Collection',
                'image_path' => 'gallery/nocturne_clock.png',
                'image_alt' => 'Soleil Brass Resin Vessel',
            ],
            [
                'category_slug' => 'tables',
                'title' => 'Loire Coffee Table',
                'slug' => 'loire-coffee-table',
                'location' => 'Tours Residence',
                'image_path' => 'gallery/bench_three.png',
                'image_alt' => 'Loire Coffee Table',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Corail Sculptural Bowl',
                'slug' => 'corail-sculptural-bowl',
                'location' => 'Cannes Studio',
                'image_path' => 'gallery/coasters.png',
                'image_alt' => 'Corail Sculptural Bowl',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Capri Walnut Serving Board',
                'slug' => 'capri-walnut-serving-board',
                'location' => 'Private Villa',
                'image_path' => 'gallery/tray.png',
                'image_alt' => 'Capri Walnut Serving Board',
            ],
            [
                'category_slug' => 'studio',
                'title' => 'Curing Room at Dusk',
                'slug' => 'curing-room-at-dusk',
                'location' => 'Maison Résine Studio',
                'image_path' => 'gallery/the_pour.png',
                'image_alt' => 'Curing Room Resin',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Lumière Amber Sphere',
                'slug' => 'lumiere-amber-sphere',
                'location' => 'Zurich Gallery',
                'image_path' => 'gallery/botanica.png',
                'image_alt' => 'Lumière Amber Sphere',
            ],
            [
                'category_slug' => 'wall-art',
                'title' => 'Elysée Bronze Diptych',
                'slug' => 'elysee-bronze-diptych',
                'location' => 'Paris Hotel',
                'image_path' => 'gallery/seuil_threshold.png',
                'image_alt' => 'Elysée Bronze Diptych Wall Art',
            ],
            [
                'category_slug' => 'tables',
                'title' => 'Garonne High Console',
                'slug' => 'garonne-high-console',
                'location' => 'Toulouse Loft',
                'image_path' => 'gallery/segre_river_table.png',
                'image_alt' => 'Garonne High Console Table',
            ],
            [
                'category_slug' => 'wall-art',
                'title' => 'Vague Resin Relief',
                'slug' => 'vague-resin-relief',
                'location' => 'Monaco Yacht',
                'image_path' => 'gallery/mira_wall.png',
                'image_alt' => 'Vague Resin Relief Wall Art',
            ],
            [
                'category_slug' => 'interiors',
                'title' => 'Palais Reception Counter',
                'slug' => 'palais-reception-counter',
                'location' => 'Strasbourg Hotel',
                'image_path' => 'gallery/bordeaux_counter.png',
                'image_alt' => 'Palais Reception Counter',
            ],
            [
                'category_slug' => 'objects',
                'title' => 'Clair de Lune Timepiece',
                'slug' => 'clair-de-lune-timepiece',
                'location' => 'Atelier Gallery',
                'image_path' => 'gallery/nocturne_clock.png',
                'image_alt' => 'Clair de Lune Timepiece',
            ],
        ];

        foreach ($itemsTemplate as $order => $itemData) {
            $cat = $categories[$itemData['category_slug']] ?? null;
            if (!$cat) continue;

            GalleryItem::updateOrCreate(
                ['slug' => $itemData['slug']],
                [
                    'gallery_category_id' => $cat->id,
                    'title' => $itemData['title'],
                    'description' => 'Handcrafted atelier piece in solid timber and crystal resin.',
                    'caption' => 'Installed in ' . $itemData['location'],
                    'location' => $itemData['location'],
                    'image_path' => $itemData['image_path'],
                    'image_alt' => $itemData['image_alt'],
                    'sort_order' => $order + 1,
                    'is_featured' => ($order % 3 === 0),
                    'is_active' => true,
                ]
            );
        }
    }
}
