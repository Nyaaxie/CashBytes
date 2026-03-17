<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiLoadController 
{
    use ApiResponse;

    private array $networks = [
        'Globe' => [
            ['code' => 'GO50',      'label' => 'GoSAKTO 50',     'amount' => 50.00,  'desc' => 'Unli texts + 1GB data, 3 days'],
            ['code' => 'GO90',      'label' => 'GoSAKTO 90',     'amount' => 90.00,  'desc' => 'Unli texts + 2GB data, 7 days'],
            ['code' => 'GOUNLI99',  'label' => 'GoUNLI 99',      'amount' => 99.00,  'desc' => 'Unli calls & texts, 7 days'],
            ['code' => 'GoSURF50',  'label' => 'GoSURF 50',      'amount' => 50.00,  'desc' => '1.5GB data, 3 days'],
            ['code' => 'GoWATCH99', 'label' => 'GoWATCH 99',     'amount' => 99.00,  'desc' => '4GB data for streaming, 7 days'],
        ],
        'Smart' => [
            ['code' => 'GIGA50',     'label' => 'GigaSurf 50',   'amount' => 50.00,  'desc' => '2GB data + Unli texts, 3 days'],
            ['code' => 'GIGA99',     'label' => 'GigaSurf 99',   'amount' => 99.00,  'desc' => '5GB data + Unli texts, 7 days'],
            ['code' => 'UNLISURF99', 'label' => 'UnliSurf 99',   'amount' => 99.00,  'desc' => 'Unli browsing, 7 days'],
            ['code' => 'BIG500',     'label' => 'Big Bytes 500',  'amount' => 500.00, 'desc' => '30GB data + Unli calls, 30 days'],
        ],
        'DITO' => [
            ['code' => 'DITO99',  'label' => 'DITO 99',  'amount' => 99.00,  'desc' => '8GB data + Unli calls & texts, 7 days'],
            ['code' => 'DITO199', 'label' => 'DITO 199', 'amount' => 199.00, 'desc' => '20GB data + Unli calls & texts, 15 days'],
        ],
        'TNT' => [
            ['code' => 'TNTGIGA55', 'label' => 'GigaKulit 55', 'amount' => 55.00, 'desc' => '2GB data + Unli texts to TNT/Smart, 3 days'],
            ['code' => 'TNTUNLI20', 'label' => 'UnliTxt 20',   'amount' => 20.00, 'desc' => 'Unli texts to TNT/Smart, 3 days'],
        ],
        'Sun' => [
            ['code' => 'SUNDATA99',  'label' => 'SunSulit 99', 'amount' => 99.00, 'desc' => '3GB data, 7 days'],
            ['code' => 'SUNSULIT50', 'label' => 'SunSulit 50', 'amount' => 50.00, 'desc' => '1GB data + Unli texts, 3 days'],
        ],
    ];

    // GET /api/v1/load/networks
    public function networks()
    {
        return $this->success(['networks' => $this->networks]);
    }

    // POST /api/v1/load
    public function buy(Request $request)
    {
        $validator = validator($request->all(), [
            'mobile_number' => ['required', 'regex:/^09\d{9}$/'],
            'network'       => ['required', 'in:Globe,Smart,DITO,TNT,Sun'],
            'promo_code'    => ['required', 'string'],
            'amount'        => ['required', 'numeric', 'min:1'],
        ], [
            'mobile_number.regex' => 'Enter a valid PH mobile number (e.g. 09171234567).',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return $this->error('Wallet not found.', 404);
        }

        if ((float) $wallet->balance < (float) $request->amount) {
            return $this->error('Insufficient wallet balance.', 422);
        }

        try {
            DB::statement('CALL buy_load(?, ?, ?, ?, ?)', [
                $wallet->id,
                $request->mobile_number,
                $request->network,
                $request->promo_code,
                $request->amount,
            ]);
        } catch (\Exception $e) {
            return $this->error('Load purchase failed: ' . $e->getMessage(), 500);
        }

        $transaction = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'load')
            ->orderByDesc('created_at')
            ->first();

        $loadDetails = DB::table('load_purchases')
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
            'load' => [
                'mobile_number' => $loadDetails->mobile_number ?? $request->mobile_number,
                'network'       => $loadDetails->network       ?? $request->network,
                'promo_code'    => $loadDetails->promo_code    ?? $request->promo_code,
            ],
            'new_balance' => (float) $updatedWallet->balance,
        ], 'Load purchased successfully.');
    }
}