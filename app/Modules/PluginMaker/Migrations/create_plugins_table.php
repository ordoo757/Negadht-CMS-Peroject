<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->default('general');
            $table->string('version')->default('1.0.0');
            $table->string('status')->default('draft');
            $table->string('author')->nullable();
            $table->string('author_email')->nullable();
            $table->string('website')->nullable();
            $table->string('license')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_free')->default(true);
            $table->json('config')->nullable();
            $table->json('settings')->nullable();
            $table->json('tags')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('preview_image')->nullable();
            $table->string('view_path')->nullable();
            $table->string('style_path')->nullable();
            $table->string('script_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_core')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_public')->default(true);
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('category');
            $table->index('status');
            $table->index('type');
            $table->index('is_active');
            $table->index('is_free');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
