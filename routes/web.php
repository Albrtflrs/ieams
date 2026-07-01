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
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityLogController;

use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
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

    // ─── Dashboard ──────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Clients ────────────────────────────────────────────────
    Route::resource('clients', ClientController::class);

    // ─── Income ────────────────────────────────────────────────
    // ✅ Trash & restore routes MUST come BEFORE the resource
    Route::get('/income/trash-bin', [IncomeTransactionController::class, 'trash'])->name('income.trash');
    Route::patch('/income/{id}/restore', [IncomeTransactionController::class, 'restore'])->name('income.restore');
    Route::delete('/income/{id}/force-delete', [IncomeTransactionController::class, 'forceDelete'])->name('income.force-delete');

    // Main income resource (this must come after the trash routes)
    Route::resource('income', IncomeTransactionController::class);
    Route::post('/income/{income}/mark-paid', [IncomeTransactionController::class, 'markPaid'])->name('income.mark-paid');

    // ─── Suppliers ──────────────────────────────────────────────
    Route::resource('suppliers', SupplierController::class);

    // ─── Expenses ───────────────────────────────────────────────
    // ✅ Expenses Trash & restore routes (BEFORE resource)
    Route::get('/expenses/trash-bin', [ExpenseTransactionController::class, 'trash'])->name('expenses.trash');
    Route::patch('/expenses/{id}/restore', [ExpenseTransactionController::class, 'restore'])->name('expenses.restore');
    Route::delete('/expenses/{id}/force-delete', [ExpenseTransactionController::class, 'forceDelete'])->name('expenses.force-delete');

    // Main expenses resource
    Route::resource('expenses', ExpenseTransactionController::class);

    // ─── Miscellaneous ──────────────────────────────────────────
    Route::resource('misc', MiscellaneousController::class);
    Route::get('/misc/export/csv', [MiscellaneousController::class, 'exportCsv'])->name('misc.export.csv');

    // ─── Users ──────────────────────────────────────────────────
    Route::resource('users', UserController::class);

    // ─── Retainers ──────────────────────────────────────────────
    // ✅ Retainers Trash & restore routes (BEFORE resource)
    Route::get('/retainers/trash-bin', [RetainerController::class, 'trash'])->name('retainers.trash');
    Route::patch('/retainers/{id}/restore', [RetainerController::class, 'restore'])->name('retainers.restore');
    Route::delete('/retainers/{id}/force-delete', [RetainerController::class, 'forceDelete'])->name('retainers.force-delete');

    // Main retainers resource
    Route::resource('retainers', RetainerController::class);

    // ─── Summary ────────────────────────────────────────────────
    Route::get('/summary', [SummaryController::class, 'index'])->name('summary.index');

    // ─── Receivables & Payables ─────────────────────────────────
    Route::get('/receivables-payables', [ReceivablesPayablesController::class, 'index'])->name('receivables-payables.index');

    // ─── Reports ────────────────────────────────────────────────
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/aging', [ReportController::class, 'aging'])->name('reports.aging');
    Route::get('/reports/aging/export/csv', [ReportController::class, 'exportAgingCsv'])->name('reports.aging.export.csv');
    Route::get('/reports/aging/export/pdf', [ReportController::class, 'exportAgingPdf'])->name('reports.aging.export.pdf');
    Route::get('/reports/payables-aging', [ReportController::class, 'payablesAging'])->name('reports.payables-aging');
    Route::get('/reports/payables-aging/export/csv', [ReportController::class, 'exportPayablesCsv'])->name('reports.payables-aging.export.csv');
    Route::get('/reports/payables-aging/export/pdf', [ReportController::class, 'exportPayablesPdf'])->name('reports.payables-aging.export.pdf');

    // ─── Items ──────────────────────────────────────────────────
    Route::resource('items', ItemController::class)->only(['store', 'destroy', 'edit', 'update']);

    // ─── Settings ───────────────────────────────────────────────
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])->name('settings.logo.upload');
    Route::delete('/settings/logo', [SettingsController::class, 'removeLogo'])->name('settings.logo.remove');
    Route::get('/settings/backup', [SettingsController::class, 'downloadBackup'])->name('settings.backup.download');

    // ─── Quotations ─────────────────────────────────────────────
    // ✅ Quotations Trash & restore routes (BEFORE resource)
    Route::get('/quotations/trash-bin', [QuotationController::class, 'trash'])->name('quotations.trash');
    Route::patch('/quotations/{id}/restore', [QuotationController::class, 'restore'])->name('quotations.restore');
    Route::delete('/quotations/{id}/force-delete', [QuotationController::class, 'forceDelete'])->name('quotations.force-delete');

    // Main quotations resource
    Route::resource('quotations', QuotationController::class);
    Route::post('/quotations/{quotation}/convert-to-income', [QuotationController::class, 'convertToIncome'])->name('quotations.convert-to-income');
    Route::get('/quotations/{quotation}/export/pdf', [QuotationController::class, 'exportPdf'])->name('quotations.export.pdf');
    Route::get('/quotations/{quotation}/export/csv', [QuotationController::class, 'exportCsv'])->name('quotations.export.csv');

    // ─── Profile ─────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['PUT', 'POST'], '/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Audit-log
    Route::get('/admin/audit-log', [ActivityLogController::class, 'index'])->name('admin.audit-log');
});

require __DIR__.'/auth.php';