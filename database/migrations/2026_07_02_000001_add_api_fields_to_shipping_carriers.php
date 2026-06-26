<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipping_carriers', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_carriers', 'api_endpoint')) {
                $table->string('api_endpoint', 500)->nullable()->after('tracking_url_template');
            }
            if (!Schema::hasColumn('shipping_carriers', 'api_key')) {
                $table->string('api_key', 255)->nullable()->after('api_endpoint');
            }
            if (!Schema::hasColumn('shipping_carriers', 'webhook_secret')) {
                $table->string('webhook_secret', 100)->nullable()->after('api_key');
            }
            if (!Schema::hasColumn('shipping_carriers', 'auto_track')) {
                $table->boolean('auto_track')->default(false)->after('webhook_secret');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_status')) {
                $table->string('tracking_status', 60)->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('orders', 'tracking_last_sync_at')) {
                $table->timestamp('tracking_last_sync_at')->nullable()->after('tracking_status');
            }
            if (!Schema::hasColumn('orders', 'tracking_history')) {
                $table->json('tracking_history')->nullable()->after('tracking_last_sync_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipping_carriers', function (Blueprint $table) {
            foreach (['api_endpoint','api_key','webhook_secret','auto_track'] as $c) {
                if (Schema::hasColumn('shipping_carriers', $c)) $table->dropColumn($c);
            }
        });
        Schema::table('orders', function (Blueprint $table) {
            foreach (['tracking_status','tracking_last_sync_at','tracking_history'] as $c) {
                if (Schema::hasColumn('orders', $c)) $table->dropColumn($c);
            }
        });
    }
};
