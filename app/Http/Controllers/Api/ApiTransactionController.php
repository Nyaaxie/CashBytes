<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiTransactionController 
{
    use ApiResponse;

    // GET /api/v1/transactions
    public function index(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return $this->notFound('Wallet not found.');
        }

        $query = DB::table('transactions')
            ->where('wallet_id', $wallet->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_no', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $perPage      = min((int) $request->get('per_page', 15), 100);
        $transactions = $query->orderByDesc('created_at')->paginate($perPage);

        // Summary totals
        $totalDebits  = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('direction', 'debit')
            ->where('status', 'completed')
            ->sum('amount');

        $totalCredits = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('direction', 'credit')
            ->where('status', 'completed')
            ->sum('amount');

        return $this->success([
            'summary' => [
                'total_debits'  => (float) $totalDebits,
                'total_credits' => (float) $totalCredits,
                'balance'       => (float) $wallet->balance,
            ],
            'transactions' => $transactions->map(fn($t) => [
                'id'           => $t->id,
                'type'         => $t->type,
                'direction'    => $t->direction,
                'amount'       => (float) $t->amount,
                'description'  => $t->description,
                'reference_no' => $t->reference_no,
                'status'       => $t->status,
                'date'         => $t->created_at,
            ]),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }
}