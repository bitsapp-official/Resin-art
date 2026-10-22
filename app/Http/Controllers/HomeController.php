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

        // 6. Get wishlist IDs for the current user (Auth or Guest)
        $wishlistIds = [];
        if (Auth::check()) {
            $wishlistIds = Wishlist::where('user_id', Auth::id())
                ->pluck('product_id')
                ->toArray();
        } else {
            $wishlistIds = session('guest_wishlist', []);
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

        // 8. Dynamic Quote, Story & Bespoke Jewellery Settings
        $homeQuoteText       = SiteSetting::get('home_quote_text', 'A diamond does not merely capture light — it immortalises the moments that define your life.');
        $homeQuoteAuthor     = SiteSetting::get('home_quote_author', 'Master Jeweller · Solitaire Atelier');
        $homeStoryImage      = SiteSetting::get('home_story_image', 'images/hero/jewellery_necklace_banner.jpg');
        $homeStoryTag        = SiteSetting::get('home_story_tag', 'THE ATELIER HERITAGE');
        $homeStoryTitle      = SiteSetting::get('home_story_title', 'Heirlooms born of gold, fire, and light.');
        $homeStoryParagraph1 = SiteSetting::get('home_story_paragraph1', 'Every piece in our atelier is an intimate dialogue between master craftsmanship and certified natural diamonds. Forged in solid 18KT gold, calibrated to microscopic tolerances, and designed to be worn and passed down for a lifetime.');
        $homeStoryParagraph2 = SiteSetting::get('home_story_paragraph2', 'Certified by premier international gemmological laboratories and hallmarked for purity.');
        $homeStoryBadge      = SiteSetting::get('home_story_badge', '100% BIS HALLMARKED · IGI CERTIFIED');
        $homeStoryLinkText   = SiteSetting::get('home_story_link_text', 'Discover our craftsmanship');
        $homeStoryLinkUrl    = SiteSetting::get('home_story_link_url', '/about');

        // Bespoke Fine Jewellery Consultation Settings
        $homeCustomTag        = SiteSetting::get('home_custom_tag', 'BESPOKE JEWELLERY ATELIER');
        $homeCustomTitle      = SiteSetting::get('home_custom_title', 'Have a dream design? Handcrafted exclusively for you.');
        $homeCustomDesc       = SiteSetting::get('home_custom_desc', 'From custom solitaire engagement rings to bridal heirlooms, bring your vision to life. Share your reference sketch or photo, choose your preferred gold karat and certified diamonds, and collaborate one-on-one with our master gemologists.');
        $homeCustomBtnText    = SiteSetting::get('home_custom_btn_text', 'Commission Custom Jewellery →');
        $homeCustomBtnUrl     = SiteSetting::get('home_custom_btn_url', '/custom');
        $homeCustomStep1Title = SiteSetting::get('home_custom_step1_title', 'Design Consultation & Sketch');
        $homeCustomStep1Desc  = SiteSetting::get('home_custom_step1_desc', 'Share your design inspiration, ring size, or metal tone preference (Yellow, White, or Rose Gold). No prior jewellery expertise needed — our specialists guide you through every cut and setting.');
        $homeCustomStep2Title = SiteSetting::get('home_custom_step2_title', '3D CAD & Diamond Selection');
        $homeCustomStep2Desc  = SiteSetting::get('home_custom_step2_desc', 'Our master designers render a photorealistic 3D CAD preview. You inspect stone proportions, diamond clarity, and receive an all-inclusive transparent quote before casting.');
        $homeCustomStep3Title = SiteSetting::get('home_custom_step3_title', 'Hand Setting & Insured Delivery');
        $homeCustomStep3Desc  = SiteSetting::get('home_custom_step3_desc', 'Cast in solid gold and hand-set under 10x magnification by master setters. Shipped in tamper-evident insured packaging with independent laboratory certification to your door.');

        // 9. Editorial Journal / Blog Showcase for Homepage
        $homeBlogSetting = SiteSetting::get('home_blog_enabled', '1');
        $homeBlogEnabled = ($homeBlogSetting === '1' || $homeBlogSetting === true || $homeBlogSetting === 1);
        $homeBlogTag      = SiteSetting::get('home_blog_tag', 'THE ATELIER JOURNAL');
        $homeBlogTitle    = SiteSetting::get('home_blog_title', 'Stories of Brilliance & Craft');
        $homeBlogSubtitle = SiteSetting::get('home_blog_subtitle', 'Master guides on solitaires, 4Cs of diamonds, hallmarked gold, and fine jewellery styling.');

        $homeBlogPosts = collect();
        if ($homeBlogEnabled) {
            // Strictly fetch ONLY articles explicitly toggled ON for Home Showcase (is_featured_on_home = true)
            // Maximum 3 showcase articles only. No random or untoggled fallback.
            $homeBlogPosts = \App\Models\BlogPost::published()
                ->where('is_featured_on_home', true)
                ->latest('published_at')
                ->take(3)
                ->get();
        }

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
            'homeCustomStep3Desc',
            'homeBlogEnabled',
            'homeBlogTag',
            'homeBlogTitle',
            'homeBlogSubtitle',
            'homeBlogPosts'
        ));
    }
}
