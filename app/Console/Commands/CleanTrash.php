<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use App\Models\Retainer;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Quotation;
use App\Models\MiscellaneousTransaction;

class CleanTrash extends Command
{
    protected $signature = 'clean:trash {--days=30 : Number of days before permanent deletion}';
    protected $description = 'Permanently delete soft‑deleted records older than X days.';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("Deleting soft‑deleted records older than {$days} days ({$cutoff->toDateTimeString()})");

        $models = [
            IncomeTransaction::class,
            ExpenseTransaction::class,
            Retainer::class,
            Client::class,
            Supplier::class,
            Quotation::class,
            MiscellaneousTransaction::class,
        ];

        $totalDeleted = 0;

        foreach ($models as $model) {
            $deleted = $model::onlyTrashed()
                ->where('deleted_at', '<', $cutoff)
                ->forceDelete();

            $totalDeleted += $deleted;
            $this->line("Deleted {$deleted} records from " . class_basename($model));
        }

        $this->info("Total permanently deleted: {$totalDeleted} records.");
    }
}