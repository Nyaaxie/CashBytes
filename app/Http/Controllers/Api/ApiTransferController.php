<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiTransferController 
{
    use ApiResponse;

    // POST /api/v1/transfer
    public function send(Request $request)
    {
        $validator = validator($request->all(), [
            'receiver_contact' => ['required', 'string'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'note'             => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $sender       = $request->user();
        $senderWallet = $sender->wallet;

        if (!$senderWallet) {
            return $this->error('Sender wallet not found.', 404);
        }

        // Find receiver by contact number or email
        $receiver = DB::table('users')
            ->where('contact_no', $request->receiver_contact)
            ->orWhere('email', $request->receiver_contact)
            ->first();

        if (!$receiver) {
            return $this->error('No CashBytes account found for this contact number or email.', 404);
        }

        if ($receiver->id === $sender->id) {
            return $this->error('You cannot transfer to your own account.', 422);
        }

        $receiverWallet = DB::table('wallets')->where('user_id', $receiver->id)->first();

        if (!$receiverWallet) {
            return $this->error('Receiver does not have a wallet yet.', 404);
        }

        if ((float) $senderWallet->balance < (float) $request->amount) {
            return $this->error('Insufficient wallet balance.', 422);
        }

        try {
            DB::statement('CALL transfer_funds(?, ?, ?, ?)', [
                $senderWallet->id,
                $receiverWallet->id,
                $request->amount,
                $request->note ?? '',
            ]);
        } catch (\Exception $e) {
            return $this->error('Transfer failed: ' . $e->getMessage(), 500);
        }

        // Get the latest transaction for reference
        $transaction = DB::table('transactions')
            ->where('wallet_id', $senderWallet->id)
            ->where('type', 'transfer')
            ->orderByDesc('created_at')
            ->first();

        // Refresh balance
        $updatedWallet = DB::table('wallets')->where('id', $senderWallet->id)->first();

        return $this->success([
            'transaction' => [
                'id'           => $transaction->id,
                'reference_no' => $transaction->reference_no,
                'amount'       => (float) $transaction->amount,
                'description'  => $transaction->description,
                'status'       => $transaction->status,
                'date'         => $transaction->created_at,
            ],
            'receiver' => [
                'name'       => $receiver->name,
                'contact_no' => $receiver->contact_no,
            ],
            'new_balance' => (float) $updatedWallet->balance,
        ], 'Transfer successful.');
    }
}