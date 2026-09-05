<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $workbook->name }} - NeuroCMS Excel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Vazir', 'Segoe UI', Tahoma, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .excel-embed {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .excel-embed h1 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        .excel-embed .description {
            color: #666;
            margin-bottom: 15px;
        }
        .excel-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 14px;
        }
        .excel-table th,
        .excel-table td {
            border: 1px solid #ddd;
            padding: 6px 12px;
            text-align: right;
            min-width: 80px;
        }
        .excel-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .excel-table tr:nth-child(even) {
            background: #fafafa;
        }
        .excel-footer {
            margin-top: 15px;
            font-size: 12px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="excel-embed">
        <h1>{{ $workbook->name }}</h1>
        @if($workbook->description)
            <div class="description">{{ $workbook->description }}</div>
        @endif

        <div class="table-responsive">
            <table class="excel-table">
                <thead>
                    <tr>
                        <th></th>
                        @for($i = 1; $i <= 10; $i++)
                            <th>{{ chr(64 + $i) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        $cells = [];
                        foreach($workbook->worksheets->first()?->cells ?? [] as $cell) {
                            $cells[$cell->cell_id] = $cell->value;
                        }
                    @endphp

                    @for($row = 1; $row <= 15; $row++)
                        <tr>
                            <td><strong>{{ $row }}</strong></td>
                            @for($col = 1; $col <= 10; $col++)
                                @php
                                    $cellId = chr(64 + $col) . $row;
                                    $value = $cells[$cellId] ?? '';
                                @endphp
                                <td>{{ $value }}</td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="excel-footer">
            ارائه‌شده توسط NeuroCMS &bull; نسخه {{ $workbook->version ?? '1.0.0' }}
        </div>
    </div>
</body>
</html>
