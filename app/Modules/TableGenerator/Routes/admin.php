<?php

use Illuminate\Support\Facades\Route;
use App\Modules\TableGenerator\Controllers\Admin\TableController;

Route::get('/', [TableController::class, 'index'])->name('admin.table.index');
Route::get('/create', [TableController::class, 'create'])->name('admin.table.create');
Route::post('/store', [TableController::class, 'store'])->name('admin.table.store');
