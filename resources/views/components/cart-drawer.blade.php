@php
    $cart = null;
    $cartItems = collect();
    if (Auth::check()) {
        $cart = \App\Models\Cart::where('user_id', Auth::id())->with('items.product')->first();
    } else {
        $cart = \App\Models\Cart::where('session_id', session()->getId())->whereNull('user_id')->with('items.product')->first();
    }
    if ($cart) {
        $cartItems = $cart->items;
    }
@endphp

{{-- Cart Drawer Overlay --}}
<div x-show="cartOpen"
     x-cloak
     @keydown.escape.window="cartOpen = false"
     x-effect="document.body.classList.toggle('overflow-hidden', cartOpen)"
     class="fixed inset-0 z-[100] overflow-hidden pointer-events-auto"
     role="dialog"
     aria-modal="true">

    {{-- Backdrop --}}
    <div x-show="cartOpen"
         x-transition:enter="ease-in-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in-out duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="cartOpen = false"
         class="fixed inset-0 bg-black/30 transition-opacity cursor-pointer pointer-events-auto"></div>

    {{-- Panel --}}
    <div class="fixed inset-y-0 right-0 max-w-full flex pointer-events-auto">
        <div x-show="cartOpen"
             x-transition:enter="transform transition ease-in-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-[420px] max-w-full bg-[#FAF9F6] flex flex-col shadow-2xl">

            {{-- Header --}}
            <div class="flex items-center justify-between px-7 py-6 border-b border-[#DFD9CE]/60">
                <h2 class="font-editorial text-2xl sm:text-3xl text-[#1C1917] font-light">Your bag</h2>
                <button type="button" @click="cartOpen = false"
                        class="text-[9.5px] uppercase tracking-[0.25em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors cursor-pointer">
                    CLOSE
                </button>
            </div>

            {{-- Dynamic Content Area (Items + Footer) --}}
            <div id="cart-drawer-content-area" class="flex-1 flex flex-col justify-between overflow-hidden">
                @include('components.cart-drawer-content', ['cart' => $cart, 'cartItems' => $cartItems])
            </div>

        </div>
    </div>
</div>
