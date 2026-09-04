
// ===== Security Tests =====
Route::get('/security-test', [App\Http\Controllers\Admin\SecurityTestController::class, 'index'])->name('security-test.index');
Route::post('/security-test/run', [App\Http\Controllers\Admin\SecurityTestController::class, 'run'])->name('security-test.run');
Route::get('/security-test/results', [App\Http\Controllers\Admin\SecurityTestController::class, 'results'])->name('security-test.results');
Route::get('/security-test/export', [App\Http\Controllers\Admin\SecurityTestController::class, 'export'])->name('security-test.export');
