<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiWalletController 
{
    use ApiResponse;

    // GET /api/v1/wallet
    public function index(Request $request)
    {
        $user   = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return $this->notFound('Wallet not found.');
        }

        // Last 5 transactions
        $recentTransactions = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'type'         => $t->type,
                'direction'    => $t->direction,
                'amount'       => (float) $t->amount,
                'description'  => $t->description,
                'reference_no' => $t->reference_no,
                'status'       => $t->status,
                'date'         => $t->created_at,
            ]);

        return $this->success([
            'wallet' => [
                'id'       => $wallet->id,
                'balance'  => (float) $wallet->balance,
                'currency' => $wallet->currency,
            ],
            'recent_transactions' => $recentTransactions,
        ]);
    }
}