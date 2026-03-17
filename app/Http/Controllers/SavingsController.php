<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingsController 
{
    public function index()
    {
        $user   = Auth::user();
        $wallet = $user->wallet;

        $goals = DB::table('saving_goals')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $goals = $goals->map(function ($goal) {
            $goal->progress = $goal->target_amount > 0
                ? min(100, round(($goal->current_amount / $goal->target_amount) * 100, 1))
                : 0;
            return $goal;
        });

        return view('savings.index', compact('goals', 'wallet'));
    }

    public function create()
    {
        $categories = ['Travel', 'Tuition', 'Shopping', 'Emergency', 'Business', 'Custom'];
        return view('savings.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category'        => ['required', 'string'],
            'custom_category' => ['nullable', 'string', 'max:50'],
            'name'            => ['required', 'string', 'max:255'],
            'target_amount'   => ['required', 'numeric', 'min:1'],
            'target_date'     => ['nullable', 'date', 'after:today'],
        ]);

        $category = $request->category === 'Custom'
            ? trim($request->custom_category)
            : $request->category;

        if (empty($category)) {
            return back()->withInput()->withErrors(['custom_category' => 'Please enter a custom category name.']);
        }

        DB::table('saving_goals')->insert([
            'user_id'        => Auth::id(),
            'name'           => $request->name,
            'category'       => $category,
            'target_amount'  => $request->target_amount,
            'current_amount' => 0,
            'target_date'    => $request->target_date,
            'status'         => 'active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('savings.index')
            ->with('success', 'Savings goal "' . $request->name . '" created!');
    }

    public function show($goalId)
    {
        $user = Auth::user();

        $goal = DB::table('saving_goals')
            ->where('id', $goalId)
            ->where('user_id', $user->id)
            ->first();

        if (!$goal) {
            return redirect()->route('savings.index');
        }

        $goal->progress = $goal->target_amount > 0
            ? min(100, round(($goal->current_amount / $goal->target_amount) * 100, 1))
            : 0;

        $allocations = DB::table('savings_allocations as sa')
            ->join('transactions as t', 'sa.transaction_id', '=', 't.id')
            ->where('sa.savings_goal_id', $goalId)
            ->select('sa.amount', 't.created_at', 't.reference_no')
            ->orderByDesc('t.created_at')
            ->get();

        $wallet = $user->wallet;

        return view('savings.show', compact('goal', 'allocations', 'wallet'));
    }

    public function allocate(Request $request, $goalId)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $user   = Auth::user();
        $wallet = $user->wallet;

        $goal = DB::table('saving_goals')
            ->where('id', $goalId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$goal) {
            return redirect()->route('savings.index');
        }

        $remaining = $goal->target_amount - $goal->current_amount;

        if ($request->amount > $remaining) {
            return back()->withErrors(['amount' => "Maximum you can add is ₱" . number_format($remaining, 2)]);
        }

        if ($wallet->balance < $request->amount) {
            return back()->withErrors(['amount' => 'Insufficient wallet balance.']);
        }

        try {
            DB::statement('CALL add_to_savings(?, ?, ?)', [
                $wallet->id,
                $goalId,
                $request->amount,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => 'Failed: ' . $e->getMessage()]);
        }

        $transaction = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('type', 'save')
            ->orderByDesc('created_at')
            ->first();

        return redirect()->route('savings.receipt', $transaction->id)
            ->with('success', 'Savings updated successfully!');
    }

    // DELETE goal — refunds current_amount back to wallet
    public function destroy($goalId)
    {
        $user   = Auth::user();
        $wallet = $user->wallet;

        // Ownership check
        $goal = DB::table('saving_goals')
            ->where('id', $goalId)
            ->where('user_id', $user->id)
            ->first();

        if (!$goal) {
            return redirect()->route('savings.index')
                ->withErrors(['error' => 'Goal not found.']);
        }

        $refundAmount = $goal->current_amount;

        try {
            DB::statement('CALL delete_savings_goal(?, ?)', [
                $goalId,
                $wallet->id,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete goal: ' . $e->getMessage()]);
        }

        $message = $refundAmount > 0
            ? 'Goal deleted. ₱' . number_format($refundAmount, 2) . ' has been returned to your wallet.'
            : 'Savings goal deleted.';

        return redirect()->route('savings.index')->with('success', $message);
    }

    public function receipt($transactionId)
    {
        $transaction = DB::table('transactions')->where('id', $transactionId)->first();

        if (!$transaction || $transaction->wallet_id !== Auth::user()->wallet->id) {
            return redirect()->route('savings.index');
        }

        $wallet = Auth::user()->wallet;

        return view('savings.receipt', compact('transaction', 'wallet'));
    }

    public function withdraw(Request $request, $goalId)
{
    $request->validate([
        'amount' => ['required', 'numeric', 'min:1'],
    ]);

    $user   = Auth::user();
    $wallet = $user->wallet;

    $goal = DB::table('saving_goals')
        ->where('id', $goalId)
        ->where('user_id', $user->id)
        ->whereIn('status', ['active', 'completed'])
        ->first();

    if (!$goal) {
        return redirect()->route('savings.index');
    }

    if ($request->amount > $goal->current_amount) {
        return back()->withErrors(['withdraw_amount' => 'Cannot withdraw more than ₱' . number_format($goal->current_amount, 2) . '.']);
    }

    try {
        DB::statement('CALL withdraw_from_savings(?, ?, ?)', [
            $goalId,
            $wallet->id,
            $request->amount,
        ]);
    } catch (\Exception $e) {
        return back()->withErrors(['withdraw_amount' => 'Withdrawal failed: ' . $e->getMessage()]);
    }

    return back()->with('success', '₱' . number_format($request->amount, 2) . ' withdrawn and returned to your wallet.');
}
}