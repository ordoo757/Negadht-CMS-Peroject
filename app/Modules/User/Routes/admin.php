<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Controllers\Admin\UserController;

Route::get('/', [UserController::class, 'index'])->name('admin.user.index');
Route::get('/create', [UserController::class, 'create'])->name('admin.user.create');
Route::post('/store', [UserController::class, 'store'])->name('admin.user.store');
Route::get('/roles', [UserController::class, 'roles'])->name('admin.user.roles');
Route::get('/permissions', [UserController::class, 'permissions'])->name('admin.user.permissions');
