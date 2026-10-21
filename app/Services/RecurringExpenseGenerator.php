<?php

namespace App\Services;

use App\Models\ExpenseTransaction;
use App\Models\RecurringExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringExpenseGenerator
{
    public function generateDue(?Carbon $asOf = null): int
    {
        $asOf ??= Carbon::today();
        $generated = 0;

        RecurringExpense::query()
            ->where('is_active', true)
            ->whereDate('next_due_date', '<=', $asOf->toDateString())
            ->select('id')
            ->chunkById(100, function ($rows) use ($asOf, &$generated) {
                foreach ($rows as $row) {
                    $generated += DB::transaction(function () use ($row, $asOf) {
                        $template = RecurringExpense::query()
                            ->whereKey($row->id)
                            ->where('is_active', true)
                            ->lockForUpdate()
                            ->first();

                        if (!$template) {
                            return 0;
                        }

                        $nextDue = $template->next_due_date->copy();
                        $count = 0;

                        while ($nextDue->lte($asOf)) {
                            $expense = ExpenseTransaction::firstOrCreate(
                                [
                                    'recurring_expense_id' => $template->id,
                                    'recurring_period' => $nextDue->toDateString(),
                                ],
                                [
                                    'supplier_id' => $template->supplier_id,
                                    'date' => $nextDue->toDateString(),
                                    'amount' => $template->amount,
                                    'category' => $template->category,
                                    'subcategory' => $template->subcategory,
                                    'description' => $template->description,
                                    'payment_method' => $template->payment_method,
                                    'status' => 'Unpaid',
                                    'created_by' => $template->created_by,
                                ]
                            );
                            if ($expense->wasRecentlyCreated) {
                                $count++;
                            }

                            $month = $nextDue->copy()->startOfMonth()->addMonthNoOverflow();
                            $day = min($template->billing_day, $month->daysInMonth);
                            $nextDue = $month->day($day);
                        }

                        $template->update(['next_due_date' => $nextDue->toDateString()]);

                        return $count;
                    });
                }
            });

        return $generated;
    }
}
