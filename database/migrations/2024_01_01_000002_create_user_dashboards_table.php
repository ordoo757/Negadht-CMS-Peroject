<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('layout')->nullable();
            $table->json('widgets')->nullable();
            $table->json('favorites')->nullable();
            $table->string('theme')->default('default');
            $table->boolean('sidebar_state')->default(false);
            $table->json('quick_actions')->nullable();
            $table->json('ai_preferences')->nullable();
            $table->timestamps();
        });
        
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('user_dashboards');
    }
};
