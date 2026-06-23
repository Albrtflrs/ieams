<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\IncomeTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ExpenseTransactionController;
use App\Http\Controllers\MiscellaneousController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RetainerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\ReceivablesPayablesController;
use App\Http\Controllers\QuotationController;

use Inertia\Inertia;

Route::get('/', function () {
    return view('welcome');
});

// Guest routes (login, registration)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Registration routes protected by 'registration' middleware
    Route::middleware('registration')->group(function () {
        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [RegisteredUserController::class, 'store']);
    });
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('clients', ClientController::class);
    Route::resource('income', IncomeTransactionController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('expenses', ExpenseTransactionController::class);
    Route::resource('misc', MiscellaneousController::class);
    Route::resource('users', UserController::class);
    Route::resource('retainers', RetainerController::class);
    Route::get('/summary', [SummaryController::class, 'index'])->name('summary.index');
    Route::get('/receivables-payables', [ReceivablesPayablesController::class, 'index'])->name('receivables-payables.index');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/aging', [ReportController::class, 'aging'])->name('reports.aging');
    Route::get('/reports/aging/export/csv', [ReportController::class, 'exportAgingCsv'])->name('reports.aging.export.csv');
    Route::get('/reports/aging/export/pdf', [ReportController::class, 'exportAgingPdf'])->name('reports.aging.export.pdf');
    Route::get('/reports/payables-aging', [ReportController::class, 'payablesAging'])->name('reports.payables-aging');
    Route::get('/reports/payables-aging/export/csv', [ReportController::class, 'exportPayablesCsv'])->name('reports.payables-aging.export.csv');
    Route::get('/reports/payables-aging/export/pdf', [ReportController::class, 'exportPayablesPdf'])->name('reports.payables-aging.export.pdf');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])->name('settings.logo.upload');
    Route::delete('/settings/logo', [SettingsController::class, 'removeLogo'])->name('settings.logo.remove');
    Route::get('/settings/backup', [SettingsController::class, 'downloadBackup'])->name('settings.backup.download');

    // ─── Quotations (Full CRUD + Convert) ───
    Route::resource('quotations', QuotationController::class);
    Route::post('/quotations/{quotation}/convert-to-income', [QuotationController::class, 'convertToIncome'])->name('quotations.convert-to-income');
});

require __DIR__.'/auth.php';