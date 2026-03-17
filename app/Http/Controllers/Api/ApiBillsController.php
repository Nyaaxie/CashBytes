<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiBillsController 
{
    use ApiResponse;

    // GET /api/v1/bills
    public function index(Request $request)
    {
        $query = DB::table('billers')->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $billers = $query->orderBy('category')->orderBy('name')->get();

        $grouped = $billers->groupBy('category')->map(fn($group) =>
            $group->map(fn($b) => [
                'id'       => $b->id,
                'name'     => $b->name,
                'category' => $b->category,
            ])
        );

        return $this->success([
            'billers'    => $grouped,
            'categories' => $billers->pluck('category')->unique()->values(),
        ]);
    }

    // GET /api/v1/bills/{billerId}
    public function show(Request $request, $billerId)
    {
        $biller = DB::table('billers')
            ->where('id', $billerId)
            ->where('is_active', true)
            ->first();

        if (!$biller) {
            return $this->notFound('Biller not found.');
        }

        // Last account number used for this biller by this user
        $wallet = $request->user()->wallet;
        $pastPayment = $wallet ? DB::table('bills_payments')
            ->where('wallet_id', $wallet->id)
            ->where('biller_id', $billerId)
            ->orderByDesc('created_at')
            ->first() : null;

        return $this->success([
            'biller' => [
                'id'       => $biller->id,
                'name'     => $biller->name,
                'category' => $biller->category,
            ],
            'last_account_number' => $pastPayment?->account_number ?? null,
        ]);
    }

    // POST /api/v1/bills/pay
    public function pay(Request $request)
    {
        $validator = validator($request->all(), [
            'biller_id'      => ['required', 'integer', 'exists:billers,id'],
            'account_number' => ['required', 'string', 'max:100'],
            'amount'         => ['required', 'numeric', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $biller = DB::table('billers')
            ->where('id', $request->biller_id)
            ->where('is_active', true)
            ->first();

        if (!$biller) {
            return $this->notFound('Biller not found or inactive.');
        }

        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return $this->error('Wallet not found.', 404);
        }

        if ((float) $wallet->balance < (float) $request->amount) {
            return $this->error('Insufficient wallet balance.', 422);
        }

        try {
            DB::statement('CALL pay_bill(?, ?, ?, ?)', [
                $wallet->id,
                $request->biller_id,
                $request->account_number,
                $request->amount,
            ]);
        } catch (\Exception $e) {
            return $this->error('Payment failed: ' . $e->getMessage(), 500);
        }

        $transaction = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'bill_payment')
            ->orderByDesc('created_at')
            ->first();

        $billDetails = DB::table('bills_payments')
            ->where('id', $transaction->transactable_id)
            ->first();

        $updatedWallet = DB::table('wallets')->where('id', $wallet->id)->first();

        return $this->success([
            'transaction' => [
                'id'           => $transaction->id,
                'reference_no' => $transaction->reference_no,
                'amount'       => (float) $transaction->amount,
                'status'       => $transaction->status,
                'date'         => $transaction->created_at,
            ],
            'bill' => [
                'biller_name'     => $biller->name,
                'biller_category' => $biller->category,
                'account_number'  => $request->account_number,
                'confirmation_no' => $billDetails->confirmation_no ?? null,
            ],
            'new_balance' => (float) $updatedWallet->balance,
        ], 'Bill payment successful.');
    }
}