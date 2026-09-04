<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['admin', 'site'])->default('site');
            $table->text('description')->nullable();
            $table->string('author')->default('NeuroCMS');
            $table->string('version')->default('1.0.0');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('path');
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
