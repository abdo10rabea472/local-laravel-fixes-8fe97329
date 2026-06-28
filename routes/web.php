<?php

use App\Http\Controllers\PagesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TrackOrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\OffersController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\FaqController;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController as FrontProductController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\HomePageController;
use App\Http\Controllers\Admin\ProductCatalogController as AdminProductCatalogController;
use App\Http\Controllers\Admin\HeaderMenuController;
use App\Http\Controllers\Admin\ShippingRateController;
use App\Http\Controllers\Admin\ProductDiscountController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\CartController;



Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductCatalogController::class, 'index'])->name('products.index');
// Checkout — all routes require an authenticated end-user (web guard).
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])
        ->middleware('throttle:30,1')->name('checkout.apply-coupon');
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])
        ->middleware('throttle:30,1')->name('checkout.place-order');
    Route::get('/checkout/stocks', [CheckoutController::class, 'stocks'])
        ->middleware('throttle:60,1')->name('checkout.stocks');
    Route::post('/checkout/aramex-rate', [CheckoutController::class, 'aramexRate'])
        ->middleware('throttle:30,1')->name('checkout.aramex-rate');

    // Payment lifecycle
    Route::match(['get','post'], '/checkout/{order}/pay', [\App\Http\Controllers\PaymentController::class, 'start'])
        ->middleware('throttle:30,1')->name('checkout.pay');
    Route::get('/checkout/{order}/completed', [\App\Http\Controllers\PaymentController::class, 'completed'])
        ->name('checkout.completed');
});

// Public gateway return URL (must be GET/POST, CSRF-exempt for some gateways).
Route::match(['get', 'post'], '/payments/verify/{payment?}', [\App\Http\Controllers\PaymentController::class, 'verify'])
    ->name('verify-payment');

// Server-backed cart (replaces localStorage)
Route::prefix('cart')->name('cart.')->middleware('throttle:120,1')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/update', [CartController::class, 'update'])->name('update');
    Route::delete('/remove', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::post('/merge', [CartController::class, 'merge'])->name('merge');
});

Route::get('/payment/success', [PageController::class, 'paymentSuccess'])->name('pages.payment-success');
Route::get('/faqs', [PageController::class, 'faqs'])->name('pages.faqs');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/returns-refunds', [PageController::class, 'returns'])->name('pages.returns');

Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/product/{slug}', [FrontProductController::class, 'show'])->name('product.show');

// Public shipping webhook (carriers POST status updates here)
Route::post('/api/shipping/{code}/webhook', \App\Http\Controllers\ShippingWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('shipping.webhook');



Route::get('/admin', function () {
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});

Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:5,1');
});

Route::middleware(['auth:admin', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // ── تصنيفات الكليات ──
    Route::prefix('colleges')->name('colleges.')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'collegesIndex'])->name('index');
        Route::get('/create', [AdminCategoryController::class, 'collegesCreate'])->name('create');
        Route::post('/', [AdminCategoryController::class, 'collegesStore'])->name('store');
        Route::get('/{category}/edit', [AdminCategoryController::class, 'collegesEdit'])->name('edit');
        Route::put('/{category}', [AdminCategoryController::class, 'collegesUpdate'])->name('update');
        Route::delete('/{category}', [AdminCategoryController::class, 'collegesDestroy'])->name('destroy');
    });

    // ── التصنيفات الفرعية ──
    Route::prefix('subcategories')->name('subcategories.')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'subcategoriesIndex'])->name('index');
        Route::get('/create', [AdminCategoryController::class, 'subcategoriesCreate'])->name('create');
        Route::post('/', [AdminCategoryController::class, 'subcategoriesStore'])->name('store');
        Route::get('/{category}/edit', [AdminCategoryController::class, 'subcategoriesEdit'])->name('edit');
        Route::put('/{category}', [AdminCategoryController::class, 'subcategoriesUpdate'])->name('update');
        Route::delete('/{category}', [AdminCategoryController::class, 'subcategoriesDestroy'])->name('destroy');
    });

    Route::get('/categories/{category}/children', [AdminCategoryController::class, 'children'])->name('categories.children');

    Route::get('/products/export/csv', [ProductController::class, 'exportCsv'])->name('products.export');
    Route::post('/products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');
    Route::post('/products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
    Route::resource('products', ProductController::class)->except(['show']);

    // Admin notifications feed (Topbar bell)
    Route::get('/notifications/feed', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'feed'])->name('notifications.feed');
    // Site Settings
    Route::get('/settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SiteSettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/ai/test', [SiteSettingController::class, 'testAi'])->name('settings.ai.test');
    Route::post('/settings/mail/test', [SiteSettingController::class, 'testMail'])->name('settings.mail.test');

    // Languages
    Route::get('/settings/languages', [\App\Http\Controllers\Admin\LanguageController::class, 'index'])->name('settings.languages.index');
    Route::post('/settings/languages', [\App\Http\Controllers\Admin\LanguageController::class, 'store'])->name('settings.languages.store');
    Route::put('/settings/languages/{language}', [\App\Http\Controllers\Admin\LanguageController::class, 'update'])->name('settings.languages.update');
    Route::delete('/settings/languages/{language}', [\App\Http\Controllers\Admin\LanguageController::class, 'destroy'])->name('settings.languages.destroy');
    Route::post('/settings/languages/{language}/default', [\App\Http\Controllers\Admin\LanguageController::class, 'setDefault'])->name('settings.languages.default');
    Route::get('/settings/languages/{language}/translations', [\App\Http\Controllers\Admin\LanguageController::class, 'translations'])->name('settings.languages.translations');
    Route::post('/settings/languages/{language}/translations', [\App\Http\Controllers\Admin\LanguageController::class, 'saveTranslations'])->name('settings.languages.translations.save');

    // Currencies
    Route::get('/settings/currencies', [\App\Http\Controllers\Admin\CurrencyController::class, 'index'])->name('settings.currencies.index');
    Route::post('/settings/currencies', [\App\Http\Controllers\Admin\CurrencyController::class, 'store'])->name('settings.currencies.store');
    Route::put('/settings/currencies/{currency}', [\App\Http\Controllers\Admin\CurrencyController::class, 'update'])->name('settings.currencies.update');
    Route::delete('/settings/currencies/{currency}', [\App\Http\Controllers\Admin\CurrencyController::class, 'destroy'])->name('settings.currencies.destroy');
    Route::post('/settings/currencies/{currency}/default', [\App\Http\Controllers\Admin\CurrencyController::class, 'setDefault'])->name('settings.currencies.default');


    // Payment Gateways
    Route::get('/settings/payment-gateways', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'index'])->name('settings.payment-gateways.index');
    Route::get('/settings/payment-gateways/{gateway}/edit', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'edit'])->name('settings.payment-gateways.edit');
    Route::put('/settings/payment-gateways/{gateway}', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'update'])->name('settings.payment-gateways.update');
    Route::patch('/settings/payment-gateways/{gateway}/toggle', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'toggle'])->name('settings.payment-gateways.toggle');
    Route::post('/settings/payment-gateways/{gateway}/test', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'test'])->name('settings.payment-gateways.test');

    // Static Pages
    Route::resource('pages', AdminPageController::class)->except(['show']);

    // Homepage
    Route::get('/homepage/edit', [HomePageController::class, 'edit'])->name('homepage.edit');
    Route::put('/homepage', [HomePageController::class, 'update'])->name('homepage.update');

    // Product Catalog Page
    Route::get('/product-catalog/edit', [AdminProductCatalogController::class, 'edit'])->name('product-catalog.edit');
    Route::put('/product-catalog', [AdminProductCatalogController::class, 'update'])->name('product-catalog.update');

    // Header Menu
    Route::get('/settings/header-menu', [HeaderMenuController::class, 'index'])->name('settings.header-menu');
    Route::post('/settings/header-menu', [HeaderMenuController::class, 'store'])->name('settings.header-menu.store');
    Route::put('/settings/header-menu/{item}', [HeaderMenuController::class, 'update'])->name('settings.header-menu.update');
    Route::delete('/settings/header-menu/{item}', [HeaderMenuController::class, 'destroy'])->name('settings.header-menu.destroy');
    Route::post('/settings/header-menu/reorder', [HeaderMenuController::class, 'reorder'])->name('settings.header-menu.reorder');

    // Shipping (Countries + Regions + Free shipping settings)
    Route::get('/settings/shipping', [ShippingRateController::class, 'index'])->name('settings.shipping');
    // Countries
    Route::post('/settings/shipping/countries', [ShippingRateController::class, 'storeCountry'])->name('settings.shipping.countries.store');
    Route::put('/settings/shipping/countries/{country}', [ShippingRateController::class, 'updateCountry'])->name('settings.shipping.countries.update');
    Route::delete('/settings/shipping/countries/{country}', [ShippingRateController::class, 'destroyCountry'])->name('settings.shipping.countries.destroy');
    // Regions
    Route::post('/settings/shipping/regions', [ShippingRateController::class, 'storeRegion'])->name('settings.shipping.regions.store');
    Route::put('/settings/shipping/regions/{region}', [ShippingRateController::class, 'updateRegion'])->name('settings.shipping.regions.update');
    Route::delete('/settings/shipping/regions/{region}', [ShippingRateController::class, 'destroyRegion'])->name('settings.shipping.regions.destroy');
    // Free shipping settings
    Route::put('/settings/shipping-threshold', [ShippingRateController::class, 'updateThreshold'])->name('settings.shipping.threshold');

    // Product discounts
    Route::get('/product-discounts', [ProductDiscountController::class, 'index'])->name('product-discounts.index');
    Route::post('/product-discounts', [ProductDiscountController::class, 'store'])->name('product-discounts.store');
    Route::put('/product-discounts/{discount}', [ProductDiscountController::class, 'update'])->name('product-discounts.update');
    Route::patch('/product-discounts/{discount}/toggle', [ProductDiscountController::class, 'toggle'])->name('product-discounts.toggle');
    Route::delete('/product-discounts/{discount}', [ProductDiscountController::class, 'destroy'])->name('product-discounts.destroy');

    // Coupons
    Route::resource('coupons', CouponController::class)->except(['show']);
    Route::patch('/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');

    // Orders
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/export/csv', [\App\Http\Controllers\Admin\OrderController::class, 'exportCsv'])->name('orders.export');
    Route::post('/orders/bulk-status', [\App\Http\Controllers\Admin\OrderController::class, 'bulkStatus'])->name('orders.bulk-status');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');
    Route::patch('/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('/orders/{order}/shipping', [\App\Http\Controllers\Admin\OrderController::class, 'updateShipping'])->name('orders.shipping');
    Route::post('/orders/{order}/resend-email', [\App\Http\Controllers\Admin\OrderController::class, 'resendEmail'])->name('orders.resend-email');
    Route::post('/orders/{order}/refresh-tracking', [\App\Http\Controllers\Admin\OrderController::class, 'refreshTracking'])->name('orders.refresh-tracking');
    Route::post('/orders/{order}/shipment/retry', [\App\Http\Controllers\Admin\OrderController::class, 'retryShipment'])->name('orders.shipment.retry');
    Route::post('/orders/{order}/shipment/sync',  [\App\Http\Controllers\Admin\OrderController::class, 'syncShipment'])->name('orders.shipment.sync');
    // حذف الطلبات معطّل عمداً — نحتفظ بكل السجلات.
    // Route::delete('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'destroy'])->name('orders.destroy');


    // Reports & Analytics
    Route::get('/reports/analytics', [\App\Http\Controllers\Admin\ReportController::class, 'analytics'])->name('reports.analytics');
    Route::get('/reports/sales',     [\App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/inventory', [\App\Http\Controllers\Admin\ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/coupons',   [\App\Http\Controllers\Admin\ReportController::class, 'coupons'])->name('reports.coupons');


    // Customers
    Route::get('/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'update'])->name('customers.update');
    Route::patch('/customers/{customer}/toggle-active', [\App\Http\Controllers\Admin\CustomerController::class, 'toggleActive'])->name('customers.toggle-active');
    Route::post('/customers/{customer}/send-email', [\App\Http\Controllers\Admin\CustomerController::class, 'sendEmail'])->name('customers.send-email');

    // Customer Groups
    Route::get('/customer-groups', [\App\Http\Controllers\Admin\CustomerGroupController::class, 'index'])->name('customer-groups.index');
    Route::post('/customer-groups', [\App\Http\Controllers\Admin\CustomerGroupController::class, 'store'])->name('customer-groups.store');
    Route::put('/customer-groups/{group}', [\App\Http\Controllers\Admin\CustomerGroupController::class, 'update'])->name('customer-groups.update');
    Route::delete('/customer-groups/{group}', [\App\Http\Controllers\Admin\CustomerGroupController::class, 'destroy'])->name('customer-groups.destroy');

    // Reviews
    Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/status', [\App\Http\Controllers\Admin\ReviewController::class, 'updateStatus'])->name('reviews.status');
    Route::post('/reviews/{review}/reply', [\App\Http\Controllers\Admin\ReviewController::class, 'reply'])->name('reviews.reply');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Stock Management
    Route::get('/stock', [\App\Http\Controllers\Admin\StockController::class, 'index'])->name('stock.index');
    Route::patch('/stock/{product}', [\App\Http\Controllers\Admin\StockController::class, 'update'])->name('stock.update');
    Route::post('/stock/bulk-update', [\App\Http\Controllers\Admin\StockController::class, 'bulkUpdate'])->name('stock.bulk-update');
    Route::get('/stock-history', [\App\Http\Controllers\Admin\StockController::class, 'history'])->name('stock.history');

    // Shipping Carriers
    Route::get('/shipping-carriers', [\App\Http\Controllers\Admin\ShippingCarrierController::class, 'index'])->name('shipping-carriers.index');
    Route::post('/shipping-carriers', [\App\Http\Controllers\Admin\ShippingCarrierController::class, 'store'])->name('shipping-carriers.store');
    Route::put('/shipping-carriers/{carrier}', [\App\Http\Controllers\Admin\ShippingCarrierController::class, 'update'])->name('shipping-carriers.update');
    Route::patch('/shipping-carriers/{carrier}/toggle', [\App\Http\Controllers\Admin\ShippingCarrierController::class, 'toggle'])->name('shipping-carriers.toggle');
    Route::delete('/shipping-carriers/{carrier}', [\App\Http\Controllers\Admin\ShippingCarrierController::class, 'destroy'])->name('shipping-carriers.destroy');
    Route::post('/shipping-carriers/install-aramex', [\App\Http\Controllers\Admin\ShippingCarrierController::class, 'installAramex'])->name('shipping-carriers.install-aramex');

    // Returns (RMA)
    Route::get('/returns', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'index'])->name('returns.index');
    Route::get('/returns/{return}', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'show'])->name('returns.show');
    Route::patch('/returns/{return}/status', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'updateStatus'])->name('returns.status');
    Route::post('/returns/{return}/aramex-pickup', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'schedulePickup'])->name('returns.aramex-pickup');
    Route::delete('/returns/{return}', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'destroy'])->name('returns.destroy');

    // ── Content Management ──
    // Blog posts
    Route::post('/blog/ai-generate', [\App\Http\Controllers\Admin\BlogPostController::class, 'aiGenerate'])->name('blog.ai-generate');
    Route::resource('blog', \App\Http\Controllers\Admin\BlogPostController::class)->except(['show']);

    // FAQs
    Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class)->only(['index','store','update','destroy']);
    Route::patch('/faqs/{faq}/toggle', [\App\Http\Controllers\Admin\FaqController::class, 'toggle'])->name('faqs.toggle');

    // Contact messages
    Route::get('/messages', [\App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'show'])->name('messages.show');
    Route::patch('/messages/{message}/status', [\App\Http\Controllers\Admin\ContactMessageController::class, 'updateStatus'])->name('messages.status');
    Route::delete('/messages/{message}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])->name('messages.destroy');

    // Newsletter subscribers
    Route::get('/subscribers', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'index'])->name('subscribers.index');
    Route::get('/subscribers/export', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'export'])->name('subscribers.export');
    Route::post('/subscribers/send-article', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'sendArticle'])->name('subscribers.send-article');
    Route::get('/subscribers/posts-search', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'searchPosts'])->name('subscribers.posts-search');

    Route::patch('/subscribers/{subscriber}/toggle', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'toggle'])->name('subscribers.toggle');
    Route::delete('/subscribers/{subscriber}', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'destroy'])->name('subscribers.destroy');

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');


});

Route::get('/dashboard', function () {
    return redirect()->route('account.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customer account area
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [\App\Http\Controllers\AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [\App\Http\Controllers\AccountController::class, 'order'])->name('orders.show');
        Route::get('/reviews', [\App\Http\Controllers\AccountController::class, 'reviews'])->name('reviews');
        Route::post('/reviews', [\App\Http\Controllers\AccountController::class, 'storeReview'])->name('reviews.store');

        // Returns (RMA)
        Route::get('/returns', [\App\Http\Controllers\CustomerReturnController::class, 'index'])->name('returns.index');
        Route::get('/orders/{order}/return', [\App\Http\Controllers\CustomerReturnController::class, 'create'])->name('returns.create');
        Route::post('/orders/{order}/return', [\App\Http\Controllers\CustomerReturnController::class, 'store'])->name('returns.store');
        Route::get('/returns/{return}', [\App\Http\Controllers\CustomerReturnController::class, 'show'])->name('returns.show');
    });
});


require __DIR__.'/auth.php';

// ===== Extra public pages (الشركة، التسويق، المحتوى) =====
Route::get('/about', [PagesController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/track-order', [TrackOrderController::class, 'show'])->middleware('throttle:30,1')->name('track-order');

Route::get('/offers', [OffersController::class, 'index'])->name('offers');

// Blog (public)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{slug}/comments', [BlogController::class, 'storeComment'])
    ->middleware('throttle:10,1')->name('blog.comments.store');

// FAQ dynamic (override existing static)
Route::get('/faqs-dynamic', [FaqController::class, 'index'])->name('faqs.dynamic');

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:10,1')->name('newsletter.subscribe');

// Compare (session-based, no auth)
Route::prefix('compare')->name('compare.')->group(function () {
    Route::get('/', [CompareController::class, 'index'])->name('index');
    Route::post('/add', [CompareController::class, 'add'])->name('add');
    Route::post('/remove', [CompareController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CompareController::class, 'clear'])->name('clear');
});

// Wishlist (requires auth)
Route::middleware('auth')->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
    Route::delete('/{wishlist}', [WishlistController::class, 'destroy'])->name('destroy');
});

// Locale & currency switchers (cookie-based; redirects back)
Route::get('/locale/{code}', function (string $code) {
    $svc = app(\App\Services\LanguageService::class);
    if ($svc->exists($code)) {
        cookie()->queue(cookie()->forever('locale', $code));
    }
    return back();
})->name('locale.switch');

Route::get('/currency/{code}', function (string $code) {
    $svc = app(\App\Services\CurrencyService::class);
    if ($svc->find($code)) {
        cookie()->queue(cookie()->forever('currency', strtoupper($code)));
    }
    return back();
})->name('currency.switch');

// Note: /{locale}/... prefix is handled globally by HandleLocalePrefix middleware,
// which strips the locale from the URI before routing and forces URL generation
// to include the prefix. No explicit prefix route is needed here.
