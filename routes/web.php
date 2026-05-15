<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\QueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('queue.register'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/daftar', [QueueController::class, 'index'])->name('queue.register');
Route::post('/daftar', [QueueController::class, 'register'])->name('queue.store');
Route::post('/lacak', [QueueController::class, 'search'])->name('queue.search');
Route::get('/tiket/{queue}', [QueueController::class, 'ticket'])->name('queue.ticket');

Route::get('/display', [MonitorController::class, 'display'])->name('monitor.display');


Route::middleware(['admin.whitelist'])->group(function () {
    Route::get('/monitor', [MonitorController::class, 'monitor'])->name('monitor.monitor');
});

Route::prefix('admin')->name('admin.')->middleware(['admin.whitelist'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/call-next', [AdminController::class, 'callNext'])->name('call-next');
    Route::post('/add-manual', [AdminController::class, 'addManual'])->name('add-manual');
    Route::patch('/queue/{queue}/status', [AdminController::class, 'updateStatus'])->name('update-status');
    Route::post('/queue/{queue}/recall', [AdminController::class, 'recall'])->name('recall');
    Route::post('/queue/{queue}/requeue', [AdminController::class, 'requeue'])->name('requeue');
    Route::get('/export', [AdminController::class, 'export'])->name('export');
    Route::delete('/reset-today', [AdminController::class, 'resetToday'])->name('reset-today');

    Route::patch('services/{service}/toggle-active', [\App\Http\Controllers\Admin\ServiceController::class, 'toggleActive'])->name('services.toggle-active');
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
    
    Route::patch('counters/{counter}/toggle-active', [\App\Http\Controllers\Admin\CounterController::class, 'toggleActive'])->name('counters.toggle-active');
    Route::resource('counters', \App\Http\Controllers\Admin\CounterController::class);
});

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/stats', [QueueController::class, 'stats'])->name('stats');
    Route::get('/waiting', [QueueController::class, 'waitingList'])->name('waiting');
    Route::get('/counters', [MonitorController::class, 'counters'])->name('counters');
    Route::middleware(['admin.whitelist'])->group(function () {
        Route::get('/admin/stats', [AdminController::class, 'statsApi'])->name('admin.stats');
        Route::get('/admin/today', [AdminController::class, 'todayList'])->name('admin.today');
    });
});
