<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module')->default('core');
            $table->string('action'); // create, read, update, delete, publish, export, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->json('conditions')->nullable(); // شرایط اضافی
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->json('meta')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('template_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->enum('type', ['admin', 'site', 'dashboard', 'login']);
            $table->json('structure'); // ساختار JSON Layout
            $table->json('positions')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('template_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // header, footer, sidebar, content, widget
            $table->string('position');
            $table->integer('order')->default(0);
            $table->json('config');
            $table->text('html_content')->nullable();
            $table->text('css_content')->nullable();
            $table->text('js_content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('template_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained();
            $table->morphs('assignable'); // به هر موجودیتی قابل اتصال
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_assignments');
        Schema::dropIfExists('template_components');
        Schema::dropIfExists('template_layouts');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
