<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ایجاد جدول translations فقط در صورتی که وجود نداشته باشد
        if (! Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->string('locale', 10);
                $table->text('value');
                $table->string('group')->default('general');
                $table->timestamps();

                $table->unique(['key', 'locale']);
                $table->index('locale');
            });
        }

        // ایجاد جدول languages فقط در صورتی که وجود نداشته باشد
        if (! Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 10)->unique();
                $table->string('flag')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // در صورت رول‌بک، فقط جدول‌هایی که وجود دارند حذف می‌شوند
        Schema::dropIfExists('translations');
        Schema::dropIfExists('languages');
    }
};
