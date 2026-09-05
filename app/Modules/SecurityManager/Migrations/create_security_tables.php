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
        // جدول تنظیمات امنیتی
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول لاگ‌های امنیتی
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('type');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->string('risk_level')->default('low');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('event');
            $table->index('type');
            $table->index('risk_level');
            $table->index('is_resolved');
            $table->index('created_at');
        });

        // جدول آی‌پی‌های مسدود شده
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique();
            $table->text('reason')->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->timestamp('blocked_until')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('ip_address');
            $table->index('is_permanent');
            $table->index('blocked_until');
        });

        // جدول آی‌پی‌های مجاز (WhiteList)
        Schema::create('whitelist_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique();
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('ip_address');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whitelist_ips');
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('security_settings');
    }
};
