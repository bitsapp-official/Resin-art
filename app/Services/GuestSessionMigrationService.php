<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\RecentlyViewedProduct;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuestSessionMigrationService
{
    /**
     * Migrate guest session state (Cart, Wishlist, Recently Viewed) to an authenticated user account.
     *
     * @param User $user The authenticated user
     * @param string|null $guestSessionId The session ID prior to authentication
     * @param array $guestWishlist The wishlist product IDs stored in session
     */
    public static function migrate(User $user, ?string $guestSessionId, array $guestWishlist = []): void
    {
        try {
            DB::transaction(function () use ($user, $guestSessionId, $guestWishlist) {
                // 1. Merge Guest Cart into User Cart
                if ($guestSessionId) {
                    self::mergeCart($user, $guestSessionId);
                }

                // 2. Merge Guest Wishlist into User Wishlist
                self::mergeWishlist($user, $guestWishlist);

                // 3. Migrate Recently Viewed Products
                if ($guestSessionId) {
                    self::migrateRecentlyViewed($user, $guestSessionId);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Failed to migrate guest session state for user ID: ' . $user->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Merge guest cart items into authenticated user cart.
     */
    protected static function mergeCart(User $user, string $guestSessionId): void
    {
        $guestCart = Cart::where('session_id', $guestSessionId)
            ->whereNull('user_id')
            ->with(['items.product'])
            ->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
        $userCart->loadMissing(['items.product']);

        foreach ($guestCart->items as $guestItem) {
            $product = $guestItem->product;
            if (!$product || !$product->is_available) {
                continue;
            }

            $guestOptions = $guestItem->options ?? [];

            // Find matching item in user cart by product_id AND same options
            $existingUserItem = $userCart->items->first(function ($item) use ($guestItem, $guestOptions) {
                return (int) $item->product_id === (int) $guestItem->product_id
                    && ($item->options ?? []) == $guestOptions;
            });

            if ($existingUserItem) {
                $newQuantity = $existingUserItem->quantity + $guestItem->quantity;
                if ($product->inventory_type === 'READY_TO_SHIP' && $product->stock > 0 && $newQuantity > $product->stock) {
                    $newQuantity = $product->stock;
                }
                $existingUserItem->quantity = $newQuantity;
                $existingUserItem->price = $product->effective_price;
                $existingUserItem->save();
            } else {
                CartItem::create([
                    'cart_id'    => $userCart->id,
                    'product_id' => $guestItem->product_id,
                    'quantity'   => $guestItem->quantity,
                    'price'      => $guestItem->price ?: $product->effective_price,
                    'options'    => $guestOptions,
                ]);
            }
        }

        $userCart->recalculateTotal();

        // Clean up guest cart and its items
        $guestCart->items()->delete();
        $guestCart->delete();
    }

    /**
     * Merge guest wishlist product IDs into authenticated user wishlist database table.
     */
    protected static function mergeWishlist(User $user, array $guestWishlist): void
    {
        if (empty($guestWishlist)) {
            return;
        }

        $productIds = array_unique(array_filter(array_map('intval', $guestWishlist)));
        if (empty($productIds)) {
            return;
        }

        $validProductIds = Product::published()
            ->whereIn('id', $productIds)
            ->pluck('id')
            ->toArray();

        foreach ($validProductIds as $productId) {
            Wishlist::firstOrCreate([
                'user_id'    => $user->id,
                'product_id' => $productId,
            ]);
        }

        // Clear guest wishlist from session
        session()->forget('guest_wishlist');
    }

    /**
     * Migrate guest recently viewed entries to user account.
     */
    protected static function migrateRecentlyViewed(User $user, string $guestSessionId): void
    {
        RecentlyViewedProduct::where('session_id', $guestSessionId)
            ->whereNull('user_id')
            ->update([
                'user_id'    => $user->id,
                'session_id' => null,
            ]);
    }
}
