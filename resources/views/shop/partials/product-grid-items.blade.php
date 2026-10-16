@foreach($products as $product)
    @include('shop.partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds ?? []])
@endforeach
