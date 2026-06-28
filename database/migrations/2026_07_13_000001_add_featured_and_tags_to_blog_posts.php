<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->index();
            }
            if (!Schema::hasColumn('blog_posts', 'tags')) {
                $table->string('tags')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (Schema::hasColumn('blog_posts', 'is_featured')) {
                $table->dropIndex(['is_featured']);
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('blog_posts', 'tags')) {
                $table->dropColumn('tags');
            }
        });
    }
};
