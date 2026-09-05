<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\RecentlyViewedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::published()->with(['category', 'collection']);

        // Search Filter (Supports both 'q' and 'search' parameters)
        if ($request->filled('q') || $request->filled('search')) {
            $search = $request->input('q') ?: $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Collection Filter
        if ($request->filled('collection')) {
            $collectionSlug = $request->input('collection');
            $query->whereHas('collection', function ($q) use ($collectionSlug) {
                $q->where('slug', $collectionSlug);
            });
        }

        // Price Filter (Matches under_X options from frontend)
        if ($request->filled('price')) {
            $priceRange = $request->input('price');
            switch ($priceRange) {
                case 'under_15000':
                    $query->whereRaw('COALESCE(sale_price, price) <= 15000');
                    break;
                case 'under_50000':
                    $query->whereRaw('COALESCE(sale_price, price) <= 50000');
                    break;
                case 'under_150000':
                    $query->whereRaw('COALESCE(sale_price, price) <= 150000');
                    break;
                case 'under_500000':
                    $query->whereRaw('COALESCE(sale_price, price) <= 500000');
                    break;
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        // Inventory Type Filter
        if ($request->filled('availability')) {
            $avail = strtoupper($request->input('availability'));
            if (in_array($avail, ['READY_TO_SHIP', 'MADE_TO_ORDER'])) {
                $query->where('inventory_type', $avail);
            }
        }

        // Feature / Badge Filters
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }
        if ($request->boolean('new')) {
            $query->where('is_new', true);
        }
        if ($request->boolean('bestseller')) {
            $query->where('is_bestseller', true);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_high':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'bestseller':
                $query->orderBy('is_bestseller', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::where('is_active', true)->withCount('products')->get();
        $collections = Collection::where('is_active', true)->withCount('products')->get();

        $wishlistIds = [];
        if (Auth::check()) {
            $wishlistIds = \App\Models\Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray();
        } else {
            $wishlistIds = session('guest_wishlist', []);
        }

        return view('shop.index', compact('products', 'categories', 'collections', 'wishlistIds'));
    }

    public function newArrivals(Request $request)
    {
        $query = Product::published()->where('is_new', true)->with(['category', 'collection']);

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_high':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        $wishlistIds = [];
        if (Auth::check()) {
            $wishlistIds = \App\Models\Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray();
        } else {
            $wishlistIds = session('guest_wishlist', []);
        }

        $eyebrow = 'NEW CREATIONS';
        $title = 'New arrivals.';
        $subtitle = 'The latest creations from our Bordeaux studio.';

        return view('shop.special', compact('products', 'wishlistIds', 'eyebrow', 'title', 'subtitle'));
    }

    public function bestSellers(Request $request)
    {
        $query = Product::published()->where('is_bestseller', true)->with(['category', 'collection']);

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_high':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        $wishlistIds = [];
        if (Auth::check()) {
            $wishlistIds = \App\Models\Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray();
        } else {
            $wishlistIds = session('guest_wishlist', []);
        }

        $eyebrow = 'MOST LOVED';
        $title = 'Best sellers.';
        $subtitle = 'The pieces our collectors return to again and again.';

        return view('shop.special', compact('products', 'wishlistIds', 'eyebrow', 'title', 'subtitle'));
    }

    public function show($slug)
    {
        $product = Product::published()
            ->where('slug', $slug)
            ->with(['category', 'collection', 'reviews'])
            ->firstOrFail();

        // Track recently viewed
        if (Auth::check()) {
            RecentlyViewedProduct::updateOrCreate(
                ['user_id' => Auth::id(), 'product_id' => $product->id],
                ['viewed_at' => now()]
            );
        } else {
            $sessionId = session()->getId();
            RecentlyViewedProduct::updateOrCreate(
                ['session_id' => $sessionId, 'product_id' => $product->id],
                ['viewed_at' => now()]
            );
        }

        $relatedProducts = Product::published()
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                if ($product->category_id) {
                    $q->orWhere('category_id', $product->category_id);
                }
                if ($product->collection_id) {
                    $q->orWhere('collection_id', $product->collection_id);
                }
            })
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'relatedProducts'));
    }
}
