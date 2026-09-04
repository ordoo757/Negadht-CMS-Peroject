<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_learning_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('pattern_hash')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('pattern');
            $table->string('activity_type');
            $table->integer('frequency')->default(1);
            $table->float('confidence')->default(0.5);
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('activity_count')->default(0);
            $table->json('preferred_hours')->nullable();
            $table->json('common_actions')->nullable();
            $table->float('risk_score')->default(0);
            $table->timestamps();
        });

        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('weights')->nullable();
            $table->float('training_time')->nullable();
            $table->integer('samples_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_models');
        Schema::dropIfExists('ai_user_profiles');
        Schema::dropIfExists('ai_learning_patterns');
    }
};
