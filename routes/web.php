<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\QueueController;
use Illuminate\Support\Facades\Route;

// ============================
// Halaman Utama
// ============================
Route::get('/', fn() => redirect()->route('queue.register'));

// ============================
// Pendaftaran Antrian (Pasien)
// ============================
Route::get('/daftar', [QueueController::class, 'index'])->name('queue.register');
Route::post('/daftar', [QueueController::class, 'register'])->name('queue.store');
Route::get('/tiket/{queue}', [QueueController::class, 'ticket'])->name('queue.ticket');

// ============================
// Monitor & Display Publik
// ============================
Route::get('/display', [MonitorController::class, 'display'])->name('monitor.display');
Route::get('/monitor', [MonitorController::class, 'monitor'])->name('monitor.monitor');

// ============================
// Panel Admin
// ============================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/call-next', [AdminController::class, 'callNext'])->name('call-next');
    Route::post('/add-manual', [AdminController::class, 'addManual'])->name('add-manual');
    Route::patch('/queue/{queue}/status', [AdminController::class, 'updateStatus'])->name('update-status');
    Route::post('/queue/{queue}/recall', [AdminController::class, 'recall'])->name('recall');
    Route::delete('/reset-today', [AdminController::class, 'resetToday'])->name('reset-today');
});


// ============================
// API Endpoints
// ============================
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/stats', [QueueController::class, 'stats'])->name('stats');
    Route::get('/waiting', [QueueController::class, 'waitingList'])->name('waiting');
    Route::get('/counters', [MonitorController::class, 'counters'])->name('counters');
    Route::get('/admin/stats', [AdminController::class, 'statsApi'])->name('admin.stats');
    Route::get('/admin/today', [AdminController::class, 'todayList'])->name('admin.today');
});
