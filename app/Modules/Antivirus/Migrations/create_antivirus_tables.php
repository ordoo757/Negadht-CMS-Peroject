<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== تعاریف ویروس‌ها =====
        Schema::create('virus_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('pattern');
            $table->string('type')->default('php');
            $table->string('severity')->default('medium');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('version')->default('1.0.0');
            $table->timestamps();

            $table->index('type');
            $table->index('severity');
            $table->index('is_active');
        });

        // ===== گزارش‌های اسکن =====
        Schema::create('scan_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('full');
            $table->string('path')->nullable();
            $table->integer('file_count')->default(0);
            $table->integer('infected_count')->default(0);
            $table->integer('scanned_count')->default(0);
            $table->string('status')->default('pending');
            $table->json('result')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->float('duration')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('started_at');
            $table->index('completed_at');
        });

        // ===== فایل‌های قرنطینه =====
        Schema::create('quarantine_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_path');
            $table->string('quarantine_path');
            $table->string('filename');
            $table->integer('size');
            $table->string('mime_type')->nullable();
            $table->text('reason')->nullable();
            $table->string('virus_name')->nullable();
            $table->string('severity')->default('medium');
            $table->boolean('is_restored')->default(false);
            $table->timestamp('restored_at')->nullable();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('filename');
            $table->index('severity');
            $table->index('is_restored');
        });

        // ===== اسکن‌های زمان‌بندی‌شده =====
        Schema::create('scheduled_scans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->string('schedule'); // daily, weekly, monthly, custom
            $table->string('cron_expression')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('options')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();

            $table->index('schedule');
            $table->index('is_active');
            $table->index('next_run_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_scans');
        Schema::dropIfExists('quarantine_files');
        Schema::dropIfExists('scan_reports');
        Schema::dropIfExists('virus_definitions');
    }
};
