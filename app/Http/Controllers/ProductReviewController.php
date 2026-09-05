<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'reviewer_name' => 'required|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string',
        ]);

        ProductReview::create([
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
            'reviewer_name' => $request->reviewer_name,
            'rating' => (int) $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'is_verified_buyer' => true,
        ]);

        return back()->with('success', 'Thank you! Your review has been submitted successfully.');
    }
}
