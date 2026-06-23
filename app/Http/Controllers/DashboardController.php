<?php

namespace App\Http\Controllers;

use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use App\Models\MiscellaneousTransaction;
use App\Models\Client;
use App\Models\Supplier;
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

        // ---- 3. Cash Position ----
        $cashBalance = (float) IncomeTransaction::sum('amount_paid') - (float) ExpenseTransaction::sum('amount');

        $burnRate = (float) ExpenseTransaction::whereBetween('created_at', [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()])
            ->sum('amount') / 3;

        $cashRunaway = $burnRate > 0 ? $cashBalance / $burnRate : 0;

        // ---- 4. Receivables & Payables (status filters) ----
        $totalReceivables = (float) IncomeTransaction::where('status', 'Unpaid')
            ->orWhere('status', 'Cash On Hold')
            ->sum('amount_paid');

        $totalPayables = (float) ExpenseTransaction::where('status', 'Unpaid')
            ->orWhere('status', 'Pending')
            ->sum('amount');

        $netPosition = $totalReceivables - $totalPayables;

        // ---- 5. Receivables Aging (buckets) ----
        $now = Carbon::now();

        $receivableAging = [
            '0_30' => (float) IncomeTransaction::where('status', 'Unpaid')
                ->orWhere('status', 'Cash On Hold')
                ->where('date_delivered', '>=', $now->copy()->subDays(30))
                ->sum('amount_paid'),
            '31_60' => (float) IncomeTransaction::where('status', 'Unpaid')
                ->orWhere('status', 'Cash On Hold')
                ->where('date_delivered', '>=', $now->copy()->subDays(60))
                ->where('date_delivered', '<', $now->copy()->subDays(30))
                ->sum('amount_paid'),
            '61_90' => (float) IncomeTransaction::where('status', 'Unpaid')
                ->orWhere('status', 'Cash On Hold')
                ->where('date_delivered', '>=', $now->copy()->subDays(90))
                ->where('date_delivered', '<', $now->copy()->subDays(60))
                ->sum('amount_paid'),
            '90_plus' => (float) IncomeTransaction::where('status', 'Unpaid')
                ->orWhere('status', 'Cash On Hold')
                ->where('date_delivered', '<', $now->copy()->subDays(90))
                ->sum('amount_paid'),
        ];

        // ---- 6. Payables Aging (buckets) ----
        $payableAging = [
            '0_30' => (float) ExpenseTransaction::where('status', 'Unpaid')
                ->orWhere('status', 'Pending')
                ->where('date', '>=', $now->copy()->subDays(30))
                ->sum('amount'),
            '31_60' => (float) ExpenseTransaction::where('status', 'Unpaid')
                ->orWhere('status', 'Pending')
                ->where('date', '>=', $now->copy()->subDays(60))
                ->where('date', '<', $now->copy()->subDays(30))
                ->sum('amount'),
            '61_90' => (float) ExpenseTransaction::where('status', 'Unpaid')
                ->orWhere('status', 'Pending')
                ->where('date', '>=', $now->copy()->subDays(90))
                ->where('date', '<', $now->copy()->subDays(60))
                ->sum('amount'),
            '90_plus' => (float) ExpenseTransaction::where('status', 'Unpaid')
                ->orWhere('status', 'Pending')
                ->where('date', '<', $now->copy()->subDays(90))
                ->sum('amount'),
        ];

        // ---- 7. Monthly breakdowns for charts ----
        $months = collect(range(0, 11))->map(fn($i) => Carbon::parse($selectedMonth)->subMonths(11 - $i)->format('M Y'))->values();

        $incomeByMonth = [];
        $expenseByMonth = [];
        $revenueByMonth = [];
        $operatingExpensesByMonth = [];
        $marginByMonth = [];
        $grossMarginByMonth = [];
        $operatingExpensesRatioByMonth = [];
        $cashBalanceByMonth = [];

        foreach ($months as $index => $monthLabel) {
            $monthStart = Carbon::parse($monthLabel)->startOfMonth();
            $monthEnd = Carbon::parse($monthLabel)->endOfMonth();

            $inc = (float) IncomeTransaction::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('is_miscellaneous', false)
                ->sum('amount_paid');

            $exp = (float) ExpenseTransaction::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('is_miscellaneous', false)
                ->sum('amount');

            $opExp = (float) MiscellaneousTransaction::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('type', 'expense')
                ->sum('amount');

            $rev = $inc;
            $margin = $inc > 0 ? (($inc - $exp) / $inc) * 100 : 0;
            $grossMargin = $inc > 0 ? (($inc - $exp) / $inc) * 100 : 0;
            $opExpRatio = $rev > 0 ? ($opExp / $rev) * 100 : 0;

            $cashBalanceMonth = (float) IncomeTransaction::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount_paid')
                - (float) ExpenseTransaction::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');

            $incomeByMonth[] = $inc;
            $expenseByMonth[] = $exp;
            $revenueByMonth[] = $rev;
            $operatingExpensesByMonth[] = $opExp;
            $marginByMonth[] = round($margin, 2);
            $grossMarginByMonth[] = round($grossMargin, 2);
            $operatingExpensesRatioByMonth[] = round($opExpRatio, 2);
            $cashBalanceByMonth[] = $cashBalanceMonth;
        }

        // ---- 8. Expense categories & vendors (for pie/bar charts) ----
        $expenseCategories = ExpenseTransaction::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('created_at', [$start12Months, $end12Months])
            ->groupBy('category')
            ->get()
            ->map(fn($item) => ['name' => $item->category, 'amount' => (float) $item->total])
            ->toArray();

        $expenseVendors = ExpenseTransaction::with('supplier')
            ->select('supplier_id', DB::raw('SUM(amount) as total'))
            ->whereNotNull('supplier_id')
            ->whereBetween('created_at', [$start12Months, $end12Months])
            ->groupBy('supplier_id')
            ->get()
            ->map(fn($item) => ['name' => $item->supplier?->name ?? 'Unknown', 'amount' => (float) $item->total])
            ->toArray();

        // ---- 9. Recent transactions ----
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

        // ---- Prepare props ----
        $props = [
            'months' => $months,
            'income_by_month' => array_map('floatval', $incomeByMonth),
            'expense_by_month' => array_map('floatval', $expenseByMonth),
            'revenue_this_month' => (float) $incomeThisMonth,
            'direct_costs_this_month' => (float) $directCostsThisMonth,
            'gross_profit_this_month' => (float) $grossProfitThisMonth,
            'operating_expenses_this_month' => (float) $operatingExpensesThisMonth,
            'operating_profit_this_month' => (float) $operatingProfitThisMonth,
            'net_profit_this_month' => (float) $netProfitThisMonth,
            'revenue_last_12m' => (float) $incomeLast12,
            'direct_costs_last_12m' => (float) $directCostsLast12,
            'gross_profit_last_12m' => (float) $grossProfitLast12,
            'operating_expenses_last_12m' => (float) $operatingExpensesLast12,
            'operating_profit_last_12m' => (float) $operatingProfitLast12,
            'net_profit_last_12m' => (float) $netProfitLast12,
            'cash_balance' => (float) $cashBalance,
            'burn_rate' => (float) $burnRate,
            'cash_runaway' => (float) $cashRunaway,
            'revenue_by_month' => array_map('floatval', $revenueByMonth),
            'operating_expenses_by_month' => array_map('floatval', $operatingExpensesByMonth),
            'margin_by_month' => array_map('floatval', $marginByMonth),
            'gross_margin_by_month' => array_map('floatval', $grossMarginByMonth),
            'operating_expenses_ratio_by_month' => array_map('floatval', $operatingExpensesRatioByMonth),
            'expense_categories' => $expenseCategories,
            'expense_vendors' => $expenseVendors,
            'recent_transactions' => $recentTransactions,
            'cash_balance_by_month' => array_map('floatval', $cashBalanceByMonth),
            // ---- Receivables & Payables ----
            'total_receivables' => $totalReceivables,
            'total_payables' => $totalPayables,
            'net_position' => $netPosition,
            'receivable_aging' => $receivableAging,
            'payable_aging' => $payableAging,
        ];

        return Inertia::render('Dashboard', $props);
    }
}