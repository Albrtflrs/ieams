<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CleanTrash;

// ─── Default inspire command ──────────────────────────────────────
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Schedule: Auto‑delete soft‑deleted records after 30 days ──
Schedule::command('clean:trash --days=30')->dailyAt('02:00');

// ─── Alternative: Schedule via closure (no command needed) ─────
// use App\Models\IncomeTransaction;
// Schedule::call(function () {
//     $cutoff = now()->subDays(30);
//     IncomeTransaction::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
// })->dailyAt('02:00');