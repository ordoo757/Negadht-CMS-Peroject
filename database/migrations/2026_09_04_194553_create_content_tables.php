<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== جدول دسته‌بندی‌ها (اگر وجود ندارد ایجاد کن) =====
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('slug');
                $table->index('is_active');
            });
        }

        // ===== جدول صفحات (حذف و ایجاد مجدد) =====
        Schema::dropIfExists('pages');
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_home')->default(false);
            $table->integer('views')->default(0);

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->string('featured_image')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('status');
            $table->index('is_home');
            $table->index('published_at');
            $table->index(['status', 'published_at']);
        });

        // ===== جدول رسانه‌ها =====
        if (!Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('original_name');
                $table->string('path');
                $table->string('url');
                $table->string('mime_type');
                $table->integer('size');
                $table->string('alt')->nullable();
                $table->string('caption')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();

                $table->index('mime_type');
                $table->index('is_active');
            });
        }

        // ===== جدول نظرات =====
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->text('content');
                $table->boolean('is_approved')->default(false);
                $table->string('status')->default('pending');

                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();

                $table->timestamps();

                $table->index('is_approved');
                $table->index('status');
                $table->index(['page_id', 'is_approved']);
            });
        }

        // ===== جدول تنظیمات محتوا =====
        if (!Schema::hasTable('content_settings')) {
            Schema::create('content_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('text');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_settings');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('media');
        Schema::dropIfExists('pages');
        // categories را drop نمی‌کنیم تا داده‌ها از دست نروند
    }
};
