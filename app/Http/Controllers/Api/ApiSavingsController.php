<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiSavingsController 
{
    use ApiResponse;

    // GET /api/v1/savings
    public function index(Request $request)
    {
        $goals = DB::table('saving_goals')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($g) => $this->formatGoal($g));

        return $this->success(['goals' => $goals]);
    }

    // POST /api/v1/savings
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'name'          => ['required', 'string', 'max:255'],
            'category'      => ['required', 'in:Travel,Tuition,Shopping,Custom'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'target_date'   => ['nullable', 'date', 'after:today'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $goalId = DB::table('saving_goals')->insertGetId([
            'user_id'        => $request->user()->id,
            'name'           => $request->name,
            'category'       => $request->category,
            'target_amount'  => $request->target_amount,
            'current_amount' => 0,
            'target_date'    => $request->target_date,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $goal = DB::table('saving_goals')->where('id', $goalId)->first();

        return $this->success(['goal' => $this->formatGoal($goal)], 'Savings goal created.', 201);
    }

    // GET /api/v1/savings/{goalId}
    public function show(Request $request, $goalId)
    {
        $goal = DB::table('saving_goals')
            ->where('id', $goalId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$goal) {
            return $this->notFound('Savings goal not found.');
        }

        $allocations = DB::table('savings_allocations as sa')
            ->join('transactions as t', 'sa.transaction_id', '=', 't.id')
            ->where('sa.savings_goal_id', $goalId)
            ->select('sa.amount', 't.created_at', 't.reference_no')
            ->orderByDesc('t.created_at')
            ->get();

        return $this->success([
            'goal'        => $this->formatGoal($goal),
            'allocations' => $allocations,
        ]);
    }

    // POST /api/v1/savings/{goalId}/allocate
    public function allocate(Request $request, $goalId)
    {
        $validator = validator($request->all(), [
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user   = $request->user();
        $wallet = $user->wallet;

        $goal = DB::table('saving_goals')
            ->where('id', $goalId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$goal) {
            return $this->notFound('Active savings goal not found.');
        }

        $remaining = (float) $goal->target_amount - (float) $goal->current_amount;

        if ((float) $request->amount > $remaining) {
            return $this->error("Maximum you can add is ₱" . number_format($remaining, 2), 422);
        }

        if ((float) $wallet->balance < (float) $request->amount) {
            return $this->error('Insufficient wallet balance.', 422);
        }

        try {
            DB::statement('CALL add_to_savings(?, ?, ?)', [
                $wallet->id,
                $goalId,
                $request->amount,
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed: ' . $e->getMessage(), 500);
        }

        $transaction   = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'save')
            ->orderByDesc('created_at')
            ->first();

        $updatedGoal   = DB::table('saving_goals')->where('id', $goalId)->first();
        $updatedWallet = DB::table('wallets')->where('id', $wallet->id)->first();

        return $this->success([
            'transaction' => [
                'id'           => $transaction->id,
                'reference_no' => $transaction->reference_no,
                'amount'       => (float) $transaction->amount,
                'status'       => $transaction->status,
                'date'         => $transaction->created_at,
            ],
            'goal'        => $this->formatGoal($updatedGoal),
            'new_balance' => (float) $updatedWallet->balance,
        ], 'Funds added to savings goal.');
    }

    // ── Helper ────────────────────────────────────────────────────
    private function formatGoal($goal): array
    {
        $progress = $goal->target_amount > 0
            ? min(100, round(($goal->current_amount / $goal->target_amount) * 100, 1))
            : 0;

        return [
            'id'             => $goal->id,
            'name'           => $goal->name,
            'category'       => $goal->category,
            'target_amount'  => (float) $goal->target_amount,
            'current_amount' => (float) $goal->current_amount,
            'remaining'      => (float) ($goal->target_amount - $goal->current_amount),
            'progress'       => $progress,
            'target_date'    => $goal->target_date,
            'status'         => $goal->status,
            'created_at'     => $goal->created_at,
        ];
    }
}