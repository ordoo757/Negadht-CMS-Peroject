<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ایجاد جدول reports فقط در صورتی که وجود نداشته باشد
        if (! Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->json('config');
                $table->boolean('ai_analysis')->default(true);
                $table->foreignId('user_id')->nullable()->constrained();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_run_at')->nullable();
                $table->integer('run_count')->default(0);
                $table->timestamps();
            });
        }

        // ایجاد جدول scheduled_reports فقط در صورتی که وجود نداشته باشد
        if (! Schema::hasTable('scheduled_reports')) {
            Schema::create('scheduled_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_id')->constrained()->onDelete('cascade');
                $table->string('frequency');
                $table->json('recipients');
                $table->timestamp('next_run_at');
                $table->timestamp('last_run_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // در رول‌بک، هر دو جدول را با خیال راحت حذف می‌کنیم (اگر وجود داشته باشند)
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('reports');
    }
};
