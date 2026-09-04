<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('fields');
            $table->json('settings')->nullable();
            $table->string('css_class')->nullable();
            $table->text('success_message')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('email_notifications')->default(false);
            $table->string('notification_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('form_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('columns');
            $table->enum('data_source', ['manual', 'database', 'query', 'api'])->default('manual');
            $table->text('query')->nullable();
            $table->string('table_name')->nullable();
            $table->json('settings')->nullable();
            $table->string('css_class')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
        Schema::dropIfExists('form_responses');
        Schema::dropIfExists('forms');
    }
};
