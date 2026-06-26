<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Shipping defaults on the user profile
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'shipping_country'))   $table->string('shipping_country', 100)->nullable()->after('phone');
            if (! Schema::hasColumn('users', 'shipping_region'))    $table->string('shipping_region', 100)->nullable()->after('shipping_country');
            if (! Schema::hasColumn('users', 'shipping_city'))      $table->string('shipping_city', 100)->nullable()->after('shipping_region');
            if (! Schema::hasColumn('users', 'shipping_address'))   $table->string('shipping_address', 255)->nullable()->after('shipping_city');
            if (! Schema::hasColumn('users', 'shipping_postcode'))  $table->string('shipping_postcode', 20)->nullable()->after('shipping_address');
        });

        // 2) Payment fields on orders
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_gateway'))   $table->string('payment_gateway', 50)->nullable()->after('payment_status')->index();
            if (! Schema::hasColumn('orders', 'payment_reference')) $table->string('payment_reference')->nullable()->after('payment_gateway')->index();
            if (! Schema::hasColumn('orders', 'payment_response'))  $table->json('payment_response')->nullable()->after('payment_reference');
            if (! Schema::hasColumn('orders', 'payment_fees'))      $table->decimal('payment_fees', 12, 2)->default(0)->after('payment_response');
            if (! Schema::hasColumn('orders', 'paid_at'))           $table->timestamp('paid_at')->nullable()->after('payment_fees');
        });

        // 3) Payment gateways admin table
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();           // e.g. cod, paymob, stripe, paypal ...
            $table->string('driver', 50);                   // Nafezly factory key, or 'cod'
            $table->string('name');                         // display name (ar/en)
            $table->string('logo')->nullable();             // public path
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('sandbox')->default(true);
            $table->decimal('extra_fees', 12, 2)->default(0);
            $table->json('allowed_countries')->nullable();  // ['EG','SA'] or null = all
            $table->json('config')->nullable();             // gateway-specific keys
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
        Schema::table('orders', function (Blueprint $table) {
            foreach (['payment_gateway','payment_reference','payment_response','payment_fees','paid_at'] as $c) {
                if (Schema::hasColumn('orders', $c)) $table->dropColumn($c);
            }
        });
        Schema::table('users', function (Blueprint $table) {
            foreach (['shipping_country','shipping_region','shipping_city','shipping_address','shipping_postcode'] as $c) {
                if (Schema::hasColumn('users', $c)) $table->dropColumn($c);
            }
        });
    }
};
