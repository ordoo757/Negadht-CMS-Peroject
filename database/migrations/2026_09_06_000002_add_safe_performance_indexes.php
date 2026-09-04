<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =========================================================
        // ===== کاربران (users) =====
        // =========================================================
        Schema::table('users', function (Blueprint $table) {
            if ($this->columnExists('users', 'email') && !$this->hasIndex('users', 'users_email_index')) {
                $table->index('email');
            }
            if ($this->columnExists('users', 'role') && !$this->hasIndex('users', 'users_role_index')) {
                $table->index('role');
            }
            if ($this->columnExists('users', 'created_at') && !$this->hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at');
            }
            if ($this->columnExists('users', 'role') && $this->columnExists('users', 'created_at') && !$this->hasIndex('users', 'users_role_created_at_index')) {
                $table->index(['role', 'created_at']);
            }
        });

        // =========================================================
        // ===== صفحات (pages) =====
        // =========================================================
        Schema::table('pages', function (Blueprint $table) {
            if ($this->columnExists('pages', 'category_id') && !$this->hasIndex('pages', 'pages_category_id_index')) {
                $table->index('category_id');
            }
            if ($this->columnExists('pages', 'user_id') && !$this->hasIndex('pages', 'pages_user_id_index')) {
                $table->index('user_id');
            }
            if ($this->columnExists('pages', 'category_id') && $this->columnExists('pages', 'status') && !$this->hasIndex('pages', 'pages_category_id_status_index')) {
                $table->index(['category_id', 'status']);
            }
            if ($this->columnExists('pages', 'user_id') && $this->columnExists('pages', 'created_at') && !$this->hasIndex('pages', 'pages_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
        });

        // =========================================================
        // ===== دسته‌بندی‌ها (categories) =====
        // =========================================================
        Schema::table('categories', function (Blueprint $table) {
            if ($this->columnExists('categories', 'parent_id') && !$this->hasIndex('categories', 'categories_parent_id_index')) {
                $table->index('parent_id');
            }
            if ($this->columnExists('categories', 'parent_id') && $this->columnExists('categories', 'is_active') && !$this->hasIndex('categories', 'categories_parent_id_is_active_index')) {
                $table->index(['parent_id', 'is_active']);
            }
            if ($this->columnExists('categories', 'is_active') && $this->columnExists('categories', 'order') && !$this->hasIndex('categories', 'categories_is_active_order_index')) {
                $table->index(['is_active', 'order']);
            }
        });

        // =========================================================
        // ===== نظرات (comments) =====
        // =========================================================
        Schema::table('comments', function (Blueprint $table) {
            if ($this->columnExists('comments', 'user_id') && !$this->hasIndex('comments', 'comments_user_id_index')) {
                $table->index('user_id');
            }
            if ($this->columnExists('comments', 'status') && !$this->hasIndex('comments', 'comments_status_index')) {
                $table->index('status');
            }
            if ($this->columnExists('comments', 'user_id') && $this->columnExists('comments', 'created_at') && !$this->hasIndex('comments', 'comments_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
            if ($this->columnExists('comments', 'is_approved') && $this->columnExists('comments', 'created_at') && !$this->hasIndex('comments', 'comments_is_approved_created_at_index')) {
                $table->index(['is_approved', 'created_at']);
            }
        });

        // =========================================================
        // ===== رسانه‌ها (media) =====
        // =========================================================
        Schema::table('media', function (Blueprint $table) {
            if ($this->columnExists('media', 'user_id') && !$this->hasIndex('media', 'media_user_id_index')) {
                $table->index('user_id');
            }
            if ($this->columnExists('media', 'mime_type') && $this->columnExists('media', 'is_active') && !$this->hasIndex('media', 'media_mime_type_is_active_index')) {
                $table->index(['mime_type', 'is_active']);
            }
            if ($this->columnExists('media', 'user_id') && $this->columnExists('media', 'created_at') && !$this->hasIndex('media', 'media_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
        });

        // =========================================================
        // ===== کتاب‌های کار اکسل (excel_workbooks) =====
        // =========================================================
        Schema::table('excel_workbooks', function (Blueprint $table) {
            if ($this->columnExists('excel_workbooks', 'user_id') && !$this->hasIndex('excel_workbooks', 'excel_workbooks_user_id_index')) {
                $table->index('user_id');
            }
            if ($this->columnExists('excel_workbooks', 'user_id') && $this->columnExists('excel_workbooks', 'is_active') && !$this->hasIndex('excel_workbooks', 'excel_workbooks_user_id_is_active_index')) {
                $table->index(['user_id', 'is_active']);
            }
            if ($this->columnExists('excel_workbooks', 'is_active') && $this->columnExists('excel_workbooks', 'view_count') && !$this->hasIndex('excel_workbooks', 'excel_workbooks_is_active_view_count_index')) {
                $table->index(['is_active', 'view_count']);
            }
            if ($this->columnExists('excel_workbooks', 'created_at') && $this->columnExists('excel_workbooks', 'view_count') && !$this->hasIndex('excel_workbooks', 'excel_workbooks_created_at_view_count_index')) {
                $table->index(['created_at', 'view_count']);
            }
        });

        // =========================================================
        // ===== سلول‌های اکسل (excel_cells) =====
        // =========================================================
        Schema::table('excel_cells', function (Blueprint $table) {
            if ($this->columnExists('excel_cells', 'data_type') && !$this->hasIndex('excel_cells', 'excel_cells_data_type_index')) {
                $table->index('data_type');
            }
        });

        // =========================================================
        // ===== ارائه‌های پاورپوینت (presentations) =====
        // =========================================================
        Schema::table('presentations', function (Blueprint $table) {
            if ($this->columnExists('presentations', 'user_id') && $this->columnExists('presentations', 'is_active') && !$this->hasIndex('presentations', 'presentations_user_id_is_active_index')) {
                $table->index(['user_id', 'is_active']);
            }
            if ($this->columnExists('presentations', 'is_active') && $this->columnExists('presentations', 'view_count') && !$this->hasIndex('presentations', 'presentations_is_active_view_count_index')) {
                $table->index(['is_active', 'view_count']);
            }
        });

        // =========================================================
        // ===== اسلایدها (slides) =====
        // =========================================================
        Schema::table('slides', function (Blueprint $table) {
            if ($this->columnExists('slides', 'presentation_id') && $this->columnExists('slides', 'order') && !$this->hasIndex('slides', 'slides_presentation_id_order_index')) {
                $table->index(['presentation_id', 'order']);
            }
        });

        // =========================================================
        // ===== عناصر اسلاید (slide_elements) =====
        // =========================================================
        Schema::table('slide_elements', function (Blueprint $table) {
            if ($this->columnExists('slide_elements', 'slide_id') && $this->columnExists('slide_elements', 'order') && !$this->hasIndex('slide_elements', 'slide_elements_slide_id_order_index')) {
                $table->index(['slide_id', 'order']);
            }
        });

        // =========================================================
        // ===== الگوهای یادگیری AI (ai_learning_patterns) =====
        // =========================================================
        Schema::table('ai_learning_patterns', function (Blueprint $table) {
            if ($this->columnExists('ai_learning_patterns', 'user_id') && $this->columnExists('ai_learning_patterns', 'confidence') && !$this->hasIndex('ai_learning_patterns', 'ai_learning_patterns_user_id_confidence_index')) {
                $table->index(['user_id', 'confidence']);
            }
            if ($this->columnExists('ai_learning_patterns', 'activity_type') && $this->columnExists('ai_learning_patterns', 'frequency') && !$this->hasIndex('ai_learning_patterns', 'ai_learning_patterns_activity_type_frequency_index')) {
                $table->index(['activity_type', 'frequency']);
            }
            if ($this->columnExists('ai_learning_patterns', 'created_at') && $this->columnExists('ai_learning_patterns', 'confidence') && !$this->hasIndex('ai_learning_patterns', 'ai_learning_patterns_created_at_confidence_index')) {
                $table->index(['created_at', 'confidence']);
            }
        });

        // =========================================================
        // ===== گزارش‌های اسکن (scan_reports) =====
        // =========================================================
        Schema::table('scan_reports', function (Blueprint $table) {
            if ($this->columnExists('scan_reports', 'user_id') && $this->columnExists('scan_reports', 'status') && !$this->hasIndex('scan_reports', 'scan_reports_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
            if ($this->columnExists('scan_reports', 'type') && $this->columnExists('scan_reports', 'status') && !$this->hasIndex('scan_reports', 'scan_reports_type_status_index')) {
                $table->index(['type', 'status']);
            }
            if ($this->columnExists('scan_reports', 'created_at') && $this->columnExists('scan_reports', 'status') && !$this->hasIndex('scan_reports', 'scan_reports_created_at_status_index')) {
                $table->index(['created_at', 'status']);
            }
            if ($this->columnExists('scan_reports', 'started_at') && $this->columnExists('scan_reports', 'completed_at') && !$this->hasIndex('scan_reports', 'scan_reports_started_at_completed_at_index')) {
                $table->index(['started_at', 'completed_at']);
            }
        });

        // =========================================================
        // ===== فایل‌های قرنطینه (quarantine_files) =====
        // =========================================================
        Schema::table('quarantine_files', function (Blueprint $table) {
            if ($this->columnExists('quarantine_files', 'severity') && $this->columnExists('quarantine_files', 'is_restored') && !$this->hasIndex('quarantine_files', 'quarantine_files_severity_is_restored_index')) {
                $table->index(['severity', 'is_restored']);
            }
            if ($this->columnExists('quarantine_files', 'user_id') && $this->columnExists('quarantine_files', 'created_at') && !$this->hasIndex('quarantine_files', 'quarantine_files_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
        });

        // =========================================================
        // ===== لاگ‌های امنیتی (security_logs) =====
        // =========================================================
        Schema::table('security_logs', function (Blueprint $table) {
            if ($this->columnExists('security_logs', 'event') && !$this->hasIndex('security_logs', 'security_logs_event_index')) {
                $table->index('event');
            }
            if ($this->columnExists('security_logs', 'type') && !$this->hasIndex('security_logs', 'security_logs_type_index')) {
                $table->index('type');
            }
            if ($this->columnExists('security_logs', 'risk_level') && !$this->hasIndex('security_logs', 'security_logs_risk_level_index')) {
                $table->index('risk_level');
            }
            if ($this->columnExists('security_logs', 'created_at') && !$this->hasIndex('security_logs', 'security_logs_created_at_index')) {
                $table->index('created_at');
            }
        });

        // =========================================================
        // ===== آی‌پی‌های مسدود شده (blocked_ips) =====
        // =========================================================
        Schema::table('blocked_ips', function (Blueprint $table) {
            if ($this->columnExists('blocked_ips', 'ip_address') && !$this->hasIndex('blocked_ips', 'blocked_ips_ip_address_index')) {
                $table->index('ip_address');
            }
            if ($this->columnExists('blocked_ips', 'blocked_until') && !$this->hasIndex('blocked_ips', 'blocked_ips_blocked_until_index')) {
                $table->index('blocked_until');
            }
        });

        // =========================================================
        // ===== لاگ‌های فعالیت (activity_logs) =====
        // =========================================================
        Schema::table('activity_logs', function (Blueprint $table) {
            if ($this->columnExists('activity_logs', 'user_id') && $this->columnExists('activity_logs', 'created_at') && !$this->hasIndex('activity_logs', 'activity_logs_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
            if ($this->columnExists('activity_logs', 'action') && $this->columnExists('activity_logs', 'created_at') && !$this->hasIndex('activity_logs', 'activity_logs_action_created_at_index')) {
                $table->index(['action', 'created_at']);
            }
            if ($this->columnExists('activity_logs', 'ip_address') && $this->columnExists('activity_logs', 'created_at') && !$this->hasIndex('activity_logs', 'activity_logs_ip_address_created_at_index')) {
                $table->index(['ip_address', 'created_at']);
            }
        });
    }

    /**
     * بررسی وجود ایندکس در جدول
     */
    protected function hasIndex(string $table, string $indexName): bool
    {
        try {
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * بررسی وجود ستون در جدول
     */
    protected function columnExists(string $table, string $column): bool
    {
        try {
            $result = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function down(): void
    {
        // حذف ایندکس‌های اضافه شده (در صورت نیاز)
    }
};
