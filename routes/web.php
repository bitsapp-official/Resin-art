<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Account\AccountDashboardController;
use App\Http\Controllers\Account\AccountDeletionController;
use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\CustomRequestAccountController;
use App\Http\Controllers\Account\DownloadController;
use App\Http\Controllers\Account\NotificationController;
use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\RecentlyViewedController;
use App\Http\Controllers\Account\RefundRequestController;
use App\Http\Controllers\Account\ReturnRequestController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Controllers\Account\SupportTicketController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\CustomRequestController;
use App\Http\Controllers\OrderInvoiceController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PrivateMediaController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\QuotePdfController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// E-Commerce Shop Catalog Routes
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/new-arrivals', [ShopController::class, 'newArrivals'])->name('shop.new-arrivals');
Route::get('/best-sellers', [ShopController::class, 'bestSellers'])->name('shop.best-sellers');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::post('/reviews', [ProductReviewController::class, 'store'])->name('reviews.store')->middleware(['auth', 'verified']);

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->name('cart.buy-now');

// Wishlist Routes
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index')->middleware(['auth', 'verified']);
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process')->middleware(['auth', 'verified']);
Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
Route::get('/checkout/cancel/{order}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Stripe Webhook Route (CSRF exempt for asynchronous Stripe server notifications)
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhook.stripe');

// Order Tracking Route (GET = show form, POST = search securely without email in URL)
Route::get('/track-order', [OrderTrackingController::class, 'index'])->name('tracking.index');
Route::post('/track-order', [OrderTrackingController::class, 'search'])->name('tracking.search');
Route::redirect('/tracking', '/track-order', 301);

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('account.dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->route('account.dashboard');
    }
    try {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    } catch (\Exception $e) {
        return back()->with('smtp_error', true);
    }
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [LoginController::class, 'logout'])->middleware('auth');

// Customer Account System Routes (Auth Required)
Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/password', [PasswordController::class, 'index'])->name('password.index');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [OrderInvoiceController::class, 'download'])->name('orders.invoice');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/returns', [ReturnRequestController::class, 'index'])->name('returns.index');
    Route::get('/returns/create', [ReturnRequestController::class, 'create'])->name('returns.create');
    Route::post('/returns', [ReturnRequestController::class, 'store'])->name('returns.store');

    Route::get('/refunds', [RefundRequestController::class, 'index'])->name('refunds.index');
    Route::post('/refunds', [RefundRequestController::class, 'store'])->name('refunds.store');

    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportTicketController::class, 'store'])->name('support.store');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('/recently-viewed', [RecentlyViewedController::class, 'index'])->name('recently-viewed.index');
    Route::get('/downloads', [DownloadController::class, 'index'])->name('downloads.index');

    Route::get('/settings', function () {
        return redirect()->route('account.profile.index');
    })->name('settings.index');
    Route::put('/settings', [ProfileController::class, 'update'])->name('settings.update');

    Route::post('/delete', [AccountDeletionController::class, 'destroy'])->name('delete');

    Route::get('/custom-requests', [CustomRequestAccountController::class, 'index'])->name('custom-requests.index');
});

// Existing Atelier Pages & Custom Artwork Module
Route::get('/about', [AboutController::class, 'index'])->name('about.index');
Route::redirect('/our-story', '/about', 301);
Route::get('/our-process', [ProcessController::class, 'index'])->name('our-process.index');
Route::get('/gallery', [\App\Http\Controllers\GalleryController::class, 'index'])->name('gallery.index');
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
Route::get('/collections', [\App\Http\Controllers\CollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/{slug}', [\App\Http\Controllers\CollectionController::class, 'show'])->name('collections.show');

Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
$maxSubmissions = config('atelier.rate_limit.max_submissions', 5);
$decayMinutes = config('atelier.rate_limit.decay_minutes', 10);
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware("throttle:{$maxSubmissions},{$decayMinutes}")
    ->name('contact.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/custom', [CustomRequestController::class, 'create'])->name('custom.index');
    Route::post('/custom', [CustomRequestController::class, 'store'])->name('custom.store');
    Route::get('/custom/success', [CustomRequestController::class, 'success'])->name('custom.success');
});
Route::get('/private-media/custom-requests/{path}', [PrivateMediaController::class, 'show'])->where('path', '.*')->name('private.media.show');

Route::get('/orders/{order}/invoice/download', [OrderInvoiceController::class, 'publicDownload'])->name('orders.invoice.public');
Route::get('/admin/orders/{order}/pdf', [OrderInvoiceController::class, 'adminDownload'])->name('admin.orders.pdf');
Route::get('/admin/quotes/{quote}/pdf', [QuotePdfController::class, 'download'])->name('admin.quotes.pdf');
Route::get('/admin/custom-requests/{customRequest}/pdf', [OrderInvoiceController::class, 'customRequestInvoicePdf'])->name('admin.custom_requests.pdf');
Route::get('/admin/invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\InvoicePdfController::class, 'download'])->name('admin.invoices.pdf');

// Legal / Policy Pages (managed via Admin Panel → Content → Policy Pages)
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/shipping',  [LegalController::class, 'show'])->defaults('slug', 'shipping')->name('shipping');
    Route::get('/return',    [LegalController::class, 'show'])->defaults('slug', 'return')->name('return');
    Route::get('/privacy',   [LegalController::class, 'show'])->defaults('slug', 'privacy')->name('privacy');
    Route::get('/terms',     [LegalController::class, 'show'])->defaults('slug', 'terms')->name('terms');
});
// Legacy redirect for old /terms and /privacy URLs
Route::redirect('/terms',   '/legal/terms',   301)->name('terms');
Route::redirect('/privacy', '/legal/privacy', 301)->name('privacy');

// CSRF Token auto-refresh endpoint (strictly rate-limited and non-cacheable)
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
})->middleware('throttle:60,1')->name('csrf.token');


