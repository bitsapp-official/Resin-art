<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function getCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $sessionId = session()->getId();
        return Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }

    public function index()
    {
        $cart = $this->getCart();
        $cart->load('items.product');

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'quantity'     => ['nullable', 'integer', 'min:1', 'max:99'],
            'options'      => ['nullable', 'array'],
            'options.size' => ['nullable', 'string', 'max:150'],
        ]);

        $product = Product::published()->findOrFail($request->product_id);

        if (!$product->is_available) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => 'This piece is currently unavailable or sold out.'], 422);
            }
            return back()->with('error', 'This piece is currently unavailable or sold out.');
        }

        $quantity = (int) ($request->quantity ?? 1);
        $options = $request->options ?? [];

        // SECURITY: Price is ALWAYS derived server-side from the product's size_variants.
        // Never accept a price from the client (prevents price tampering).
        $price = $product->effective_price;
        if (!empty($options['size'])) {
            $sizeVariants = $product->attributes['size_variants'] ?? [];
            $matched = false;
            foreach ($sizeVariants as $variant) {
                if (($variant['size'] ?? '') === $options['size'] && isset($variant['price']) && (float)$variant['price'] > 0) {
                    $price = (float) $variant['price'];
                    $matched = true;
                    break;
                }
            }
            // If size was submitted but didn't match any variant, clear options to avoid orphaned size
            if (!$matched) {
                $options = [];
            }
        }

        $cart = $this->getCart();

        // Find existing item with exact same options
        $cartItem = $cart->items()->where('product_id', $product->id)->get()->first(function ($item) use ($options) {
            return ($item->options ?? []) == $options;
        });

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($product->inventory_type === 'READY_TO_SHIP' && $product->stock > 0 && $newQuantity > $product->stock) {
                $newQuantity = $product->stock;
            }
            $cartItem->quantity = $newQuantity;
            $cartItem->price = $price; // Update price in case it changed
            $cartItem->options = $options;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
                'options' => $options,
            ]);
        }

        $cart->recalculateTotal();
        $cart->load('items.product');
        $cartItems = $cart->items;

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Piece added to your bag.',
                'cart_total' => number_format($cart->total),
                'cart_count' => $cartItems->count(),
                'drawer_html' => view('components.cart-drawer-content', compact('cart', 'cartItems'))->render(),
            ]);
        }

        if ($request->headers->has('referer')) {
            return redirect()->back()->with('cart_open', true)->with('success', 'Piece added to your bag.');
        }

        return redirect()->route('cart.index')->with('success', 'Piece added to your bag.');
    }

    /**
     * BUY NOW — Add to cart (replacing existing for same product+size) and redirect to checkout.
     * Handles both authenticated and guest users.
     */
    public function buyNow(Request $request)
    {
        $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'quantity'     => ['nullable', 'integer', 'min:1', 'max:99'],
            'options'      => ['nullable', 'array'],
            'options.size' => ['nullable', 'string', 'max:150'],
        ]);

        $product = Product::published()->findOrFail($request->product_id);

        if (!$product->is_available) {
            return back()->with('error', 'This piece is currently unavailable or sold out.');
        }

        $quantity = (int) ($request->quantity ?? 1);
        $options  = $request->options ?? [];

        // SECURITY: Derive price server-side only
        $price = $product->effective_price;
        if (!empty($options['size'])) {
            $sizeVariants = $product->attributes['size_variants'] ?? [];
            foreach ($sizeVariants as $variant) {
                if (($variant['size'] ?? '') === $options['size'] && isset($variant['price']) && (float)$variant['price'] > 0) {
                    $price = (float) $variant['price'];
                    break;
                }
            }
        }

        $cart = $this->getCart();

        // Remove existing cart items for this product+size (Buy Now = fresh intent)
        $existingItems = $cart->items()->where('product_id', $product->id)->get();
        foreach ($existingItems as $existing) {
            if (($existing->options ?? []) == $options) {
                $existing->delete();
            }
        }

        // Add fresh item with correct variant price
        CartItem::create([
            'cart_id'    => $cart->id,
            'product_id' => $product->id,
            'quantity'   => $quantity,
            'price'      => $price,
            'options'    => $options,
        ]);

        $cart->recalculateTotal();

        // If user is not authenticated, redirect to login and then back to checkout
        if (!Auth::check()) {
            session()->put('url.intended', route('checkout.index'));
            return redirect()->route('login')->with('info', 'Please sign in to complete your purchase.');
        }

        return redirect()->route('checkout.index');
    }

    public function update(Request $request)
    {
        $request->validate([
            'item_id'  => ['required', 'exists:cart_items,id'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = $this->getCart();
        $cartItem = CartItem::where('cart_id', $cart->id)->where('id', $request->item_id)->firstOrFail();

        if ($request->quantity == 0) {
            $cartItem->delete();
        } else {
            $product = $cartItem->product;
            $quantity = (int) $request->quantity;

            if ($product && $product->inventory_type === 'READY_TO_SHIP' && $product->stock > 0 && $quantity > $product->stock) {
                $quantity = $product->stock;
            }

            $cartItem->quantity = $quantity;
            // IMPORTANT: Do NOT overwrite price here. The cartItem->price already
            // holds the correct size-variant price that was set when the item was added.
            // Overwriting with effective_price would wipe the variant pricing.
            $cartItem->save();
        }

        $cart->recalculateTotal();
        $cart->load('items.product');
        $cartItems = $cart->items;

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Bag updated.',
                'item_id' => (int) $request->item_id,
                'cart_total' => number_format($cart->total),
                'cart_count' => $cartItems->count(),
                'drawer_html' => view('components.cart-drawer-content', compact('cart', 'cartItems'))->render(),
            ]);
        }

        return back()->with('cart_open', true)->with('success', 'Bag updated.');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:cart_items,id'],
        ]);

        $cart = $this->getCart();
        CartItem::where('cart_id', $cart->id)->where('id', $request->item_id)->delete();

        $cart->recalculateTotal();
        $cart->load('items.product');
        $cartItems = $cart->items;

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Item removed.',
                'item_id' => (int) $request->item_id,
                'cart_total' => number_format($cart->total),
                'cart_count' => $cartItems->count(),
                'drawer_html' => view('components.cart-drawer-content', compact('cart', 'cartItems'))->render(),
            ]);
        }

        return back()->with('cart_open', true)->with('success', 'Item removed from bag.');
    }
}
