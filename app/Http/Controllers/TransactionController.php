<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController 
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $wallet = $user->wallet;

        $query = DB::table('transactions')
            ->where('wallet_id', $wallet->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
                  ->orWhere('description',  'like', '%' . $request->search . '%');
            });
        }

        $transactions = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Resolve readable labels for each transaction
        $transactions->getCollection()->transform(function ($txn) use ($wallet) {
            $txn->label    = $this->resolveLabel($txn, $wallet->id);
            $txn->sublabel = $this->resolveSublabel($txn);
            return $txn;
        });

        $totalDebits = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('direction', 'debit')
            ->where('status', 'completed')
            ->sum('amount');

        $totalCredits = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('direction', 'credit')
            ->where('status', 'completed')
            ->sum('amount');

        return view('transactions.index', compact(
            'transactions', 'wallet', 'totalDebits', 'totalCredits'
        ));
    }

    // ── Main label shown in the Description column ───────────────
    private function resolveLabel($txn, $walletId): string
    {
        switch ($txn->type) {

            case 'transfer':
                if ($txn->direction === 'credit') {
                    // Find who sent it
                    $transfer = DB::table('funds_transfers')
                        ->where('id', $txn->transactable_id)
                        ->first();
                    if ($transfer) {
                        $sender = DB::table('wallets')
                            ->join('users', 'wallets.user_id', '=', 'users.id')
                            ->where('wallets.id', $transfer->sender_wallet_id)
                            ->value('users.name');
                        return 'Received from ' . ($sender ?? 'Unknown');
                    }
                    return 'Money Received';
                } else {
                    // Find who received it
                    $transfer = DB::table('funds_transfers')
                        ->where('id', $txn->transactable_id)
                        ->first();
                    if ($transfer) {
                        $receiver = DB::table('wallets')
                            ->join('users', 'wallets.user_id', '=', 'users.id')
                            ->where('wallets.id', $transfer->receiver_wallet_id)
                            ->value('users.name');
                        return 'Sent to ' . ($receiver ?? 'Unknown');
                    }
                    return 'Money Sent';
                }

            case 'bank_transfer':
                $transfer = DB::table('funds_transfers')
                    ->where('id', $txn->transactable_id)
                    ->first();
                if ($transfer && $transfer->bank_account_id) {
                    $bank = DB::table('bank_accounts')
                        ->where('id', $transfer->bank_account_id)
                        ->first();
                    if ($bank) {
                        return 'Bank Transfer to ' . $bank->account_name;
                    }
                }
                return 'Bank Transfer';

            case 'load':
                $load = DB::table('load_purchases')
                    ->where('id', $txn->transactable_id)
                    ->first();
                if ($load) {
                    return $load->network . ' Load — ' . $load->mobile_number;
                }
                return 'Load Purchase';

            case 'bill_payment':
                $bill = DB::table('bills_payments')
                    ->join('billers', 'bills_payments.biller_id', '=', 'billers.id')
                    ->where('bills_payments.id', $txn->transactable_id)
                    ->select('billers.name', 'bills_payments.account_number')
                    ->first();
                if ($bill) {
                    return 'Bill Payment — ' . $bill->name;
                }
                return 'Bill Payment';

            case 'save':
                if ($txn->direction === 'credit') {
                    // Could be a withdrawal or refund
                    if (str_contains($txn->description ?? '', 'Refund')) {
                        return 'Savings Refund';
                    }
                    return 'Savings Withdrawal';
                }
                // Find goal name
                $allocation = DB::table('savings_allocations')
                    ->where('transaction_id', $txn->id)
                    ->first();
                if ($allocation) {
                    $goal = DB::table('saving_goals')
                        ->where('id', $allocation->savings_goal_id)
                        ->first();
                    if ($goal) {
                        return 'Saved to "' . $goal->name . '"';
                    }
                }
                return 'Savings Deposit';

            default:
                return ucfirst(str_replace('_', ' ', $txn->type));
        }
    }

    // ── Sublabel shown as secondary line ─────────────────────────
    private function resolveSublabel($txn): string
    {
        switch ($txn->type) {

            case 'load':
                $load = DB::table('load_purchases')
                    ->where('id', $txn->transactable_id)
                    ->first();
                return $load ? $load->promo_code : '';

            case 'bill_payment':
                $bill = DB::table('bills_payments')
                    ->where('id', $txn->transactable_id)
                    ->first();
                return $bill ? 'Acct: ' . $bill->account_number : '';

            case 'bank_transfer':
                $transfer = DB::table('funds_transfers')
                    ->where('id', $txn->transactable_id)
                    ->first();
                if ($transfer && $transfer->bank_account_id) {
                    $bank = DB::table('bank_accounts')
                        ->where('id', $transfer->bank_account_id)
                        ->first();
                    return $bank ? $bank->bank_name . ' — ' . $bank->account_number : '';
                }
                return '';

            case 'save':
                if ($txn->direction === 'debit') {
                    $allocation = DB::table('savings_allocations')
                        ->where('transaction_id', $txn->id)
                        ->first();
                    if ($allocation) {
                        $goal = DB::table('saving_goals')
                            ->where('id', $allocation->savings_goal_id)
                            ->first();
                        if ($goal) {
                            $progress = $goal->target_amount > 0
                                ? round(($goal->current_amount / $goal->target_amount) * 100)
                                : 0;
                            return $goal->category . ' — ' . $progress . '% of goal';
                        }
                    }
                }
                return '';

            default:
                return '';
        }
    }
}