<?php

namespace App\Http\Controllers;

use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReceivablesPayablesController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Receivables (Unpaid Income)
        $receivables = IncomeTransaction::where('status', 'Unpaid')
            ->orWhere('status', 'Cash On Hold')
            ->with('client')
            ->latest()
            ->get();

        $totalReceivables = $receivables->sum('amount_paid');

        $receivableAging = [
            '0_30' => $receivables->filter(fn($r) => $r->date_delivered && Carbon::parse($r->date_delivered)->diffInDays($now) <= 30)->sum('amount_paid'),
            '31_60' => $receivables->filter(fn($r) => $r->date_delivered && Carbon::parse($r->date_delivered)->diffInDays($now) > 30 && Carbon::parse($r->date_delivered)->diffInDays($now) <= 60)->sum('amount_paid'),
            '61_90' => $receivables->filter(fn($r) => $r->date_delivered && Carbon::parse($r->date_delivered)->diffInDays($now) > 60 && Carbon::parse($r->date_delivered)->diffInDays($now) <= 90)->sum('amount_paid'),
            '90_plus' => $receivables->filter(fn($r) => $r->date_delivered && Carbon::parse($r->date_delivered)->diffInDays($now) > 90)->sum('amount_paid'),
        ];

        // Payables (Unpaid Expenses)
        $payables = ExpenseTransaction::where('status', 'Unpaid')
            ->orWhere('status', 'Pending')
            ->with('supplier')
            ->latest()
            ->get();

        $totalPayables = $payables->sum('amount');

        $payableAging = [
            '0_30' => $payables->filter(fn($p) => $p->date && Carbon::parse($p->date)->diffInDays($now) <= 30)->sum('amount'),
            '31_60' => $payables->filter(fn($p) => $p->date && Carbon::parse($p->date)->diffInDays($now) > 30 && Carbon::parse($p->date)->diffInDays($now) <= 60)->sum('amount'),
            '61_90' => $payables->filter(fn($p) => $p->date && Carbon::parse($p->date)->diffInDays($now) > 60 && Carbon::parse($p->date)->diffInDays($now) <= 90)->sum('amount'),
            '90_plus' => $payables->filter(fn($p) => $p->date && Carbon::parse($p->date)->diffInDays($now) > 90)->sum('amount'),
        ];

        return Inertia::render('ReceivablesPayables/Index', [
            'receivables' => $receivables,
            'payables' => $payables,
            'totalReceivables' => $totalReceivables,
            'totalPayables' => $totalPayables,
            'netPosition' => $totalReceivables - $totalPayables,
            'receivableAging' => $receivableAging,
            'payableAging' => $payableAging,
        ]);
    }
}