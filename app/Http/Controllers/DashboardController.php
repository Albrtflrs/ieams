<?php

namespace App\Http\Controllers;

use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use App\Models\MiscellaneousTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ---- Date range (default: current month) ----
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($selectedMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($selectedMonth)->endOfMonth();

        // ---- 1. This Month ----
        $incomeThisMonth = (float) IncomeTransaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('is_miscellaneous', false)
            ->sum('amount_paid');

        $directCostsThisMonth = (float) ExpenseTransaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('is_miscellaneous', false)
            ->sum('amount');

        $grossProfitThisMonth = $incomeThisMonth - $directCostsThisMonth;

        $operatingExpensesThisMonth = (float) MiscellaneousTransaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('type', 'expense')
            ->sum('amount');

        $operatingProfitThisMonth = $grossProfitThisMonth - $operatingExpensesThisMonth;
        $netProfitThisMonth = $operatingProfitThisMonth;

        // ---- 2. Last 12 Months (rolling) ----
        $start12Months = Carbon::parse($selectedMonth)->subMonths(11)->startOfMonth();
        $end12Months = $endOfMonth;

        $incomeLast12 = (float) IncomeTransaction::whereBetween('created_at', [$start12Months, $end12Months])
            ->where('is_miscellaneous', false)
            ->sum('amount_paid');

        $directCostsLast12 = (float) ExpenseTransaction::whereBetween('created_at', [$start12Months, $end12Months])
            ->where('is_miscellaneous', false)
            ->sum('amount');

        $grossProfitLast12 = $incomeLast12 - $directCostsLast12;

        $operatingExpensesLast12 = (float) MiscellaneousTransaction::whereBetween('created_at', [$start12Months, $end12Months])
            ->where('type', 'expense')
            ->sum('amount');

        $operatingProfitLast12 = $grossProfitLast12 - $operatingExpensesLast12;
        $netProfitLast12 = $operatingProfitLast12;

        // ---- 3. Monthly breakdowns for charts (Income vs Expenses) ----
        $months = collect(range(0, 11))->map(fn($i) => Carbon::parse($selectedMonth)->subMonths(11 - $i)->format('M Y'))->values();

        $incomeByMonth = [];
        $expenseByMonth = [];

        foreach ($months as $index => $monthLabel) {
            $monthStart = Carbon::parse($monthLabel)->startOfMonth();
            $monthEnd = Carbon::parse($monthLabel)->endOfMonth();

            $inc = (float) IncomeTransaction::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('is_miscellaneous', false)
                ->sum('amount_paid');

            $exp = (float) ExpenseTransaction::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('is_miscellaneous', false)
                ->sum('amount');

            $incomeByMonth[] = $inc;
            $expenseByMonth[] = $exp;
        }

        // ---- 4. Expense categories (for bar chart) ----
        $expenseCategories = ExpenseTransaction::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('created_at', [$start12Months, $end12Months])
            ->where('is_miscellaneous', false)
            ->groupBy('category')
            ->get()
            ->map(fn($item) => ['name' => $item->category, 'amount' => (float) $item->total])
            ->toArray();

        // ---- 5. Accounting Categories (BROADER BUCKETS - COMPLETE MAPPING) ----
        $mapping = [
            'MONTHLY FIX BILLS'               => 'Fixed Bills',
            'Utilities'                       => 'Fixed Bills',
            'Rent'                            => 'Fixed Bills',
            'Rental'                          => 'Fixed Bills',
            'Internet'                        => 'Fixed Bills',
            'Communication'                   => 'Fixed Bills',
            'Light & Water'                   => 'Fixed Bills',
            'Electricity'                     => 'Fixed Bills',
            'Water'                           => 'Fixed Bills',
            'Insurance'                       => 'Fixed Bills',
            'Insurance and Bond Expenses'     => 'Fixed Bills',
            'SALARY'                          => 'Salaries and Wages',
            'Salary'                          => 'Salaries and Wages',
            'Salaries'                        => 'Salaries and Wages',
            'DAILY EXPENSES'                  => 'Operating Expenses',
            'GAS/MAINTENANCE'                 => 'Operating Expenses',
            'Gas & Oil'                       => 'Operating Expenses',
            'Gas'                             => 'Operating Expenses',
            'Fuel'                            => 'Operating Expenses',
            'DELIVERY (parcel receiving)'     => 'Operating Expenses',
            'Delivery'                        => 'Operating Expenses',
            'Freight and Hauling'             => 'Operating Expenses',
            'Office Supplies'                 => 'Operating Expenses',
            'Supplies'                        => 'Operating Expenses',
            'Maintenance'                     => 'Operating Expenses',
            'Repairs and Maintenance'         => 'Operating Expenses',
            'Transportation'                  => 'Operating Expenses',
            'Travel and Transpo'              => 'Operating Expenses',
            'Travel'                          => 'Operating Expenses',
            'Entertainment'                   => 'Operating Expenses',
            'Entertainment, Amusement and Recreation' => 'Operating Expenses',
            'Ads & Promotion'                 => 'Operating Expenses',
            'Marketing'                       => 'Operating Expenses',
            'Charitable Contribution'         => 'Operating Expenses',
            'Depreciation'                    => 'Operating Expenses',
            'Miscellaneous Expense'           => 'Operating Expenses',
            'LOAN PAYMENT'                    => 'Loan Payments',
            'Loan'                            => 'Loan Payments',
            'Loan Payment'                    => 'Loan Payments',
            'CASH RECEIVED'                   => 'Cash Received',
            'Taxes'                           => 'Taxes',
            'Fees and Charges'                => 'Taxes',
        ];

        $accountingTotals = [];
        foreach ($expenseCategories as $detail) {
            $cat = $detail['name'];
            $amount = $detail['amount'];
            $accountingCat = $mapping[$cat] ?? 'Other Expenses';
            $accountingTotals[$accountingCat] = ($accountingTotals[$accountingCat] ?? 0) + $amount;
        }

        $accountingCategories = collect($accountingTotals)
            ->map(fn($amount, $name) => ['name' => $name, 'amount' => $amount])
            ->sortByDesc('amount')
            ->values()
            ->toArray();

        // ---- 6. Recent transactions ----
        $recentTransactions = IncomeTransaction::with('client')
            ->select('id', 'particulars', 'client_id', 'amount_paid as amount', DB::raw("'income' as type"), 'created_at')
            ->union(
                ExpenseTransaction::with('supplier')
                    ->select('id', 'description as particulars', 'supplier_id as client_id', 'amount', DB::raw("'expense' as type"), 'created_at')
            )
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'date' => $item->created_at->format('Y-m-d'),
                'particulars' => $item->particulars,
                'client_name' => $item->client?->name ?? $item->supplier?->name ?? '',
                'amount' => (float) $item->amount,
                'type' => $item->type,
            ])
            ->toArray();

        // ---- 7. Real Receivables (unpaid balance) ----
        $receivablesThisMonth = (float) IncomeTransaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['Unpaid', 'Cash On Hold'])
            ->sum(DB::raw('gross_price - amount_paid'));

        $receivablesLast12 = (float) IncomeTransaction::whereBetween('created_at', [$start12Months, $end12Months])
            ->whereIn('status', ['Unpaid', 'Cash On Hold'])
            ->sum(DB::raw('gross_price - amount_paid'));

        // ---- Prepare props ----
        $props = [
            // For charts
            'months' => $months,
            'income_by_month' => array_map('floatval', $incomeByMonth),
            'expense_by_month' => array_map('floatval', $expenseByMonth),
            'expense_categories' => $expenseCategories,
            'accounting_categories' => $accountingCategories,
            'recent_transactions' => $recentTransactions,

            // For cards (This Month)
            'gross_profit_this_month' => (float) $grossProfitThisMonth,
            'direct_costs_this_month' => (float) $directCostsThisMonth,
            'revenue_this_month' => (float) $incomeThisMonth,
            'net_profit_this_month' => (float) $netProfitThisMonth,
            'operating_expenses_this_month' => (float) $operatingExpensesThisMonth,
            'operating_profit_this_month' => (float) $operatingProfitThisMonth,
            'receivables_this_month' => $receivablesThisMonth,

            // For cards (Last 12 Months)
            'gross_profit_last_12m' => (float) $grossProfitLast12,
            'direct_costs_last_12m' => (float) $directCostsLast12,
            'revenue_last_12m' => (float) $incomeLast12,
            'net_profit_last_12m' => (float) $netProfitLast12,
            'operating_expenses_last_12m' => (float) $operatingExpensesLast12,
            'operating_profit_last_12m' => (float) $operatingProfitLast12,
            'receivables_last_12m' => $receivablesLast12,
        ];

        return Inertia::render('Dashboard', $props);
    }
}