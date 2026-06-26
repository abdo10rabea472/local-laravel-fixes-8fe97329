<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\HeaderMenuItem;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ShippingCountry;
use App\Models\ShippingRegion;
use App\Services\NavigationService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share navigation data with header/footer through a single cached call.
        View::composer(['components.front-header', 'components.front-footer'], function ($view) {
            $nav = NavigationService::getData();

            $view->with([
                'navCategories' => $nav['colleges'],
                'navTotalProducts' => $nav['totalProducts'],
                'navTotalColleges' => $nav['totalColleges'],
                'navHeaderMenu' => $nav['headerMenu'],
                'navTopMenu' => $nav['topMenu'],
                'navFooterMenu' => $nav['footerMenu'],
            ]);
        });

        // Auto-invalidate navigation/dashboard/coupon caches whenever the
        // underlying data changes so admins never see stale content.
        $invalidate = fn () => NavigationService::clearCache();
        foreach ([
            Category::class,
            Product::class,
            ProductDiscount::class,
            HeaderMenuItem::class,
            ShippingCountry::class,
            ShippingRegion::class,
            Coupon::class,
        ] as $model) {
            $model::saved($invalidate);
            $model::deleted($invalidate);
        }
    }
}
