<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\GalleryItem;
use App\Models\HomeSlide;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\SiteSetting;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Fetch active hero slides from admin panel
        $homeSlides = HomeSlide::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // 2. Fetch featured collections for home page (prioritize is_featured_on_home)
        $featuredCollections = Collection::active()
            ->orderByDesc('is_featured_on_home')
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        // 3. Fetch active categories with published product counts
        $categories = Category::withCount(['products' => function ($query) {
            $query->where('status', 'published');
        }])->where('is_active', true)->get();

        // 4. Fetch most loved / featured published products
        $mostLoved = Product::where('status', 'published')
            ->where(function ($q) {
                $q->where('is_featured', true)
                  ->orWhere('is_bestseller', true);
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Fallback: If not enough featured products, take any published products
        if ($mostLoved->count() < 4) {
            $existingIds = $mostLoved->pluck('id')->toArray();
            $additional = Product::where('status', 'published')
                ->whereNotIn('id', $existingIds)
                ->inRandomOrder()
                ->take(4 - $mostLoved->count())
                ->get();
            $mostLoved = $mostLoved->concat($additional);
        }

        // 4b. Fetch Bestseller products
        $bestsellers = Product::where('status', 'published')
            ->where('is_bestseller', true)
            ->inRandomOrder()
            ->take(4)
            ->get();

        // 4c. Fetch New Arrival products (is_new flag OR latest created)
        $newArrivals = Product::where('status', 'published')
            ->where(function ($q) {
                $q->where('is_new', true)
                  ->orWhere('created_at', '>=', now()->subDays(30));
            })
            ->latest()
            ->take(4)
            ->get();

        // 5. Fetch random gallery items for interior inspiration section
        $galleryItems = GalleryItem::with('galleryCategory')
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(5)
            ->get();

        // 6. Get wishlist IDs for the current user
        $wishlistIds = [];
        if (Auth::check()) {
            $wishlistIds = Wishlist::where('user_id', Auth::id())
                ->pluck('product_id')
                ->toArray();
        }

        // 7. Fetch top reviews from database (prioritize is_featured_on_home, then high-rated reviews)
        $featuredReviews = ProductReview::with('product')
            ->where(function ($q) {
                $q->where('is_featured_on_home', true)
                  ->orWhere('rating', '>=', 4);
            })
            ->orderByDesc('is_featured_on_home')
            ->latest()
            ->take(10)
            ->get();

        // 8. Dynamic Quote, Story & Custom Artwork Settings
        $homeQuoteText       = SiteSetting::get('home_quote_text', 'Resin remembers everything — the hand, the hour, the light in the room.');
        $homeQuoteAuthor     = SiteSetting::get('home_quote_author', 'Founder & Resin Artist');
        $homeStoryImage      = SiteSetting::get('home_story_image', 'about/artist_workshop.png');
        $homeStoryTag        = SiteSetting::get('home_story_tag', 'OUR APPROACH');
        $homeStoryTitle      = SiteSetting::get('home_story_title', 'Handcrafted, made to order.');
        $homeStoryParagraph1 = SiteSetting::get('home_story_paragraph1', 'Every piece begins as a slow conversation between wood, resin, and time. We pour by hand, creating unique items that are specifically made to order for your space.');
        $homeStoryParagraph2 = SiteSetting::get('home_story_paragraph2', 'No two rivers are alike. No two skies. That is the point.');
        $homeStoryBadge      = SiteSetting::get('home_story_badge', 'HANDCRAFTED · MADE TO ORDER');
        $homeStoryLinkText   = SiteSetting::get('home_story_link_text', 'Read our story');
        $homeStoryLinkUrl    = SiteSetting::get('home_story_link_url', '/about');

        // Custom Made-to-Order Artwork Settings
        $homeCustomTag        = SiteSetting::get('home_custom_tag', 'CUSTOM RESIN ARTWORK');
        $homeCustomTitle      = SiteSetting::get('home_custom_title', 'Have a design in mind? Crafted for your space.');
        $homeCustomDesc       = SiteSetting::get('home_custom_desc', 'Every custom piece begins with your vision. Simply describe your requirement in your own words, share inspiration or room photos, and our lead artisan connects directly with you on WhatsApp to discuss wood slabs, colors, and pricing.');
        $homeCustomBtnText    = SiteSetting::get('home_custom_btn_text', 'Submit Custom Requirement →');
        $homeCustomBtnUrl     = SiteSetting::get('home_custom_btn_url', '/custom');
        $homeCustomStep1Title = SiteSetting::get('home_custom_step1_title', 'Describe Your Requirement & Photos');
        $homeCustomStep1Desc  = SiteSetting::get('home_custom_step1_desc', 'Tell us what you have in mind in your own words — whether a dining river table, wall art, clock, or decor — and upload inspiration photos. No complex measurements needed.');
        $homeCustomStep2Title = SiteSetting::get('home_custom_step2_title', 'Artisan WhatsApp Consultation');
        $homeCustomStep2Desc  = SiteSetting::get('home_custom_step2_desc', 'Our lead artisan connects directly with you on WhatsApp or Email to discuss wood slab selections, custom color palettes, sizing, and share a transparent all-inclusive quote.');
        $homeCustomStep3Title = SiteSetting::get('home_custom_step3_title', 'Hand-Poured Craft & Crated Delivery');
        $homeCustomStep3Desc  = SiteSetting::get('home_custom_step3_desc', 'Once approved, we pour your artwork layer-by-layer with live WhatsApp photo updates, seal with diamond gloss, and deliver safely in insured wooden crates to your doorstep.');

        return view('home.index', compact(
            'homeSlides',
            'featuredCollections',
            'categories',
            'mostLoved',
            'bestsellers',
            'newArrivals',
            'galleryItems',
            'wishlistIds',
            'featuredReviews',
            'homeQuoteText',
            'homeQuoteAuthor',
            'homeStoryImage',
            'homeStoryTag',
            'homeStoryTitle',
            'homeStoryParagraph1',
            'homeStoryParagraph2',
            'homeStoryBadge',
            'homeStoryLinkText',
            'homeStoryLinkUrl',
            'homeCustomTag',
            'homeCustomTitle',
            'homeCustomDesc',
            'homeCustomBtnText',
            'homeCustomBtnUrl',
            'homeCustomStep1Title',
            'homeCustomStep1Desc',
            'homeCustomStep2Title',
            'homeCustomStep2Desc',
            'homeCustomStep3Title',
            'homeCustomStep3Desc'
        ));
    }
}
