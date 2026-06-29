<?php

namespace App\Http\Controllers;

use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReceivablesPayablesController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();

        // ─── Filters ──────────────────────────────
        $receivableSearch = $request->input('receivable_search');
        $receivableStatus = $request->input('receivable_status');
        $receivableDateFrom = $request->input('receivable_date_from');
        $receivableDateTo = $request->input('receivable_date_to');

        $payableSearch = $request->input('payable_search');
        $payableStatus = $request->input('payable_status');
        $payableDateFrom = $request->input('payable_date_from');
        $payableDateTo = $request->input('payable_date_to');

        // ─── Receivables Query ──────────────────────
        $receivableQuery = IncomeTransaction::with('client')
            ->whereIn('status', ['Unpaid', 'Cash On Hold']);

        if ($receivableSearch) {
            $receivableQuery->whereHas('client', function ($q) use ($receivableSearch) {
                $q->where('name', 'like', "%{$receivableSearch}%");
            });
        }
        if ($receivableStatus) {
            $receivableQuery->where('status', $receivableStatus);
        }
        if ($receivableDateFrom) {
            $receivableQuery->where('date_delivered', '>=', $receivableDateFrom);
        }
        if ($receivableDateTo) {
            $receivableQuery->where('date_delivered', '<=', $receivableDateTo);
        }

        $receivables = $receivableQuery->latest()->paginate(15)->withQueryString();

        // ─── Payables Query ──────────────────────────
        $payableQuery = ExpenseTransaction::with('supplier')
            ->whereIn('status', ['Unpaid', 'Pending']);

        if ($payableSearch) {
            $payableQuery->whereHas('supplier', function ($q) use ($payableSearch) {
                $q->where('name', 'like', "%{$payableSearch}%");
            });
        }
        if ($payableStatus) {
            $payableQuery->where('status', $payableStatus);
        }
        if ($payableDateFrom) {
            $payableQuery->where('date', '>=', $payableDateFrom);
        }
        if ($payableDateTo) {
            $payableQuery->where('date', '<=', $payableDateTo);
        }

        $payables = $payableQuery->latest()->paginate(15)->withQueryString();

        // ─── Totals and Aging (based on filtered data) ──
        $totalReceivables = $receivables->sum('amount_paid');
        $totalPayables = $payables->sum('amount');

        // Aging for receivables (filtered collection)
        $receivableCollection = $receivableQuery->get(); // re-fetch for aging calculation
        $receivableAging = [
            '0_30' => $receivableCollection->filter(fn($r) => $r->date_delivered && Carbon::parse($r->date_delivered)->diffInDays($now) <= 30)->sum('amount_paid'),
            '31_60' => $receivableCollection->filter(fn($r) => $r->date_delivered && Carbon::parse($r->date_delivered)->diffInDays($now) > 30 && Carbon::parse($r->date_delivered)->diffInDays($now) <= 60)->sum('amount_paid'),
            '61_90' => $receivableCollection->filter(fn($r) => $r->date_delivered && Carbon::parse($r->date_delivered)->diffInDays($now) > 60 && Carbon::parse($r->date_delivered)->diffInDays($now) <= 90)->sum('amount_paid'),
            '90_plus' => $receivableCollection->filter(fn($r) => $r->date_delivered && Carbon::parse($r->date_delivered)->diffInDays($now) > 90)->sum('amount_paid'),
        ];

        $payableCollection = $payableQuery->get();
        $payableAging = [
            '0_30' => $payableCollection->filter(fn($p) => $p->date && Carbon::parse($p->date)->diffInDays($now) <= 30)->sum('amount'),
            '31_60' => $payableCollection->filter(fn($p) => $p->date && Carbon::parse($p->date)->diffInDays($now) > 30 && Carbon::parse($p->date)->diffInDays($now) <= 60)->sum('amount'),
            '61_90' => $payableCollection->filter(fn($p) => $p->date && Carbon::parse($p->date)->diffInDays($now) > 60 && Carbon::parse($p->date)->diffInDays($now) <= 90)->sum('amount'),
            '90_plus' => $payableCollection->filter(fn($p) => $p->date && Carbon::parse($p->date)->diffInDays($now) > 90)->sum('amount'),
        ];

        return Inertia::render('ReceivablesPayables/Index', [
            'receivables' => $receivables,          // paginated object
            'payables' => $payables,                // paginated object
            'totalReceivables' => $totalReceivables,
            'totalPayables' => $totalPayables,
            'netPosition' => $totalReceivables - $totalPayables,
            'receivableAging' => $receivableAging,
            'payableAging' => $payableAging,
            'filters' => [
                'receivable_search' => $receivableSearch,
                'receivable_status' => $receivableStatus,
                'receivable_date_from' => $receivableDateFrom,
                'receivable_date_to' => $receivableDateTo,
                'payable_search' => $payableSearch,
                'payable_status' => $payableStatus,
                'payable_date_from' => $payableDateFrom,
                'payable_date_to' => $payableDateTo,
            ],
        ]);
    }
}