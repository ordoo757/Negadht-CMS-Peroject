<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Workbook
        Schema::create('excel_workbooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('settings')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });

        // Worksheet
        Schema::create('excel_worksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workbook_id')->constrained('excel_workbooks')->cascadeOnDelete();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // Cell
        Schema::create('excel_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheet_id')->constrained('excel_worksheets')->cascadeOnDelete();
            $table->string('cell_id');
            $table->text('value')->nullable();
            $table->string('data_type')->default('text');
            $table->json('style')->nullable();
            $table->text('formula')->nullable();
            $table->timestamps();

            $table->unique(['worksheet_id', 'cell_id']);
        });

        // Chart
        Schema::create('excel_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheet_id')->constrained('excel_worksheets')->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->string('data_range');
            $table->json('config')->nullable();
            $table->json('position')->nullable();
            $table->json('size')->nullable();
            $table->timestamps();
        });

        // Permissions
        Schema::create('excel_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workbook_id')->constrained('excel_workbooks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_share')->default(false);
            $table->timestamps();

            $table->unique(['workbook_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_user_permissions');
        Schema::dropIfExists('excel_charts');
        Schema::dropIfExists('excel_cells');
        Schema::dropIfExists('excel_worksheets');
        Schema::dropIfExists('excel_workbooks');
    }
};
