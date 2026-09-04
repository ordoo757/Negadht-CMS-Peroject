<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول ارائه‌ها
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('theme')->default('default');
            $table->json('settings')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_public');
        });

        // جدول اسلایدها
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_id')->constrained('presentations')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->integer('order')->default(0);
            $table->string('layout')->default('default');
            $table->string('background')->nullable();
            $table->string('transition')->nullable();
            $table->string('animation')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('presentation_id');
            $table->index('order');
        });

        // جدول عناصر اسلاید
        Schema::create('slide_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slide_id')->constrained('slides')->cascadeOnDelete();
            $table->string('type'); // text, image, shape, chart, table, video
            $table->longText('content')->nullable();
            $table->json('style')->nullable();
            $table->json('position')->nullable(); // {x, y}
            $table->json('size')->nullable(); // {width, height}
            $table->integer('order')->default(0);
            $table->string('animation')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('slide_id');
            $table->index('type');
            $table->index('order');
        });

        // جدول تنظیمات کاربران برای هر ارائه
        Schema::create('presentation_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_id')->constrained('presentations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_share')->default(false);
            $table->boolean('can_present')->default(false);
            $table->timestamps();

            $table->unique(['presentation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentation_user_permissions');
        Schema::dropIfExists('slide_elements');
        Schema::dropIfExists('slides');
        Schema::dropIfExists('presentations');
    }
};
