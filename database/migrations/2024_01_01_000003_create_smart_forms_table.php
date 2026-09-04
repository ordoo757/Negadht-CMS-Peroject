<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('fields_json')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('ai_enabled')->default(false);
            $table->text('ai_prompt')->nullable();
            $table->integer('submission_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('success_message')->nullable();
            $table->string('redirect_url')->nullable();
            $table->json('email_notifications')->nullable();
            $table->timestamps();
        });
        
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('smart_forms')->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->string('label');
            $table->string('placeholder')->nullable();
            $table->string('validation_rules')->nullable();
            $table->json('options')->nullable();
            $table->text('default_value')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->json('ai_suggestions')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
        
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('smart_forms')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->string('status')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('smart_forms');
    }
};
