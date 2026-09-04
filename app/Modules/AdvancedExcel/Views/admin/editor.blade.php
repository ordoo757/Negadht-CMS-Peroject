@extends('core::layouts.admin')

@section('title', 'ویرایشگر اکسل - ' . $workbook->name)

@push('styles')
<style>
    .excel-container {
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: auto;
        background: #fff;
    }
    .excel-table {
        border-collapse: collapse;
        width: 100%;
    }
    .excel-table th,
    .excel-table td {
        border: 1px solid #ddd;
        padding: 5px 10px;
        min-width: 80px;
        height: 30px;
        text-align: right;
        font-family: 'Vazir', sans-serif;
    }
    .excel-table th {
        background: #f8f9fa;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .excel-table td:first-child {
        background: #f8f9fa;
        font-weight: bold;
        position: sticky;
        left: 0;
        z-index: 10;
    }
    .excel-table td:first-child {
        background: #f8f9fa;
    }
    .excel-table td.editable {
        cursor: cell;
    }
    .excel-table td.editable:hover {
        background: #f0f7ff;
    }
    .excel-table td.editable:focus {
        background: #e3f2fd;
        outline: 2px solid #2196f3;
    }
    .excel-toolbar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }
    .excel-toolbar .btn {
        font-size: 12px;
        padding: 4px 10px;
    }
    .cell-address {
        display: inline-block;
        background: #e9ecef;
        padding: 2px 10px;
        border-radius: 3px;
        font-family: monospace;
        font-size: 14px;
    }
    .formula-bar {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
    }
    .formula-bar input {
        flex: 1;
        padding: 5px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: monospace;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایشگر اکسل: {{ $workbook->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.advanced-excel.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> بازگشت
                        </a>
                        <button onclick="saveWorkbook()" class="btn btn-success btn-sm">
                            <i class="fas fa-save"></i> ذخیره
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    {{-- نوار ابزار --}}
                    <div class="excel-toolbar">
                        <div class="cell-address" id="cellAddress">A1</div>
                        <button onclick="undoCell()" class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo"></i> بازگشت
                        </button>
                        <button onclick="redoCell()" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i> جلو
                        </button>
                        <span class="badge badge-info" id="cellInfo">خالی</span>
                    </div>

                    {{-- نوار فرمول --}}
                    <div class="formula-bar">
                        <span class="badge badge-secondary">fx</span>
                        <input type="text" id="formulaInput" placeholder="فرمول را وارد کنید..." onchange="applyFormula()">
                    </div>

                    {{-- جدول اکسل --}}
                    <div class="excel-container">
                        <table class="excel-table" id="excelTable">
                            <thead id="excelHeader">
                                <tr>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="excelBody">
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            برای ویرایش سلول، روی آن کلیک کنید.
                            برای اعمال فرمول، از نوار فرمول استفاده کنید.
                            فرمول‌های پشتیبانی‌شده: SUM, AVERAGE, COUNT, MAX, MIN
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const workbookId = {{ $workbook->id }};
    const worksheets = @json($workbook->worksheets);
    let currentWorksheet = worksheets[0] || null;
    let currentCell = 'A1';
    let cellData = {};
    let history = [];
    let historyIndex = -1;

    // ===== Initialize =====
    document.addEventListener('DOMContentLoaded', function() {
        if (currentWorksheet) {
            loadWorksheetData(currentWorksheet.id);
        }
    });

    // ===== Load Worksheet Data =====
    function loadWorksheetData(worksheetId) {
        fetch('{{ url("admin/excel/worksheets") }}/' + worksheetId + '/data')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cellData = data.data || {};
                    renderTable();
                }
            })
            .catch(error => console.error('Error loading worksheet:', error));
    }

    // ===== Render Table =====
    function renderTable() {
        const rows = 20;
        const cols = 10;

        // Header
        const header = document.getElementById('excelHeader');
        let headerHtml = '<tr><th></th>';
        for (let i = 1; i <= cols; i++) {
            headerHtml += '<th>' + String.fromCharCode(64 + i) + '</th>';
        }
        headerHtml += '</tr>';
        header.innerHTML = headerHtml;

        // Body
        const body = document.getElementById('excelBody');
        let bodyHtml = '';
        for (let row = 1; row <= rows; row++) {
            bodyHtml += '<tr>';
            bodyHtml += '<td>' + row + '</td>';
            for (let col = 1; col <= cols; col++) {
                const cellId = String.fromCharCode(64 + col) + row;
                const value = cellData[cellId]?.value || '';
                const formula = cellData[cellId]?.formula || '';
                const display = formula ? '=' + formula : value;
                bodyHtml += '<td class="editable" data-cell="' + cellId + '" ' +
                    'onclick="selectCell(\'' + cellId + '\')" ' +
                    'oninput="updateCell(\'' + cellId + '\', this.innerText)">' +
                    display + '</td>';
            }
            bodyHtml += '</tr>';
        }
        body.innerHTML = bodyHtml;
    }

    // ===== Select Cell =====
    function selectCell(cellId) {
        currentCell = cellId;
        document.getElementById('cellAddress').textContent = cellId;

        const cell = cellData[cellId] || { value: '', formula: '' };
        const formulaInput = document.getElementById('formulaInput');

        if (cell.formula) {
            formulaInput.value = '=' + cell.formula;
        } else {
            formulaInput.value = cell.value || '';
        }

        updateCellInfo(cell);
    }

    // ===== Update Cell =====
    function updateCell(cellId, value) {
        // ذخیره در تاریخچه
        saveHistory();

        const oldData = cellData[cellId] || { value: '', formula: '' };

        // به‌روزرسانی
        cellData[cellId] = {
            value: value,
            formula: oldData.formula || '',
            data_type: detectDataType(value),
        };

        // ارسال به سرور
        saveCell(cellId, value, '');
    }

    // ===== Apply Formula =====
    function applyFormula() {
        const input = document.getElementById('formulaInput');
        const value = input.value.trim();

        if (!currentCell) return;

        saveHistory();

        let formula = '';
        let displayValue = value;

        if (value.startsWith('=')) {
            formula = value.substring(1);
            // محاسبه فرمول (در سرور انجام می‌شود)
            saveCell(currentCell, '', formula);
        } else {
            // مقدار عادی
            cellData[currentCell] = {
                value: value,
                formula: '',
                data_type: detectDataType(value),
            };
            saveCell(currentCell, value, '');
        }

        updateCellInfo(cellData[currentCell] || {});
    }

    // ===== Save Cell to Server =====
    function saveCell(cellId, value, formula) {
        const data = {
            cell_id: cellId,
            value: value,
            formula: formula,
        };

        // پیدا کردن worksheetId فعلی
        const worksheetId = currentWorksheet ? currentWorksheet.id : null;
        if (!worksheetId) return;

        fetch('{{ url("admin/excel/worksheets") }}/' + worksheetId + '/cell', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // به‌روزرسانی سلول در جدول
                renderTable();
                document.getElementById('cellInfo').textContent = 'ذخیره شد ✓';
            } else {
                alert('خطا در ذخیره سلول: ' + (data.error || 'خطای ناشناخته'));
            }
        })
        .catch(error => {
            console.error('Error saving cell:', error);
            alert('خطا در ارتباط با سرور');
        });
    }

    // ===== Save History =====
    function saveHistory() {
        history = history.slice(0, historyIndex + 1);
        history.push(JSON.stringify(cellData));
        historyIndex = history.length - 1;

        // محدود کردن تاریخچه
        if (history.length > 100) {
            history.shift();
            historyIndex--;
        }
    }

    function undoCell() {
        if (historyIndex > 0) {
            historyIndex--;
            cellData = JSON.parse(history[historyIndex]);
            renderTable();
        }
    }

    function redoCell() {
        if (historyIndex < history.length - 1) {
            historyIndex++;
            cellData = JSON.parse(history[historyIndex]);
            renderTable();
        }
    }

    // ===== Save Workbook =====
    function saveWorkbook() {
        alert('تمامی تغییرات به طور خودکار ذخیره می‌شوند.');
    }

    // ===== Detect Data Type =====
    function detectDataType(value) {
        if (value === '' || value === null || value === undefined) return 'text';
        if (!isNaN(value) && value.trim() !== '') return 'number';
        if (new Date(value).toString() !== 'Invalid Date') return 'date';
        return 'text';
    }

    // ===== Update Cell Info =====
    function updateCellInfo(cell) {
        const info = document.getElementById('cellInfo');
        if (!cell || (!cell.value && !cell.formula)) {
            info.textContent = 'خالی';
            return;
        }
        if (cell.formula) {
            info.textContent = 'فرمول: ' + cell.formula;
        } else {
            info.textContent = 'مقدار: ' + cell.value;
        }
    }
</script>
@endpush
