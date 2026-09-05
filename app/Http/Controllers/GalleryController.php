<?php

namespace App\Http\Controllers;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GalleryController extends Controller
{
    /**
     * Display the public gallery listing page with optional category filter and infinite scroll AJAX.
     */
    public function index(Request $request): View|JsonResponse
    {
        // Cache active categories for performance
        $categories = Cache::remember('gallery_active_categories', 3600, function () {
            return GalleryCategory::active()->ordered()->get();
        });

        $categorySlug = $request->query('category');
        $activeCategory = null;

        $query = GalleryItem::active()->with('galleryCategory')->ordered();

        if (!empty($categorySlug)) {
            // Validate category slug securely
            $activeCategory = $categories->firstWhere('slug', $categorySlug);

            if ($activeCategory) {
                $query->where('gallery_category_id', $activeCategory->id);
            }
        }

        // Set pagination batch size to 20 per page
        $items = $query->paginate(20)->withQueryString();

        // Handle AJAX infinite scroll requests securely
        if ($request->ajax() || $request->wantsJson() || $request->has('fetch_more')) {
            $startIndex = ($items->currentPage() - 1) * $items->perPage();

            $html = '';
            $itemsData = [];

            foreach ($items as $loopIndex => $item) {
                $globalIndex = $startIndex + $loopIndex;

                $html .= view('gallery.partials.item-card', [
                    'item' => $item,
                    'index' => $loopIndex,
                    'globalIndex' => $globalIndex,
                ])->render();

                $itemsData[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category' => $item->galleryCategory ? $item->galleryCategory->name : 'Gallery',
                    'image' => asset('storage/' . $item->image_path),
                    'alt' => $item->image_alt,
                    'location' => $item->location ?? '',
                ];
            }

            return response()->json([
                'html' => $html,
                'next_page_url' => $items->nextPageUrl(),
                'has_more' => $items->hasMorePages(),
                'items' => $itemsData,
            ]);
        }

        return view('gallery.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'items' => $items,
        ]);
    }
}
