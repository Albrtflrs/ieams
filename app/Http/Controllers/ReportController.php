<?php

namespace App\Http\Controllers;

use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use App\Models\MiscellaneousTransaction;
use App\Models\Client;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // ---- Main Report (Index) ----
    public function index(Request $request)
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $incomeCategories = IncomeTransaction::select('category')->distinct()->pluck('category')->toArray();
        $expenseCategories = ExpenseTransaction::select('category')->distinct()->pluck('category')->toArray();

        $data = $this->getReportData($request);

        return Inertia::render('Reports/Index', array_merge($data, [
            'filterOptions' => [
                'clients' => $clients,
                'suppliers' => $suppliers,
                'incomeCategories' => $incomeCategories,
                'expenseCategories' => $expenseCategories,
            ],
        ]));
    }

    public function exportCsv(Request $request)
    {
        $data = $this->getReportData($request);
        $filename = 'report_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            // Summary
            fputcsv($handle, ['Category', 'Income', 'Expenses', 'Net']);
            fputcsv($handle, ['Total', $data['summary']['total_income'], $data['summary']['total_expenses'], $data['summary']['net_profit']]);
            fputcsv($handle, []);
            // Income by Category
            fputcsv($handle, ['Income by Category']);
            fputcsv($handle, ['Category', 'Amount']);
            foreach ($data['income_by_category'] as $row) fputcsv($handle, [$row['name'], $row['value']]);
            fputcsv($handle, []);
            // Expense by Category
            fputcsv($handle, ['Expense by Category']);
            fputcsv($handle, ['Category', 'Amount']);
            foreach ($data['expense_by_category'] as $row) fputcsv($handle, [$row['name'], $row['value']]);
            fputcsv($handle, []);
            // Monthly Trend
            fputcsv($handle, ['Monthly Trend']);
            fputcsv($handle, ['Month', 'Income', 'Expenses']);
            foreach ($data['months'] as $i => $month) {
                fputcsv($handle, [$month, $data['income_trend'][$i], $data['expense_trend'][$i]]);
            }
            fputcsv($handle, []);
            // Top Clients
            fputcsv($handle, ['Top Clients']);
            fputcsv($handle, ['Client', 'Revenue']);
            foreach ($data['top_clients'] as $c) fputcsv($handle, [$c['name'], $c['total']]);
            fputcsv($handle, []);
            // Top Suppliers
            fputcsv($handle, ['Top Suppliers']);
            fputcsv($handle, ['Supplier', 'Expenses']);
            foreach ($data['top_suppliers'] as $s) fputcsv($handle, [$s['name'], $s['total']]);
            fputcsv($handle, []);
            // Detailed Transactions
            fputcsv($handle, ['Detailed Transactions']);
            fputcsv($handle, ['Date', 'Client / Supplier', 'Particulars', 'Amount', 'Type']);
            foreach ($data['transactions'] as $tx) {
                fputcsv($handle, [$tx['date'], $tx['client'] ?? '', $tx['particulars'] ?? '', $tx['amount'], $tx['type']]);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $pdf = Pdf::loadView('reports.pdf', ['data' => $data]);
        return $pdf->download('report_' . date('Y-m-d') . '.pdf');
    }

    // ---- Private helper for main report data ----
    private function getReportData(Request $request): array
    {
        $period = $request->input('period', 'this_month');
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $clientId = $request->input('client_id');
        $supplierId = $request->input('supplier_id');
        $incomeCategory = $request->input('income_category');
        $expenseCategory = $request->input('expense_category');
        $dateFrom = $request->input('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : null;
        $dateTo = $request->input('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : null;

        $startDate = null;
        $endDate = null;

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

        $incomeQuery = IncomeTransaction::where('is_miscellaneous', false)
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->when($incomeCategory, fn($q) => $q->where('category', $incomeCategory));
        $expenseQuery = ExpenseTransaction::where('is_miscellaneous', false)
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->when($expenseCategory, fn($q) => $q->where('category', $expenseCategory));
        $miscIncomeQuery = MiscellaneousTransaction::where('type', 'income');
        $miscExpenseQuery = MiscellaneousTransaction::where('type', 'expense');

        if ($startDate && $endDate) {
            $incomeQuery->whereBetween('created_at', [$startDate, $endDate]);
            $expenseQuery->whereBetween('created_at', [$startDate, $endDate]);
            $miscIncomeQuery->whereBetween('created_at', [$startDate, $endDate]);
            $miscExpenseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalIncome = $incomeQuery->sum('amount_paid') + $miscIncomeQuery->sum('amount');
        $totalExpenses = $expenseQuery->sum('amount') + $miscExpenseQuery->sum('amount');
        $netProfit = $totalIncome - $totalExpenses;

        $incomeByCategory = $incomeQuery
            ->select('category', DB::raw('SUM(amount_paid) as total'))
            ->groupBy('category')
            ->get()
            ->map(fn($item) => ['name' => $item->category, 'value' => $item->total])
            ->toArray();

        $expenseByCategory = $expenseQuery
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->map(fn($item) => ['name' => $item->category, 'value' => $item->total])
            ->toArray();

        $trendMonths = [];
        $incomeTrend = [];
        $expenseTrend = [];

        if ($startDate && $endDate) {
            $monthStart = $startDate->copy()->startOfMonth();
            $monthEnd = $endDate->copy()->endOfMonth();
            $current = $monthStart->copy();
            while ($current <= $monthEnd) {
                $trendMonths[] = $current->format('M Y');
                $monthStartFilter = $current->startOfMonth();
                $monthEndFilter = $current->endOfMonth();

                $inc = IncomeTransaction::where('is_miscellaneous', false)
                    ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                    ->when($incomeCategory, fn($q) => $q->where('category', $incomeCategory))
                    ->whereBetween('created_at', [$monthStartFilter, $monthEndFilter])->sum('amount_paid');
                $inc += MiscellaneousTransaction::where('type', 'income')
                    ->whereBetween('created_at', [$monthStartFilter, $monthEndFilter])->sum('amount');

                $exp = ExpenseTransaction::where('is_miscellaneous', false)
                    ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
                    ->when($expenseCategory, fn($q) => $q->where('category', $expenseCategory))
                    ->whereBetween('created_at', [$monthStartFilter, $monthEndFilter])->sum('amount');
                $exp += MiscellaneousTransaction::where('type', 'expense')
                    ->whereBetween('created_at', [$monthStartFilter, $monthEndFilter])->sum('amount');

                $incomeTrend[] = $inc;
                $expenseTrend[] = $exp;
                $current->addMonth();
            }
        } else {
            $trendMonths = collect(range(0, 11))->map(fn($i) => Carbon::now()->subMonths(11 - $i)->format('M Y'))->values();
            foreach ($trendMonths as $monthLabel) {
                $monthStart = Carbon::parse($monthLabel)->startOfMonth();
                $monthEnd = Carbon::parse($monthLabel)->endOfMonth();

                $inc = IncomeTransaction::where('is_miscellaneous', false)
                    ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                    ->when($incomeCategory, fn($q) => $q->where('category', $incomeCategory))
                    ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount_paid');
                $inc += MiscellaneousTransaction::where('type', 'income')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');

                $exp = ExpenseTransaction::where('is_miscellaneous', false)
                    ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
                    ->when($expenseCategory, fn($q) => $q->where('category', $expenseCategory))
                    ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
                $exp += MiscellaneousTransaction::where('type', 'expense')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');

                $incomeTrend[] = $inc;
                $expenseTrend[] = $exp;
            }
        }

        $topClientsQuery = Client::withSum(['incomeTransactions' => function($q) use ($startDate, $endDate, $incomeCategory) {
            if ($startDate && $endDate) $q->whereBetween('created_at', [$startDate, $endDate]);
            if ($incomeCategory) $q->where('category', $incomeCategory);
            $q->where('is_miscellaneous', false);
        }], 'amount_paid')
            ->orderBy('income_transactions_sum_amount_paid', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($c) => ['name' => $c->name, 'total' => $c->income_transactions_sum_amount_paid ?? 0])
            ->toArray();

        $topSuppliersQuery = Supplier::withSum(['expenseTransactions' => function($q) use ($startDate, $endDate, $expenseCategory) {
            if ($startDate && $endDate) $q->whereBetween('created_at', [$startDate, $endDate]);
            if ($expenseCategory) $q->where('category', $expenseCategory);
            $q->where('is_miscellaneous', false);
        }], 'amount')
            ->orderBy('expense_transactions_sum_amount', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($s) => ['name' => $s->name, 'total' => $s->expense_transactions_sum_amount ?? 0])
            ->toArray();

        $incomeTx = IncomeTransaction::with('client')
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->when($incomeCategory, fn($q) => $q->where('category', $incomeCategory))
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->where('is_miscellaneous', false)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($item) => [
                'date' => $item->created_at->format('Y-m-d'),
                'client' => $item->client?->name,
                'particulars' => $item->particulars,
                'amount' => $item->amount_paid,
                'type' => 'Income',
            ]);

        $expenseTx = ExpenseTransaction::with('supplier')
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->when($expenseCategory, fn($q) => $q->where('category', $expenseCategory))
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->where('is_miscellaneous', false)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($item) => [
                'date' => $item->created_at->format('Y-m-d'),
                'client' => $item->supplier?->name,
                'particulars' => $item->description,
                'amount' => $item->amount,
                'type' => 'Expense',
            ]);

        $transactions = $incomeTx->concat($expenseTx)->sortByDesc('date')->values()->take(50)->toArray();

        return [
            'period' => $period,
            'month' => $selectedMonth,
            'filters' => [
                'client_id' => $clientId,
                'supplier_id' => $supplierId,
                'income_category' => $incomeCategory,
                'expense_category' => $expenseCategory,
                'date_from' => $dateFrom ? $dateFrom->toDateString() : null,
                'date_to' => $dateTo ? $dateTo->toDateString() : null,
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
            ],
            'income_by_category' => $incomeByCategory,
            'expense_by_category' => $expenseByCategory,
            'months' => $trendMonths,
            'income_trend' => $incomeTrend,
            'expense_trend' => $expenseTrend,
            'top_clients' => $topClientsQuery,
            'top_suppliers' => $topSuppliersQuery,
            'transactions' => $transactions,
        ];
    }

    // ---- Receivables Aging ----
    public function aging(Request $request)
    {
        $this->authorize('viewAny', IncomeTransaction::class);
        $data = $this->getAgingData($request);
        return Inertia::render('Reports/Aging', $data);
    }

    public function exportAgingCsv(Request $request)
    {
        $data = $this->getAgingData($request);
        $filename = 'aging_report_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Client', '0-30 days', '31-60 days', '61-90 days', '90+ days', 'Total Receivable']);
            foreach ($data['agingData'] as $row) {
                fputcsv($handle, [
                    $row['client'],
                    $row['bucket_0_30'],
                    $row['bucket_31_60'],
                    $row['bucket_61_90'],
                    $row['bucket_90_plus'],
                    $row['total'],
                ]);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportAgingPdf(Request $request)
    {
        $data = $this->getAgingData($request);
        $pdf = Pdf::loadView('reports.aging_pdf', ['data' => $data]);
        return $pdf->download('aging_report_' . date('Y-m-d') . '.pdf');
    }

    private function getAgingData(Request $request): array
    {
        $asOf = $request->input('as_of') ? Carbon::parse($request->input('as_of')) : now();

        $clients = Client::with(['incomeTransactions' => function ($q) use ($asOf) {
            $q->where('is_miscellaneous', false)
              ->where('gross_price', '>', DB::raw('amount_paid'))
              ->where(function ($q2) use ($asOf) {
                  $q2->where('date_delivered', '<=', $asOf)
                     ->orWhereNull('date_delivered');
              });
        }])->get();

        $agingData = [];
        foreach ($clients as $client) {
            $buckets = [0, 0, 0, 0];
            $total = 0;
            foreach ($client->incomeTransactions as $tx) {
                $receivable = $tx->gross_price - $tx->amount_paid;
                if ($receivable <= 0) continue;
                $total += $receivable;
                $date = $tx->date_delivered ?: $tx->created_at;
                $days = $asOf->diffInDays($date);
                if ($days <= 30) $buckets[0] += $receivable;
                elseif ($days <= 60) $buckets[1] += $receivable;
                elseif ($days <= 90) $buckets[2] += $receivable;
                else $buckets[3] += $receivable;
            }
            if ($total > 0) {
                $agingData[] = [
                    'client' => $client->name,
                    'bucket_0_30' => $buckets[0],
                    'bucket_31_60' => $buckets[1],
                    'bucket_61_90' => $buckets[2],
                    'bucket_90_plus' => $buckets[3],
                    'total' => $total,
                ];
            }
        }

        usort($agingData, fn($a, $b) => $b['total'] <=> $a['total']);

        if (!empty($agingData)) {
            $agingData[] = [
                'client' => 'TOTAL',
                'bucket_0_30' => array_sum(array_column($agingData, 'bucket_0_30')),
                'bucket_31_60' => array_sum(array_column($agingData, 'bucket_31_60')),
                'bucket_61_90' => array_sum(array_column($agingData, 'bucket_61_90')),
                'bucket_90_plus' => array_sum(array_column($agingData, 'bucket_90_plus')),
                'total' => array_sum(array_column($agingData, 'total')),
            ];
        }

        return [
            'agingData' => $agingData,
            'asOf' => $asOf->toDateString(),
        ];
    }

    // ---- Payables Aging ----
    public function payablesAging(Request $request)
    {
        $this->authorize('viewAny', ExpenseTransaction::class);
        $data = $this->getPayablesAgingData($request);
        return Inertia::render('Reports/PayablesAging', $data);
    }

    public function exportPayablesCsv(Request $request)
    {
        $data = $this->getPayablesAgingData($request);
        $filename = 'payables_aging_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Supplier', '0-30 days', '31-60 days', '61-90 days', '90+ days', 'Total Payable']);
            foreach ($data['agingData'] as $row) {
                fputcsv($handle, [
                    $row['supplier'],
                    $row['bucket_0_30'],
                    $row['bucket_31_60'],
                    $row['bucket_61_90'],
                    $row['bucket_90_plus'],
                    $row['total'],
                ]);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportPayablesPdf(Request $request)
    {
        $data = $this->getPayablesAgingData($request);
        $pdf = Pdf::loadView('reports.payables_aging_pdf', ['data' => $data]);
        return $pdf->download('payables_aging_' . date('Y-m-d') . '.pdf');
    }

    private function getPayablesAgingData(Request $request): array
    {
        $asOf = $request->input('as_of') ? Carbon::parse($request->input('as_of')) : now();

        $suppliers = Supplier::with(['expenseTransactions' => function ($q) use ($asOf) {
            $q->where('is_miscellaneous', false)
              ->where(function ($q2) use ($asOf) {
                  $q2->where('date', '<=', $asOf)
                     ->orWhereNull('date');
              });
        }])->get();

        $agingData = [];
        foreach ($suppliers as $supplier) {
            $buckets = [0, 0, 0, 0];
            $total = 0;
            foreach ($supplier->expenseTransactions as $tx) {
                $payable = $tx->amount;
                if ($payable <= 0) continue;
                $total += $payable;
                $date = $tx->date ?: $tx->created_at;
                $days = $asOf->diffInDays($date);
                if ($days <= 30) $buckets[0] += $payable;
                elseif ($days <= 60) $buckets[1] += $payable;
                elseif ($days <= 90) $buckets[2] += $payable;
                else $buckets[3] += $payable;
            }
            if ($total > 0) {
                $agingData[] = [
                    'supplier' => $supplier->name,
                    'bucket_0_30' => $buckets[0],
                    'bucket_31_60' => $buckets[1],
                    'bucket_61_90' => $buckets[2],
                    'bucket_90_plus' => $buckets[3],
                    'total' => $total,
                ];
            }
        }

        usort($agingData, fn($a, $b) => $b['total'] <=> $a['total']);

        if (!empty($agingData)) {
            $agingData[] = [
                'supplier' => 'TOTAL',
                'bucket_0_30' => array_sum(array_column($agingData, 'bucket_0_30')),
                'bucket_31_60' => array_sum(array_column($agingData, 'bucket_31_60')),
                'bucket_61_90' => array_sum(array_column($agingData, 'bucket_61_90')),
                'bucket_90_plus' => array_sum(array_column($agingData, 'bucket_90_plus')),
                'total' => array_sum(array_column($agingData, 'total')),
            ];
        }

        return [
            'agingData' => $agingData,
            'asOf' => $asOf->toDateString(),
        ];
    }
}