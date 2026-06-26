<?php

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


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductCatalogController::class, 'index'])->name('products.index');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])
    ->middleware('throttle:30,1')
    ->name('checkout.apply-coupon');
Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])
    ->middleware('throttle:30,1')
    ->name('checkout.place-order');
Route::get('/checkout/stocks', [CheckoutController::class, 'stocks'])
    ->middleware('throttle:60,1')
    ->name('checkout.stocks');
Route::get('/payment/success', [PageController::class, 'paymentSuccess'])->name('pages.payment-success');
Route::get('/faqs', [PageController::class, 'faqs'])->name('pages.faqs');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/returns-refunds', [PageController::class, 'returns'])->name('pages.returns');

Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/product/{slug}', [FrontProductController::class, 'show'])->name('product.show');

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

    Route::resource('products', ProductController::class)->except(['show']);
    // Site Settings
    Route::get('/settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SiteSettingController::class, 'update'])->name('settings.update');

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

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
