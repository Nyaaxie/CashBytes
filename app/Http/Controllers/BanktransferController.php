<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankTransferController
{
    // GET /transfer/bank
    public function index()
    {
        $wallet       = Auth::user()->wallet;
        $bankAccounts = DB::table('bank_accounts')
            ->where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('transfer.bank', compact('wallet', 'bankAccounts'));
    }

    // POST /transfer/bank/account
    public function addAccount(Request $request)
    {
        $request->validate([
            'bank_name'      => ['required', 'string', 'max:100'],
            'account_name'   => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_type'   => ['required', 'in:savings,checking'],
        ]);

        $userId  = Auth::id();
        $isFirst = !DB::table('bank_accounts')->where('user_id', $userId)->exists();

        DB::table('bank_accounts')->insert([
            'user_id'        => $userId,
            'bank_name'      => $request->bank_name,
            'account_name'   => $request->account_name,
            'account_number' => $request->account_number,
            'account_type'   => $request->account_type,
            'is_default'     => $isFirst,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'Bank account saved successfully.');
    }

    // PATCH /transfer/bank/account/{id}/default
    public function setDefault($id)
    {
        $userId  = Auth::id();
        $account = DB::table('bank_accounts')->where('id', $id)->where('user_id', $userId)->first();

        if (!$account) return back()->withErrors(['error' => 'Bank account not found.']);

        DB::table('bank_accounts')->where('user_id', $userId)->update(['is_default' => false]);
        DB::table('bank_accounts')->where('id', $id)->update(['is_default' => true]);

        return back()->with('success', 'Default bank account updated.');
    }

    // DELETE /transfer/bank/account/{id}
    public function deleteAccount($id)
    {
        $userId  = Auth::id();
        $account = DB::table('bank_accounts')->where('id', $id)->where('user_id', $userId)->first();

        if (!$account) return back()->withErrors(['error' => 'Bank account not found.']);

        DB::table('bank_accounts')->where('id', $id)->delete();

        if ($account->is_default) {
            $next = DB::table('bank_accounts')->where('user_id', $userId)->first();
            if ($next) DB::table('bank_accounts')->where('id', $next->id)->update(['is_default' => true]);
        }

        return back()->with('success', 'Bank account removed.');
    }

    // POST /transfer/bank/confirm
    public function confirm(Request $request)
    {
        $request->validate([
            'bank_account_id' => ['required', 'integer'],
            'amount'          => ['required', 'numeric', 'min:1'],
            'purpose'         => ['nullable', 'string', 'max:255'],
        ]);

        $wallet      = Auth::user()->wallet;
        $bankAccount = DB::table('bank_accounts')
            ->where('id', $request->bank_account_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$bankAccount) {
            return back()->withErrors(['bank_account_id' => 'Bank account not found.']);
        }

        if ($wallet->balance < $request->amount) {
            return back()->withInput()->withErrors(['amount' => 'Insufficient wallet balance.']);
        }

        return view('transfer.bank_confirm', [
            'wallet'      => $wallet,
            'bankAccount' => $bankAccount,
            'amount'      => $request->amount,
            'purpose'     => $request->purpose ?? '',
        ]);
    }

    // POST /transfer/bank/send
    public function send(Request $request)
    {
        $request->validate([
            'bank_account_id' => ['required', 'integer'],
            'amount'          => ['required', 'numeric', 'min:1'],
        ]);

        $wallet      = Auth::user()->wallet;
        $bankAccount = DB::table('bank_accounts')
            ->where('id', $request->bank_account_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$bankAccount) {
            return redirect()->route('transfer.bank')->with('error', 'Bank account not found.');
        }

        if ($wallet->balance < $request->amount) {
            return redirect()->route('transfer.bank')->with('error', 'Insufficient wallet balance.');
        }

        try {
            DB::statement('CALL bank_transfer(?, ?, ?, ?)', [
                $wallet->id,
                $bankAccount->id,
                $request->amount,
                $request->purpose ?? '',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('transfer.bank')->with('error', 'Transfer failed: ' . $e->getMessage());
        }

        $transaction = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'bank_transfer')
            ->orderByDesc('created_at')
            ->first();

        return redirect()->route('transfer.bank.receipt', $transaction->id);
    }

    // GET /transfer/bank/receipt/{id}
    // Now joins bank_accounts directly — no more parsing packed note string
    public function receipt($transactionId)
    {
        $user        = Auth::user();
        $wallet      = $user->wallet;
        $transaction = DB::table('transactions')->where('id', $transactionId)->first();

        if (!$transaction || $transaction->wallet_id !== $wallet->id) {
            return redirect()->route('transfer.bank');
        }

        // Join funds_transfers → bank_accounts for clean 3NF data retrieval
        $transfer = DB::table('funds_transfers as ft')
            ->leftJoin('bank_accounts as ba', 'ft.bank_account_id', '=', 'ba.id')
            ->where('ft.id', $transaction->transactable_id)
            ->select(
                'ft.purpose',
                'ft.amount',
                'ba.bank_name',
                'ba.account_name',
                'ba.account_number',
                'ba.account_type'
            )
            ->first();

        // Fallback if bank account was deleted after transfer
        $bankName    = $transfer->bank_name    ?? '—';
        $accountName = $transfer->account_name ?? '—';
        $accountNo   = $transfer->account_number ?? '—';
        $accountType = $transfer->account_type ?? '—';
        $purpose     = $transfer->purpose ?? '';

        return view('transfer.bank_receipt', compact(
            'transaction', 'wallet', 'bankName', 'accountName', 'accountNo', 'accountType', 'purpose'
        ));
    }
}