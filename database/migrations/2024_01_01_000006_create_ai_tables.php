<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('ai_learning_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('pattern_hash')->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('pattern');
            $table->string('activity_type');
            $table->integer('frequency')->default(1);
            $table->decimal('confidence', 3, 2)->default(0.5);
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('activity_count')->default(0);
            $table->json('preferred_hours')->nullable();
            $table->json('common_actions')->nullable();
            $table->decimal('risk_score', 3, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('ai_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('type');
            $table->integer('input_size')->default(0);
            $table->string('provider')->default('local');
            $table->timestamps();
        });

        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('event');
            $table->string('ip_address')->nullable();
            $table->integer('risk_score')->default(0);
            $table->string('risk_level')->default('low');
            $table->json('factors')->nullable();
            $table->timestamps();
        });

        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->index();
            $table->text('reason')->nullable();
            $table->timestamp('blocked_until');
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('action');
            $table->string('url')->nullable();
            $table->text('input')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('risk_level')->default('low');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('ai_activity_logs');
        Schema::dropIfExists('ai_user_profiles');
        Schema::dropIfExists('ai_learning_patterns');
        Schema::dropIfExists('ai_configs');
    }
};
