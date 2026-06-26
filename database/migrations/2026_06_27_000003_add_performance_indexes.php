<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Composite indexes that match the actual hot query paths in this app:
     * - product listings filter by status/featured and order by created_at
     * - product listings scope by category_id + status
     * - header menu lookups filter by location + status + parent_id + position
     * - coupon lookups filter by code/active state
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'featured'], 'products_status_featured_idx');
            $table->index(['status', 'category_id'], 'products_status_category_idx');
            $table->index(['status', 'created_at'], 'products_status_created_idx');
            $table->index('stock', 'products_stock_idx');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'sort_order'], 'product_images_product_sort_idx');
        });

        if (Schema::hasTable('header_menu_items')) {
            Schema::table('header_menu_items', function (Blueprint $table) {
                $table->index(['location', 'status', 'parent_id', 'position'], 'header_menu_items_lookup_idx');
            });
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['parent_id', 'status', 'sort_order'], 'categories_parent_status_sort_idx');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->index(['is_active', 'used_count'], 'coupons_active_usage_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_featured_idx');
            $table->dropIndex('products_status_category_idx');
            $table->dropIndex('products_status_created_idx');
            $table->dropIndex('products_stock_idx');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_product_sort_idx');
        });

        if (Schema::hasTable('header_menu_items')) {
            Schema::table('header_menu_items', function (Blueprint $table) {
                $table->dropIndex('header_menu_items_lookup_idx');
            });
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_parent_status_sort_idx');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex('coupons_active_usage_idx');
        });
    }
};
