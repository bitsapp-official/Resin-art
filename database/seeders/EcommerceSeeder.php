<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Categories
        $categoriesData = [
            [
                'name' => 'Resin Wall Art',
                'description' => 'Bespoke hand-poured fluid epoxy wall masterpieces capturing ocean waves, celestial hues, and metallic geode lines.',
                'image' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Resin Clocks',
                'description' => 'Functional art luxury timepieces with gold leaf accents, silent quartz mechanisms, and deep crystal clarity.',
                'image' => 'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Name Plates',
                'description' => 'Custom engraved resin & wood nameplates featuring preserve botanical elements and metallic calligraphy.',
                'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Serving Trays',
                'description' => 'High-gloss heat-resistant resin serving boards crafted from solid teak and walnut woods.',
                'image' => 'https://images.unsplash.com/photo-1615873968403-89e068629265?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Coasters',
                'description' => 'Handcrafted resin coaster sets infused with semi-precious crushed crystals and brass inlay borders.',
                'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'River Tables',
                'description' => 'Statement live-edge wood river tables with custom epoxy pours in turquoise, deep indigo, and smoke noir.',
                'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Home Decor',
                'description' => 'Curated luxury home accents, resin bookends, trinket dishes, and sculptured centerpieces.',
                'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Wedding Preservation',
                'description' => 'Preserve your bridal bouquet forever in crystal-clear resin blocks, spheres, and keepsakes.',
                'image' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['name']] = Category::firstOrCreate(
                ['slug' => Str::slug($c['name'])],
                [
                    'name' => $c['name'],
                    'description' => $c['description'],
                    'image' => $c['image'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Create Collections
        $collectionsData = [
            [
                'name' => 'Celestial Gold Collection',
                'description' => 'Inspired by cosmic nebula formations with 24k gold leaf foil inclusions.',
                'image' => 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Ocean Waves Series',
                'description' => 'Multi-layered 3D lacing technique creating hyper-realistic ocean shoreline tide pours.',
                'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Smokey Quartz Atelier',
                'description' => 'Moody charcoal resin blended with translucent black ink and warm brass accents.',
                'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        $collections = [];
        foreach ($collectionsData as $col) {
            $collections[$col['name']] = Collection::firstOrCreate(
                ['slug' => Str::slug($col['name'])],
                [
                    'name' => $col['name'],
                    'description' => $col['description'],
                    'image' => $col['image'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Create Resin Art Products matching reference site catalogue
        $products = [
            [
                'name' => 'Adriatique Coasters',
                'sku' => 'MR-CST-001',
                'price' => 12500.00,
                'sale_price' => 9800.00,
                'category' => 'Coasters',
                'collection' => 'Ocean Waves Series',
                'inventory_type' => 'READY_TO_SHIP',
                'stock' => 12,
                'is_featured' => false,
                'is_new' => false,
                'is_bestseller' => true,
                'short_description' => 'Set of 4',
                'description' => 'Set of 4 emerald resin coasters infused with genuine 24k gold leaf flakes and clear ocean depths.',
                'images' => [
                    '/images/catalog/resin_coasters_set.png',
                    '/images/catalog/resin_ocean_coaster.png',
                ],
                'care_instructions' => 'Wipe clean with a damp microfiber cloth.',
                'shipping_info' => 'Ships within 24-48 business hours.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'SET OF 4 (10 CM)', 'price' => 9800],
                        ['size' => 'SET OF 6 (10 CM)', 'price' => 14200],
                        ['size' => 'SET OF 8 (10 CM)', 'price' => 18500],
                    ],
                ],
            ],
            [
                'name' => 'Nocturne Wall Clock',
                'sku' => 'MR-CLK-002',
                'price' => 48000.00,
                'sale_price' => null,
                'category' => 'Resin Clocks',
                'collection' => 'Smokey Quartz Atelier',
                'inventory_type' => 'READY_TO_SHIP',
                'stock' => 5,
                'is_featured' => true,
                'is_new' => false,
                'is_bestseller' => false,
                'short_description' => 'Ø 42 cm',
                'description' => 'A silent quartz movement clock encapsulated in a swirl of deep ocean ink and gilded fluid lines. A statement piece for any room.',
                'images' => [
                    '/images/catalog/nocturne_wall_clock.png',
                    '/images/catalog/resin_wall_clock.png',
                ],
                'care_instructions' => 'Requires 1x AA battery. Wipe face clean with soft dry cloth.',
                'shipping_info' => 'Complimentary express shipping across India in a protective wooden crate.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'S - 30 CM', 'price' => 36000],
                        ['size' => 'M - 42 CM', 'price' => 48000],
                        ['size' => 'L - 60 CM', 'price' => 65000],
                    ],
                ],
            ],
            [
                'name' => 'Botanica Pendant',
                'sku' => 'MR-JWL-003',
                'price' => 18500.00,
                'sale_price' => null,
                'category' => 'Home Decor',
                'collection' => null,
                'inventory_type' => 'READY_TO_SHIP',
                'stock' => 15,
                'is_featured' => false,
                'is_new' => true,
                'is_bestseller' => false,
                'short_description' => 'Preserved flora',
                'description' => 'Real dried botanical flora preserved inside a crystal clear resin medallion pendant with antique brass trim.',
                'images' => [
                    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1000&q=80',
                ],
                'care_instructions' => 'Keep away from direct perfume sprays and heat.',
                'shipping_info' => 'Includes velvet gift pouch.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'PETITE (2.5 CM)', 'price' => 14500],
                        ['size' => 'STANDARD (4.0 CM)', 'price' => 18500],
                        ['size' => 'GRAND (5.5 CM)', 'price' => 22000],
                    ],
                ],
            ],
            [
                'name' => 'Atelier Nameplate',
                'sku' => 'MR-NMP-004',
                'price' => 24500.00,
                'sale_price' => null,
                'category' => 'Name Plates',
                'collection' => null,
                'inventory_type' => 'MADE_TO_ORDER',
                'stock' => 99,
                'is_featured' => true,
                'is_new' => false,
                'is_bestseller' => false,
                'short_description' => 'Made to order',
                'description' => 'Custom engraved acrylic and gold leaf resin executive nameplate for desks and offices.',
                'images' => [
                    'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1000&q=80',
                ],
                'care_instructions' => 'Clean with lint-free cloth.',
                'shipping_info' => 'Made to order. Dispatches in 5-7 days.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'DESK SIZE (30 × 10 CM)', 'price' => 24500],
                        ['size' => 'EXECUTIVE (45 × 15 CM)', 'price' => 32000],
                        ['size' => 'DOOR SIGN (60 × 20 CM)', 'price' => 45000],
                    ],
                ],
            ],
            [
                'name' => 'Sègre — River N°04',
                'sku' => 'MR-TBL-005',
                'price' => 380000.00,
                'sale_price' => null,
                'category' => 'River Tables',
                'collection' => 'Smokey Quartz Atelier',
                'inventory_type' => 'ONE_OF_A_KIND',
                'stock' => 1,
                'is_featured' => true,
                'is_new' => false,
                'is_bestseller' => false,
                'short_description' => 'Walnut · 180 cm',
                'description' => 'Single-slab solid walnut river dining table poured with translucent deep emerald epoxy resin.',
                'images' => [
                    'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1000&q=80',
                ],
                'care_instructions' => 'Use wood polish for timber sections.',
                'shipping_info' => 'White glove freight delivery included.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'COFFEE TABLE (120 × 60 CM)', 'price' => 180000],
                        ['size' => 'DINING TABLE (180 × 90 CM)', 'price' => 380000],
                        ['size' => 'GRAND DINING (240 × 110 CM)', 'price' => 520000],
                    ],
                ],
            ],
            [
                'name' => 'Mira — Wall N°02',
                'sku' => 'MR-ART-006',
                'price' => 98000.00,
                'sale_price' => 85000.00,
                'category' => 'Resin Wall Art',
                'collection' => 'Celestial Gold Collection',
                'inventory_type' => 'ONE_OF_A_KIND',
                'stock' => 1,
                'is_featured' => false,
                'is_new' => false,
                'is_bestseller' => true,
                'short_description' => 'Framed · 90 × 60 cm',
                'description' => 'Framed ocean wave resin artwork with 3D sculpted gold coastline textures.',
                'images' => [
                    'https://images.unsplash.com/photo-1541701494587-cb58502866ab?auto=format&fit=crop&w=1000&q=80',
                ],
                'care_instructions' => 'Dust lightly with dry cloth.',
                'shipping_info' => 'Framed and ready to hang.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'STUDIO (60 × 40 CM)', 'price' => 55000],
                        ['size' => 'GALLERY (90 × 60 CM)', 'price' => 85000],
                        ['size' => 'MONUMENTAL (120 × 80 CM)', 'price' => 125000],
                    ],
                ],
            ],
            [
                'name' => 'Estuaire Serving Tray',
                'sku' => 'MR-TRY-007',
                'price' => 28500.00,
                'sale_price' => null,
                'category' => 'Serving Trays',
                'collection' => null,
                'inventory_type' => 'READY_TO_SHIP',
                'stock' => 6,
                'is_featured' => false,
                'is_new' => true,
                'is_bestseller' => false,
                'short_description' => 'Brass handles',
                'description' => 'Emerald green resin serving tray with solid brushed brass handles.',
                'images' => [
                    'https://images.unsplash.com/photo-1615873968403-89e068629265?auto=format&fit=crop&w=1000&q=80',
                ],
                'care_instructions' => 'Hand wash with mild soap.',
                'shipping_info' => 'Ships in 2 business days.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'MEDIUM (35 × 25 CM)', 'price' => 28500],
                        ['size' => 'LARGE (45 × 30 CM)', 'price' => 36000],
                        ['size' => 'GRAND ENTERTAINER (55 × 35 CM)', 'price' => 45000],
                    ],
                ],
            ],
            [
                'name' => 'Obsidienne — Wall N°07',
                'sku' => 'MR-ART-008',
                'price' => 148000.00,
                'sale_price' => null,
                'category' => 'Resin Wall Art',
                'collection' => 'Smokey Quartz Atelier',
                'inventory_type' => 'ONE_OF_A_KIND',
                'stock' => 1,
                'is_featured' => true,
                'is_new' => false,
                'is_bestseller' => false,
                'short_description' => 'Framed · 120 × 80 cm',
                'description' => 'Deep obsidian black and metallic brass fluid resin wall study.',
                'images' => [
                    'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=1000&q=80',
                ],
                'care_instructions' => 'Avoid direct sunlight.',
                'shipping_info' => 'Ships in custom wooden crate.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'GALLERY (90 × 60 CM)', 'price' => 98000],
                        ['size' => 'ATELIER (120 × 80 CM)', 'price' => 148000],
                        ['size' => 'HERITAGE (150 × 100 CM)', 'price' => 210000],
                    ],
                ],
            ],
            [
                'name' => 'Lumen Trinket Dish',
                'sku' => 'MR-HMD-009',
                'price' => 7500.00,
                'sale_price' => null,
                'category' => 'Home Decor',
                'collection' => null,
                'inventory_type' => 'READY_TO_SHIP',
                'stock' => 20,
                'is_featured' => false,
                'is_new' => true,
                'is_bestseller' => false,
                'short_description' => 'Everyday object',
                'description' => 'Hand-poured circular resin dish for jewelry and trinkets.',
                'images' => [
                    'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?auto=format&fit=crop&w=1000&q=80',
                ],
                'care_instructions' => 'Wipe clean.',
                'shipping_info' => 'Standard packaging.',
                'attributes' => [
                    'size_variants' => [
                        ['size' => 'SMALL (Ø 10 CM)', 'price' => 5500],
                        ['size' => 'MEDIUM (Ø 15 CM)', 'price' => 7500],
                        ['size' => 'LARGE (Ø 20 CM)', 'price' => 9800],
                    ],
                ],
            ],
        ];

        foreach ($products as $pData) {
            $cat = $categories[$pData['category']] ?? null;
            $col = isset($pData['collection']) ? ($collections[$pData['collection']] ?? null) : null;

            Product::updateOrCreate(
                ['sku' => $pData['sku']],
                [
                    'name' => $pData['name'],
                    'slug' => Str::slug($pData['name']),
                    'price' => $pData['price'],
                    'sale_price' => $pData['sale_price'],
                    'category_id' => $cat?->id,
                    'collection_id' => $col?->id,
                    'inventory_type' => $pData['inventory_type'],
                    'stock' => $pData['stock'],
                    'status' => 'published',
                    'is_featured' => $pData['is_featured'],
                    'is_new' => $pData['is_new'],
                    'is_bestseller' => $pData['is_bestseller'],
                    'description' => $pData['description'],
                    'images' => $pData['images'],
                    'attributes' => $pData['attributes'] ?? null,
                ]
            );
        }

        // 4. Create Demo Test Customer User (test@test.com / 9988776655)
        $user = User::firstOrCreate(
            ['email' => 'test@test.com'],
            [
                'name' => 'Test Customer',
                'password' => bcrypt('password123'),
                'phone' => '9988776655',
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@maisonresine.com'],
            [
                'name' => 'Élodie Laurent',
                'password' => bcrypt('password123'),
                'phone' => '+91 9988776655',
            ]
        );

        // Seed default addresses for test user
        foreach ([$user, $customer] as $usr) {
            $usr->addresses()->firstOrCreate(
                ['type' => 'shipping'],
                [
                    'full_name' => $usr->name,
                    'phone' => $usr->phone ?? '9988776655',
                    'address_line_1' => 'Flat 402, Royal Palms Residency',
                    'address_line_2' => 'MG Road',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'postal_code' => '400001',
                    'country' => 'India',
                    'is_default' => true,
                ]
            );

            // Seed Sample Order
            $p = Product::first();
            if ($p && $usr->orders()->count() === 0) {
                $order = Order::create([
                    'order_reference' => 'MR-2026-' . rand(1000, 9999),
                    'user_id' => $usr->id,
                    'email' => $usr->email,
                    'status' => 'SHIPPED',
                    'payment_status' => 'paid',
                    'payment_method' => 'card',
                    'subtotal' => $p->effective_price,
                    'tax' => 0.00,
                    'shipping_fee' => 0.00,
                    'grand_total' => $p->effective_price,
                    'courier' => 'Atelier Courier',
                    'tracking_number' => 'TRK-' . rand(100000, 999999),
                    'shipping_address_snapshot' => [
                        'full_name' => $usr->name,
                        'address_line_1' => 'Flat 402, Royal Palms Residency',
                        'city' => 'Mumbai',
                        'state' => 'Maharashtra',
                        'postal_code' => '400001',
                        'country' => 'India',
                        'phone' => $usr->phone ?? '9988776655',
                    ],
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'sku' => $p->sku,
                    'quantity' => 1,
                    'unit_price' => $p->effective_price,
                    'subtotal' => $p->effective_price,
                    'product_snapshot' => $p->toArray(),
                ]);
            }
        }

        // 5. Seed Reviews for ALL Products
        $allProducts = Product::all();
        $sampleReviews = [
            [
                'reviewer_name' => 'Claire M.',
                'reviewer_location' => 'Mumbai, MH',
                'rating' => 5,
                'title' => 'Exquisite fluid depth!',
                'comment' => 'It arrived boxed like a piece of fine jewellery. The depth of the resin pour is impossible to capture in photos — stunning addition to our home.',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Jonas B.',
                'reviewer_location' => 'Bengaluru, KA',
                'rating' => 5,
                'title' => 'Unbelievable craftsmanship',
                'comment' => 'Third piece from the atelier. Consistently the most considered and praised object in our home.',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Amira S.',
                'reviewer_location' => 'Delhi, DL',
                'rating' => 4,
                'title' => 'Heat resistant and gorgeous',
                'comment' => 'Beautiful and clearly hand-made. Dispatch was fast, build quality is premium, and packaging was lovely.',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Rohan Sharma',
                'reviewer_location' => 'Pune, MH',
                'rating' => 5,
                'title' => 'Mindblowing finish!',
                'comment' => 'The gloss and clarity of the resin are world class. Exactly as shown on the website.',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Priya Patel',
                'reviewer_location' => 'Ahmedabad, GJ',
                'rating' => 5,
                'title' => 'Perfect luxury gift',
                'comment' => 'Gifted this to my sister for her housewarming. She loved the gold leaf accents and solid weight.',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Karan Verma',
                'reviewer_location' => 'Hyderabad, TS',
                'rating' => 5,
                'title' => 'Top tier luxury art piece',
                'comment' => 'Extremely smooth edge work and deep mineral swirl colors. Delivered safely in a wooden crate.',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Neha Gupta',
                'reviewer_location' => 'Jaipur, RJ',
                'rating' => 5,
                'title' => 'Mesmerizing in person',
                'comment' => 'Photos do not do justice to the golden shimmer under sunlight. Highly recommended!',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Vikram Roy',
                'reviewer_location' => 'Kolkata, WB',
                'rating' => 4,
                'title' => 'Great weight & quality',
                'comment' => 'Solid materials, zero air bubbles, crystal clear surface finish. Excellent customer support too.',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Siddharth Nair',
                'reviewer_location' => 'Kochi, KL',
                'rating' => 5,
                'title' => 'A masterpiece',
                'comment' => 'Everyone who visits our home asks about this art piece. Truly one of a kind.',
                'is_verified_buyer' => true,
            ],
            [
                'reviewer_name' => 'Ananya Sen',
                'reviewer_location' => 'Chandigarh, PB',
                'rating' => 5,
                'title' => 'Beyond expectations',
                'comment' => 'Safe insured packaging, fast shipping, and breathtaking artistry. Will definitely order again.',
                'is_verified_buyer' => true,
            ],
        ];

        foreach ($allProducts as $p) {
            foreach ($sampleReviews as $rev) {
                \App\Models\ProductReview::firstOrCreate(
                    ['product_id' => $p->id, 'reviewer_name' => $rev['reviewer_name']],
                    $rev
                );
            }
        }
    }
}
