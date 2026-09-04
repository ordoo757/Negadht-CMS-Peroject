<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * اجرای میگریشن
     */
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            
            // اطلاعات پایه
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->default('custom');
            $table->string('version')->default('1.0.0');
            $table->string('status')->default('draft');
            
            // اطلاعات نویسنده
            $table->string('author')->nullable();
            $table->string('author_email')->nullable();
            $table->string('website')->nullable();
            $table->string('license')->nullable();
            
            // تنظیمات و داده‌های JSON
            $table->json('config')->nullable();
            $table->json('settings')->nullable();
            $table->json('tags')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('screenshots')->nullable();
            
            // مسیرهای فایل‌ها
            $table->string('preview_image')->nullable();
            $table->string('view_path')->nullable();
            $table->string('style_path')->nullable();
            $table->string('script_path')->nullable();
            
            // وضعیت‌ها
            $table->boolean('is_active')->default(true);
            $table->boolean('is_core')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_public')->default(true);
            
            // آمار
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            
            // زمان‌ها
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // ایندکس‌ها برای بهبود عملکرد
            $table->index('category');
            $table->index('status');
            $table->index('type');
            $table->index('is_active');
            $table->index('is_core');
            $table->index('is_system');
            $table->index('is_public');
            $table->index(['category', 'status']);
            $table->index(['is_active', 'view_count']);
        });

        // جدول وابستگی‌های کامپوننت‌ها (Many-to-Many)
        Schema::create('component_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')
                ->constrained('components')
                ->cascadeOnDelete();
            $table->foreignId('dependency_id')
                ->constrained('components')
                ->cascadeOnDelete();
            $table->string('version_constraint')->nullable();
            $table->timestamps();

            $table->unique(['component_id', 'dependency_id']);
            $table->index('dependency_id');
        });
    }

    /**
     * بازگشت میگریشن
     */
    public function down(): void
    {
        Schema::dropIfExists('component_dependencies');
        Schema::dropIfExists('components');
    }
};
