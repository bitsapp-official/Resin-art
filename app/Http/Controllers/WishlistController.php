<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $wishlists = Wishlist::where('user_id', Auth::id())
                ->with('product')
                ->latest()
                ->get();
        } else {
            $sessionWishlistIds = session('guest_wishlist', []);
            $wishlists = Product::whereIn('id', $sessionWishlistIds)->get()->map(function ($product) {
                return (object) ['product' => $product, 'id' => $product->id];
            });
        }

        return view('wishlist.index', compact('wishlists'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $productId = $request->product_id;

        if (Auth::check()) {
            $userId = Auth::id();
            $existing = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();

            if ($existing) {
                $existing->delete();
                $added = false;
                $message = 'Removed from your wishlist.';
            } else {
                Wishlist::create(['user_id' => $userId, 'product_id' => $productId]);
                $added = true;
                $message = 'Saved to your wishlist.';
            }
        } else {
            $guestWishlist = session('guest_wishlist', []);
            if (in_array($productId, $guestWishlist)) {
                $guestWishlist = array_values(array_diff($guestWishlist, [$productId]));
                $added = false;
                $message = 'Removed from your wishlist.';
            } else {
                $guestWishlist[] = $productId;
                $added = true;
                $message = 'Saved to your wishlist.';
            }
            session(['guest_wishlist' => $guestWishlist]);
        }

        if ($request->wantsJson()) {
            return response()->json(['added' => $added, 'message' => $message]);
        }

        return back();
    }

    public function moveToCart(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::published()->findOrFail($request->product_id);

        if (!$product->is_available) {
            return back()->with('error', 'This piece is currently unavailable.');
        }

        // Add to cart
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->delete();
        } else {
            $sessionId = session()->getId();
            $cart = Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
            $guestWishlist = session('guest_wishlist', []);
            $guestWishlist = array_values(array_diff($guestWishlist, [$product->id]));
            session(['guest_wishlist' => $guestWishlist]);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->first();
        if (!$cartItem) {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->effective_price,
            ]);
        }

        $cart->recalculateTotal();

        return redirect()->route('cart.index')->with('success', 'Moved piece to your bag.');
    }
}
