<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController 
{
    public function index()
    {
        $user   = Auth::user();
        $wallet = $user->wallet;

        // Guard: auto-create wallet if missing (e.g. old accounts before fix)
        if (!$wallet) {
            $wallet = \App\Models\Wallet::create([
                'user_id'  => $user->id,
                'balance'  => 0.00,
                'currency' => 'PHP',
            ]);
        }

        // Recent 5 transactions
        $recentTransactions = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Active savings goals (max 3 for dashboard)
        $savingsGoals = DB::table('saving_goals')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        // Monthly spending (current month)
        $monthlySpending = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('direction', 'debit')
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Total completed transactions
        $totalTransactions = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->count();

        return view('dashboard.index', compact(
            'user',
            'wallet',
            'recentTransactions',
            'savingsGoals',
            'monthlySpending',
            'totalTransactions'
        ));
    }
}