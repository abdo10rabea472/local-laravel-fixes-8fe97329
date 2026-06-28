<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('orders')) return;

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_gateway'))   $table->string('payment_gateway', 50)->nullable()->index();
            if (! Schema::hasColumn('orders', 'payment_reference')) $table->string('payment_reference')->nullable()->index();
            if (! Schema::hasColumn('orders', 'payment_response'))  $table->json('payment_response')->nullable();
            if (! Schema::hasColumn('orders', 'payment_fees'))      $table->decimal('payment_fees', 12, 2)->default(0);
            if (! Schema::hasColumn('orders', 'paid_at'))           $table->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) return;
        Schema::table('orders', function (Blueprint $table) {
            foreach (['payment_gateway','payment_reference','payment_response','payment_fees','paid_at'] as $c) {
                if (Schema::hasColumn('orders', $c)) $table->dropColumn($c);
            }
        });
    }
};
