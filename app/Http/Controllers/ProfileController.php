<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class ProfileController
{
    public function index()
    {
        $user   = Auth::user();
        $wallet = $user->wallet;

        // Account stats
        $totalTransactions = DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->count();

        $totalSavingsGoals = DB::table('saving_goals')
            ->where('user_id', $user->id)
            ->count();

        $completedGoals = DB::table('saving_goals')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return view('profile.index', compact(
            'user',
            'wallet',
            'totalTransactions',
            'totalSavingsGoals',
            'completedGoals'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'contact_no' => ['required', 'string', 'max:15', 'unique:users,contact_no,' . $user->id],
            'email'      => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        DB::table('users')->where('id', $user->id)->update([
            'name'       => $request->name,
            'contact_no' => $request->contact_no,
            'email'      => $request->email,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password'   => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}