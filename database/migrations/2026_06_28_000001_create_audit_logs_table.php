<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 80)->index();          // e.g. admin.login.failed
            $table->string('actor_type', 30)->nullable();   // admin | user | guest
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('context')->nullable();            // non-sensitive metadata only
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
