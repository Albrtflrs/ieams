<?php

namespace App\Http\Controllers;

use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use App\Models\MiscellaneousTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        // Get filters
        $period = $request->input('period', 'this_month');
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $dateFrom = $request->input('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : null;
        $dateTo = $request->input('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : null;

        // Determine date range
        if ($dateFrom && $dateTo) {
            $startDate = $dateFrom;
            $endDate = $dateTo;
        } else {
            switch ($period) {
                case 'this_month':
                    $startDate = Carbon::parse($selectedMonth)->startOfMonth();
                    $endDate = Carbon::parse($selectedMonth)->endOfMonth();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
                case 'last_12_months':
                    $startDate = Carbon::now()->subMonths(11)->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'all_time':
                    $startDate = null;
                    $endDate = null;
                    break;
                default:
                    $startDate = Carbon::parse($selectedMonth)->startOfMonth();
                    $endDate = Carbon::parse($selectedMonth)->endOfMonth();
            }
        }

        // ─── Base queries ────────────────────────────────────
        $incomeQuery = IncomeTransaction::where('is_miscellaneous', false);
        $expenseQuery = ExpenseTransaction::where('is_miscellaneous', false);
        $miscIncomeQuery = MiscellaneousTransaction::where('type', 'income');
        $miscExpenseQuery = MiscellaneousTransaction::where('type', 'expense');

        if ($startDate && $endDate) {
            $incomeQuery->whereBetween('created_at', [$startDate, $endDate]);
            $expenseQuery->whereBetween('created_at', [$startDate, $endDate]);
            $miscIncomeQuery->whereBetween('created_at', [$startDate, $endDate]);
            $miscExpenseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // ─── Totals ──────────────────────────────────────────
        $totalRevenue = (float) $incomeQuery->sum('amount_paid') + (float) $miscIncomeQuery->sum('amount');
        $totalExpenses = (float) $expenseQuery->sum('amount') + (float) $miscExpenseQuery->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        // Direct costs (non-miscellaneous expenses)
        $directCosts = (float) ExpenseTransaction::where('is_miscellaneous', false)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount');

        $grossProfit = $totalRevenue - $directCosts;

        // Operating expenses (miscellaneous expenses)
        $operatingExpenses = (float) MiscellaneousTransaction::where('type', 'expense')
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount');

        $operatingProfit = $grossProfit - $operatingExpenses;

        // Cash balance (all-time)
        $cashBalance = (float) IncomeTransaction::sum('amount_paid') - (float) ExpenseTransaction::sum('amount');

        // Receivables (unpaid income)
        $receivables = (float) IncomeTransaction::where('status', 'Unpaid')
            ->orWhere('status', 'Cash On Hold')
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount_paid');

        // ─── Ratios ──────────────────────────────────────────
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $operatingMargin = $totalRevenue > 0 ? ($operatingProfit / $totalRevenue) * 100 : 0;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // ─── Breakdowns for Modal ──────────────────────────
        $incomeByCategory = IncomeTransaction::select('category', DB::raw('SUM(amount_paid) as total'))
            ->where('is_miscellaneous', false)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->groupBy('category')
            ->get()
            ->map(fn($item) => ['name' => $item->category ?? 'Uncategorized', 'value' => (float) $item->total])
            ->toArray();

        $expenseByCategory = ExpenseTransaction::select('category', DB::raw('SUM(amount) as total'))
            ->where('is_miscellaneous', false)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->groupBy('category')
            ->get()
            ->map(fn($item) => ['name' => $item->category ?? 'Uncategorized', 'value' => (float) $item->total])
            ->toArray();

        // ─── Monthly trend ──────────────────────────────────
        $months = collect(range(0, 11))->map(fn($i) => Carbon::now()->subMonths(11 - $i)->format('M Y'))->values();
        $incomeTrend = [];
        $expenseTrend = [];
        foreach ($months as $monthLabel) {
            $monthStart = Carbon::parse($monthLabel)->startOfMonth();
            $monthEnd = Carbon::parse($monthLabel)->endOfMonth();
            $incomeTrend[] = (float) IncomeTransaction::where('is_miscellaneous', false)
                ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount_paid');
            $expenseTrend[] = (float) ExpenseTransaction::where('is_miscellaneous', false)
                ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
        }

        // ─── TOP 5 INCOME CLIENTS (by amount_paid) ──────── 👈 NEW
        $topIncomeClients = IncomeTransaction::with('client')
            ->where('is_miscellaneous', false)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->select('client_id', DB::raw('SUM(amount_paid) as total'))
            ->groupBy('client_id')
            ->orderBy('total', 'desc')
            ->limit(5)   // change to 3 if you prefer
            ->get()
            ->map(fn($item) => [
                'id'    => $item->client_id,
                'name'  => $item->client?->name ?? 'Unknown',
                'total' => (float) $item->total,
            ])
            ->toArray();

        // ─── TOP 5 EXPENSE CLIENTS (by amount) ───────────── 👈 NEW
        $topExpenseClients = ExpenseTransaction::with('supplier')
            ->where('is_miscellaneous', false)
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->select('supplier_id', DB::raw('SUM(amount) as total'))
            ->groupBy('supplier_id')
            ->orderBy('total', 'desc')
            ->limit(5)   // change to 3 if you prefer
            ->get()
            ->map(fn($item) => [
                'id'    => $item->supplier_id,
                'name'  => $item->supplier?->name ?? 'Unknown',
                'total' => (float) $item->total,
            ])
            ->toArray();

        // (Optional) Keep old single top_client for backward compatibility
        $topClient = $topIncomeClients[0] ?? null;

        // ─── Return to Inertia ──────────────────────────────
        return Inertia::render('Summary/Index', [
            'period' => $period,
            'month' => $selectedMonth,
            'date_from' => $dateFrom ? $dateFrom->toDateString() : null,
            'date_to' => $dateTo ? $dateTo->toDateString() : null,
            'metrics' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'direct_costs' => $directCosts,
                'gross_profit' => $grossProfit,
                'operating_expenses' => $operatingExpenses,
                'operating_profit' => $operatingProfit,
                'cash_balance' => $cashBalance,
                'receivables' => $receivables,
                'gross_margin' => $grossMargin,
                'operating_margin' => $operatingMargin,
                'net_margin' => $netMargin,
            ],
            'income_by_category' => $incomeByCategory,
            'expense_by_category' => $expenseByCategory,
            'months' => $months,
            'income_trend' => $incomeTrend,
            'expense_trend' => $expenseTrend,
            // New arrays for the updated view
            'top_income_clients' => $topIncomeClients,   // 👈 NEW
            'top_expense_clients' => $topExpenseClients, // 👈 NEW
            // Old single client (kept for backward compatibility)
            'top_client' => $topClient,
        ]);
    }
}