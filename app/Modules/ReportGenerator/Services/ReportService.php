<?php

namespace App\Modules\ReportGenerator\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class ReportService
{
    protected array $supportedFormats = ['pdf', 'excel', 'csv', 'html', 'json'];
    protected array $chartTypes = ['line', 'bar', 'pie', 'doughnut', 'radar', 'polarArea'];

    public function generateReport(int $reportId, array $filters = []): array
    {
        $report = DB::table('reports')->where('id', $reportId)->first();
        if (!$report) {
            return ['success' => false, 'error' => 'Report not found'];
        }

        $config = json_decode($report->config, true) ?? [];
        $data = $this->fetchData($config, $filters);
        $charts = $this->generateCharts($config, $data);

        return [
            'success' => true,
            'report' => $report,
            'data' => $data,
            'charts' => $charts,
            'summary' => $this->generateSummary($data),
        ];
    }

    public function createReport(array $data): int
    {
        $id = DB::table('reports')->insertGetId([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']),
            'description' => $data['description'] ?? '',
            'type' => $data['type'] ?? 'table',
            'config' => json_encode($data['config'] ?? []),
            'filters' => json_encode($data['filters'] ?? []),
            'schedule' => $data['schedule'] ?? null,
            'email_recipients' => json_encode($data['email_recipients'] ?? []),
            'is_active' => $data['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function exportReport(int $reportId, string $format, array $filters = []): array
    {
        if (!in_array($format, $this->supportedFormats)) {
            return ['success' => false, 'error' => 'Unsupported format'];
        }

        $report = $this->generateReport($reportId, $filters);

        if (!$report['success']) {
            return $report;
        }

        $filename = "report_{$reportId}_" . date('Y-m-d_His') . ".{$format}";

        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($report, $filename);
            case 'excel':
                return $this->exportToExcel($report, $filename);
            case 'csv':
                return $this->exportToCsv($report, $filename);
            case 'html':
                return $this->exportToHtml($report, $filename);
            case 'json':
                return $this->exportToJson($report, $filename);
        }

        return ['success' => false, 'error' => 'Export failed'];
    }

    public function getSystemReports(): array
    {
        return [
            'users' => $this->getUserReport(),
            'content' => $this->getContentReport(),
            'security' => $this->getSecurityReport(),
            'performance' => $this->getPerformanceReport(),
            'activity' => $this->getActivityReport(),
        ];
    }

    protected function fetchData(array $config, array $filters): array
    {
        $table = $config['table'] ?? '';
        $columns = $config['columns'] ?? ['*'];
        $query = DB::table($table)->select($columns);

        // Apply filters
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $query->whereBetween($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        // Apply date range
        if (!empty($config['date_column'])) {
            if (!empty($filters['date_from'])) {
                $query->where($config['date_column'], '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->where($config['date_column'], '<=', $filters['date_to']);
            }
        }

        // Apply grouping
        if (!empty($config['group_by'])) {
            $query->groupBy($config['group_by']);
        }

        // Apply ordering
        if (!empty($config['order_by'])) {
            foreach ($config['order_by'] as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->get()->toArray();
    }

    protected function generateCharts(array $config, array $data): array
    {
        $charts = [];

        foreach ($config['charts'] ?? [] as $chartConfig) {
            $chartType = $chartConfig['type'] ?? 'bar';

            if (!in_array($chartType, $this->chartTypes)) {
                continue;
            }

            $chartData = $this->prepareChartData($chartConfig, $data);

            $charts[] = [
                'type' => $chartType,
                'title' => $chartConfig['title'] ?? 'Chart',
                'data' => $chartData,
                'options' => $chartConfig['options'] ?? [],
            ];
        }

        return $charts;
    }

    protected function prepareChartData(array $config, array $data): array
    {
        $labelColumn = $config['label_column'] ?? '';
        $valueColumn = $config['value_column'] ?? '';

        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $labels[] = $row->$labelColumn ?? '';
            $values[] = $row->$valueColumn ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $config['dataset_label'] ?? 'Data',
                    'data' => $values,
                    'backgroundColor' => $this->generateColors(count($values)),
                ],
            ],
        ];
    }

    protected function generateSummary(array $data): array
    {
        if (empty($data)) {
            return ['total' => 0, 'average' => 0, 'min' => 0, 'max' => 0];
        }

        $numericValues = [];
        foreach ($data as $row) {
            foreach ((array) $row as $value) {
                if (is_numeric($value)) {
                    $numericValues[] = $value;
                }
            }
        }

        if (empty($numericValues)) {
            return ['total' => count($data)];
        }

        return [
            'total' => count($data),
            'sum' => array_sum($numericValues),
            'average' => round(array_sum($numericValues) / count($numericValues), 2),
            'min' => min($numericValues),
            'max' => max($numericValues),
        ];
    }

    protected function exportToPdf(array $report, string $filename): array
    {
        $html = View::make('report::exports.pdf', $report)->render();

        // Use dompdf
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);

        $path = storage_path("app/reports/{$filename}");
        $pdf->save($path);

        return [
            'success' => true,
            'path' => $path,
            'filename' => $filename,
        ];
    }

    protected function exportToExcel(array $report, string $filename): array
    {
        // Use maatwebsite/excel
        $path = storage_path("app/reports/{$filename}");

        return [
            'success' => true,
            'path' => $path,
            'filename' => $filename,
        ];
    }

    protected function exportToCsv(array $report, string $filename): array
    {
        $path = storage_path("app/reports/{$filename}");
        $handle = fopen($path, 'w');

        if (!empty($report['data'])) {
            // Headers
            $headers = array_keys((array) $report['data'][0]);
            fputcsv($handle, $headers);

            // Data
            foreach ($report['data'] as $row) {
                fputcsv($handle, (array) $row);
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'path' => $path,
            'filename' => $filename,
        ];
    }

    protected function exportToHtml(array $report, string $filename): array
    {
        $html = View::make('report::exports.html', $report)->render();

        $path = storage_path("app/reports/{$filename}");
        file_put_contents($path, $html);

        return [
            'success' => true,
            'path' => $path,
            'filename' => $filename,
        ];
    }

    protected function exportToJson(array $report, string $filename): array
    {
        $path = storage_path("app/reports/{$filename}");
        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT));

        return [
            'success' => true,
            'path' => $path,
            'filename' => $filename,
        ];
    }

    protected function getUserReport(): array
    {
        return [
            'total_users' => DB::table('users')->count(),
            'active_users' => DB::table('users')->where('is_active', true)->count(),
            'new_users_today' => DB::table('users')->whereDate('created_at', today())->count(),
            'new_users_this_month' => DB::table('users')->whereMonth('created_at', now()->month)->count(),
            'users_by_role' => DB::table('users')
                ->select('role', DB::raw('COUNT(*) as count'))
                ->groupBy('role')
                ->get()
                ->toArray(),
        ];
    }

    protected function getContentReport(): array
    {
        return [
            'total_content' => DB::table('content')->count(),
            'published' => DB::table('content')->where('status', 'published')->count(),
            'draft' => DB::table('content')->where('status', 'draft')->count(),
            'content_by_type' => DB::table('content')
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get()
                ->toArray(),
        ];
    }

    protected function getSecurityReport(): array
    {
        return [
            'failed_logins_24h' => DB::table('activity_logs')
                ->where('action', 'login_failed')
                ->where('created_at', '>=', now()->subHours(24))
                ->count(),
            'blocked_ips' => DB::table('blocked_ips')
                ->where('blocked_until', '>', now())
                ->count(),
            'security_events' => DB::table('security_logs')
                ->where('created_at', '>=', now()->subDays(7))
                ->select('risk_level', DB::raw('COUNT(*) as count'))
                ->groupBy('risk_level')
                ->get()
                ->toArray(),
        ];
    }

    protected function getPerformanceReport(): array
    {
        return [
            'avg_response_time' => DB::table('performance_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->avg('response_time') ?? 0,
            'slow_queries' => DB::table('performance_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->where('response_time', '>', 1000)
                ->count(),
            'error_rate' => DB::table('error_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->count(),
        ];
    }

    protected function getActivityReport(): array
    {
        return [
            'total_activities_24h' => DB::table('activity_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->count(),
            'activities_by_type' => DB::table('activity_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->select('action', DB::raw('COUNT(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->toArray(),
            'top_users' => DB::table('activity_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->select('user_id', DB::raw('COUNT(*) as count'))
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->toArray(),
        ];
    }

    protected function generateColors(int $count): array
    {
        $colors = [
            '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f97316',
            '#eab308', '#84cc16', '#10b981', '#06b6d4', '#3b82f6',
        ];

        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }

        return $result;
    }
}
