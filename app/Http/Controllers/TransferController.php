<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferController
{
    // Show transfer form
    public function index()
    {
        $wallet = Auth::user()->wallet;
        return view('transfer.index', compact('wallet'));
    }

    // Process transfer
    public function send(Request $request)
    {
        $request->validate([
            'receiver_contact' => ['required', 'string'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'note'             => ['nullable', 'string', 'max:255'],
        ]);

        $sender = Auth::user();
        $senderWallet = $sender->wallet;

        // Find receiver by contact number or email
        $receiver = DB::table('users')
            ->where('contact_no', $request->receiver_contact)
            ->orWhere('email', $request->receiver_contact)
            ->first();

        if (!$receiver) {
            return back()
                ->withInput()
                ->withErrors(['receiver_contact' => 'No CashBytes account found for this contact number or email.']);
        }

        if ($receiver->id === $sender->id) {
            return back()
                ->withInput()
                ->withErrors(['receiver_contact' => 'You cannot transfer to your own account.']);
        }

        if ($senderWallet->balance < $request->amount) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Insufficient wallet balance.']);
        }

        $receiverWallet = DB::table('wallets')->where('user_id', $receiver->id)->first();

        if (!$receiverWallet) {
            return back()
                ->withInput()
                ->withErrors(['receiver_contact' => 'Receiver does not have a wallet yet.']);
        }

        try {
            DB::statement('CALL transfer_funds(?, ?, ?, ?)', [
                $senderWallet->id,
                $receiverWallet->id,
                $request->amount,
                $request->note ?? '',
            ]);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'Transfer failed: ' . $e->getMessage()]);
        }

        // Get the latest debit transaction for the receipt
        $transaction = DB::table('transactions')
            ->where('wallet_id', $senderWallet->id)
            ->where('type', 'transfer')
            ->orderByDesc('created_at')
            ->first();

        return redirect()->route('transfer.receipt', $transaction->id)
            ->with('receiver_name', $receiver->name)
            ->with('amount', $request->amount);
    }

    // Receipt
    public function receipt($transactionId)
    {
        $transaction = DB::table('transactions')->where('id', $transactionId)->first();

        if (!$transaction || $transaction->wallet_id !== Auth::user()->wallet->id) {
            return redirect()->route('transfer.index');
        }

        $wallet = Auth::user()->wallet;

        return view('transfer.receipt', compact('transaction', 'wallet'));
    }
}