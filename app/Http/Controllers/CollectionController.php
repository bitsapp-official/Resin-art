<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Display a listing of all active collections.
     */
    public function index()
    {
        $collections = Collection::active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->withCount(['products' => function ($q) {
                $q->where('products.status', 'published');
            }])
            ->get();

        return view('collections.index', compact('collections'));
    }

    /**
     * Display the specified collection details and assigned active products.
     */
    public function show(string $slug)
    {
        $collection = Collection::active()
            ->where('slug', $slug)
            ->firstOrFail();

        // Paginate active products in this collection without N+1 queries
        $products = $collection->products()
            ->where('products.status', 'published')
            ->orderBy('products.id', 'desc')
            ->paginate(12);

        return view('collections.show', compact('collection', 'products'));
    }
}
