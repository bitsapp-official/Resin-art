<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BlogCategory;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $activeCategories = BlogCategory::getActiveCategories();
        $selectedCategorySlug = $request->query('category');

        $query = BlogPost::published()->with('category')->latest('published_at');

        $selectedCategory = null;
        if (!empty($selectedCategorySlug)) {
            $selectedCategory = $activeCategories->firstWhere('slug', $selectedCategorySlug);
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        }

        $posts = $query->paginate(9)->withQueryString();

        // On Page 1 with no category filter, separate the first post as the main 2-column featured card
        $featuredPost = null;
        $gridPosts = $posts;

        if ($posts->currentPage() === 1 && empty($selectedCategorySlug) && $posts->count() > 0) {
            $featuredPost = $posts->first();
            $gridPosts = $posts->slice(1);
        }

        return view('blog.index', [
            'categories' => $activeCategories,
            'selectedCategory' => $selectedCategory,
            'selectedCategorySlug' => $selectedCategorySlug,
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'gridPosts' => $gridPosts,
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        // 3 Related Posts from same category, excluding current post
        $relatedPosts = BlogPost::published()
            ->with('category')
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take(3)
            ->get();

        // If fewer than 3 related in same category, backfill with recent posts
        if ($relatedPosts->count() < 3) {
            $existingIds = $relatedPosts->pluck('id')->push($post->id)->toArray();
            $backfill = BlogPost::published()
                ->with('category')
                ->whereNotIn('id', $existingIds)
                ->latest('published_at')
                ->take(3 - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->concat($backfill);
        }

        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
