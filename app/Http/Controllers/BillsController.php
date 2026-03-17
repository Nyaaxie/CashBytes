<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillsController 
{
    // Show biller list
    public function index(Request $request)
    {
        $wallet = Auth::user()->wallet;

        $query = DB::table('billers')->where('is_active', true);

        // Search/filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $billers = $query->orderBy('category')->orderBy('name')->get();

        // Group by category for display
        $grouped = $billers->groupBy('category');

        $categories = DB::table('billers')
            ->where('is_active', true)
            ->distinct()
            ->pluck('category');

        return view('bills.index', compact('grouped', 'categories', 'wallet'));
    }

    // Show payment form for a biller
    public function pay($billerId)
    {
        $biller = DB::table('billers')
            ->where('id', $billerId)
            ->where('is_active', true)
            ->first();

        if (!$biller) {
            return redirect()->route('bills.index');
        }

        $wallet = Auth::user()->wallet;

        // Past payments to this biller (for account number autofill)
        $pastPayment = DB::table('bills_payments')
            ->where('wallet_id', $wallet->id)
            ->where('biller_id', $billerId)
            ->orderByDesc('created_at')
            ->first();

        return view('bills.pay', compact('biller', 'wallet', 'pastPayment'));
    }

    // Process bill payment
    public function process(Request $request, $billerId)
    {
        $request->validate([
            'account_number' => ['required', 'string', 'max:100'],
            'amount'         => ['required', 'numeric', 'min:1'],
        ]);

        $biller = DB::table('billers')
            ->where('id', $billerId)
            ->where('is_active', true)
            ->first();

        if (!$biller) {
            return redirect()->route('bills.index');
        }

        $wallet = Auth::user()->wallet;

        if ($wallet->balance < $request->amount) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Insufficient wallet balance.']);
        }

        try {
            DB::statement('CALL pay_bill(?, ?, ?, ?)', [
                $wallet->id,
                $billerId,
                $request->account_number,
                $request->amount,
            ]);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Payment failed: ' . $e->getMessage()]);
        }

        $transaction = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'bill_payment')
            ->orderByDesc('created_at')
            ->first();

        return redirect()->route('bills.receipt', $transaction->id);
    }

    // Receipt
    public function receipt($transactionId)
    {
        $transaction = DB::table('transactions')->where('id', $transactionId)->first();

        if (!$transaction || $transaction->wallet_id !== Auth::user()->wallet->id) {
            return redirect()->route('bills.index');
        }

        // Get bill payment details
        $billDetails = DB::table('bills_payments as bp')
            ->join('billers as b', 'bp.biller_id', '=', 'b.id')
            ->where('bp.id', $transaction->transactable_id)
            ->select('bp.*', 'b.name as biller_name', 'b.category as biller_category')
            ->first();

        $wallet = Auth::user()->wallet;

        return view('bills.receipt', compact('transaction', 'billDetails', 'wallet'));
    }
}