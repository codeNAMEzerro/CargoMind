<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// ============================================
// Guest Routes
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ============================================
// Authenticated Routes
// ============================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Items (All authenticated users)
    Route::get('/inventory', [ItemController::class, 'inventory'])->name('inventory.index');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::resource('items', ItemController::class)->except(['show']);

    // Transactions / POS (Karyawan + Manager + Master)
    Route::get('/pos', [TransactionController::class, 'create'])->name('pos');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::get('/transactions/{transaction}/pdf', [TransactionController::class, 'downloadPdf'])->name('transactions.pdf');

    // Reports (Manager + Master)
    Route::middleware('role:master,manager')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/export', [ReportController::class, 'exportExcel'])->name('reports.export');
    });

    // Settings (Master only)
    Route::middleware('role:master')->group(function () {
        Route::post('/toggle-god-mode', [TransactionController::class, 'toggleGodMode'])->name('master.toggle-god-mode');
        Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/users', [SettingController::class, 'createUser'])->name('settings.users.create');
        Route::delete('/settings/users/{user}', [SettingController::class, 'deleteUser'])->name('settings.users.delete');
    });
});
