<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('return_requests', 'pickup_guid')) {
                $table->string('pickup_guid', 100)->nullable()->after('admin_note');
            }
            if (!Schema::hasColumn('return_requests', 'pickup_reference')) {
                $table->string('pickup_reference', 100)->nullable()->after('pickup_guid');
            }
            if (!Schema::hasColumn('return_requests', 'pickup_scheduled_at')) {
                $table->timestamp('pickup_scheduled_at')->nullable()->after('pickup_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            foreach (['pickup_guid','pickup_reference','pickup_scheduled_at'] as $c) {
                if (Schema::hasColumn('return_requests', $c)) $table->dropColumn($c);
            }
        });
    }
};
