<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('reviews')) {
            return;
        }

        // Add status column if it doesn't exist
        if (!Schema::hasColumn('reviews', 'status')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->index()
                    ->after('body');
            });

            // Migrate data from old `approved` column if it exists
            if (Schema::hasColumn('reviews', 'approved')) {
                DB::statement("UPDATE reviews SET status = CASE WHEN approved = 1 THEN 'approved' ELSE 'pending' END");
            }
        }

        // Drop legacy `approved` column to avoid confusion
        if (Schema::hasColumn('reviews', 'approved')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn('approved');
            });
        }

        // Ensure other columns expected by the app exist
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'reviewer_name'))  $table->string('reviewer_name')->nullable();
            if (!Schema::hasColumn('reviews', 'reviewer_email')) $table->string('reviewer_email')->nullable();
            if (!Schema::hasColumn('reviews', 'title'))          $table->string('title')->nullable();
            if (!Schema::hasColumn('reviews', 'admin_reply'))    $table->text('admin_reply')->nullable();
            if (!Schema::hasColumn('reviews', 'replied_at'))     $table->timestamp('replied_at')->nullable();
        });
    }

    public function down(): void
    {
        // no-op
    }
};
