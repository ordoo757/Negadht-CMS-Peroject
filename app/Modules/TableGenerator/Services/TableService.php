<?php

namespace App\Modules\TableGenerator\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TableService
{
    protected string $cachePrefix = 'table_';

    public function getTable(int $id): ?object
    {
        return DB::table('tables')->where('id', $id)->first();
    }

    public function createTable(array $data): int
    {
        $id = DB::table('tables')->insertGetId([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']),
            'description' => $data['description'] ?? '',
            'columns' => json_encode($data['columns'] ?? []),
            'data_source' => $data['data_source'] ?? 'manual',
            'query' => $data['query'] ?? null,
            'table_name' => $data['table_name'] ?? null,
            'settings' => json_encode($data['settings'] ?? []),
            'css_class' => $data['css_class'] ?? '',
            'is_active' => $data['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function renderTable(int $tableId, array $params = []): string
    {
        $table = $this->getTable($tableId);
        if (!$table || !$table->is_active) {
            return '<div class="alert alert-warning">جدول یافت نشد یا غیرفعال است.</div>';
        }

        $columns = json_decode($table->columns, true) ?? [];
        $settings = json_decode($table->settings, true) ?? [];
        $data = $this->fetchTableData($table, $params);

        $html = '<div class="neuro-table-wrapper ' . ($table->css_class ?? '') . '">';

        if ($settings['searchable'] ?? false) {
            $html .= '<div class="table-search">';
            $html .= '<input type="text" class="table-search-input" placeholder="جستجو...">';
            $html .= '</div>';
        }

        $html .= '<table class="neuro-table ' . ($settings['striped'] ? 'table-striped' : '') . ' ' . ($settings['bordered'] ? 'table-bordered' : '') . '">';

        // Header
        $html .= '<thead><tr>';
        foreach ($columns as $column) {
            $html .= '<th>';
            $html .= $column['label'] ?? $column['name'];
            if ($column['sortable'] ?? false) {
                $html .= ' <span class="sort-icon">↕</span>';
            }
            $html .= '</th>';
        }
        if ($settings['actions'] ?? false) {
            $html .= '<th>عملیات</th>';
        }
        $html .= '</tr></thead>';

        // Body
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($columns as $column) {
                $value = $row->{$column['name']} ?? '';
                $html .= '<td>' . $this->formatValue($value, $column['format'] ?? null) . '</td>';
            }
            if ($settings['actions'] ?? false) {
                $html .= '<td class="actions">';
                $html .= '<button class="btn-edit">ویرایش</button>';
                $html .= '<button class="btn-delete">حذف</button>';
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        // Pagination
        if ($settings['pagination'] ?? false) {
            $html .= '<div class="table-pagination">';
            $html .= '<button class="btn-prev">قبلی</button>';
            $html .= '<span class="page-info">صفحه 1 از 1</span>';
            $html .= '<button class="btn-next">بعدی</button>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function fetchTableData(object $table, array $params = []): array
    {
        $source = $table->data_source;

        switch ($source) {
            case 'database':
                return $this->fetchFromDatabase($table, $params);
            case 'query':
                return $this->fetchFromQuery($table, $params);
            case 'api':
                return $this->fetchFromApi($table, $params);
            case 'manual':
            default:
                return json_decode($table->manual_data ?? '[]', true) ?? [];
        }
    }

    protected function fetchFromDatabase(object $table, array $params): array
    {
        $tableName = $table->table_name;
        if (!$tableName) return [];

        $query = DB::table($tableName);

        // Apply filters
        if (!empty($params['filters'])) {
            foreach ($params['filters'] as $column => $value) {
                $query->where($column, $value);
            }
        }

        // Apply search
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search, $table) {
                $columns = json_decode($table->columns, true) ?? [];
                foreach ($columns as $column) {
                    $q->orWhere($column['name'], 'like', "%{$search}%");
                }
            });
        }

        // Apply sorting
        if (!empty($params['sort'])) {
            $query->orderBy($params['sort'], $params['direction'] ?? 'asc');
        }

        // Apply pagination
        if (!empty($params['per_page'])) {
            return $query->paginate($params['per_page'])->items();
        }

        return $query->limit($params['limit'] ?? 100)->get()->toArray();
    }

    protected function fetchFromQuery(object $table, array $params): array
    {
        $query = $table->query;
        if (!$query) return [];

        try {
            return DB::select($query, $params['bindings'] ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function fetchFromApi(object $table, array $params): array
    {
        $apiUrl = $table->api_url;
        if (!$apiUrl) return [];

        try {
            $response = \Illuminate\Support\Facades\Http::get($apiUrl, $params);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function formatValue($value, ?string $format): string
    {
        if ($format === 'date') {
            return $value ? date('Y/m/d', strtotime($value)) : '';
        }
        if ($format === 'datetime') {
            return $value ? date('Y/m/d H:i', strtotime($value)) : '';
        }
        if ($format === 'price') {
            return number_format($value ?? 0) . ' تومان';
        }
        if ($format === 'boolean') {
            return $value ? '<span class="badge badge-success">بله</span>' : '<span class="badge badge-danger">خیر</span>';
        }
        if ($format === 'image') {
            return $value ? '<img src="' . $value . '" class="table-image" alt="">' : '-';
        }

        return (string) $value;
    }
}
