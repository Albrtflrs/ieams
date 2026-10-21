<?php

namespace App\Console\Commands;

use App\Services\RecurringExpenseGenerator;
use Illuminate\Console\Command;

class GenerateRecurringExpenses extends Command
{
    protected $signature = 'expenses:generate-recurring';

    protected $description = 'Create due expense transactions from active monthly bill templates';

    public function handle(RecurringExpenseGenerator $generator): int
    {
        $count = $generator->generateDue();
        $this->info("Generated {$count} recurring expense transaction(s).");

        return self::SUCCESS;
    }
}
