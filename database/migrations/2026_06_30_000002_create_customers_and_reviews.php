<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('badge_color', 20)->default('violet');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->foreignId('customer_group_id')->nullable()->after('phone')->constrained('customer_groups')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('customer_group_id');
            $table->text('admin_notes')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('admin_notes');
            $table->index('is_active');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reviewer_name')->nullable();
            $table->string('reviewer_email')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body');
            $table->enum('status', ['pending','approved','rejected'])->default('pending')->index();
            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['customer_group_id']);
            $table->dropColumn(['phone','customer_group_id','is_active','admin_notes','last_login_at']);
        });
        Schema::dropIfExists('customer_groups');
    }
};
