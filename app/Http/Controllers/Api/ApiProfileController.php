<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ApiProfileController 
{
    use ApiResponse;

    // GET /api/v1/profile
    public function index(Request $request)
    {
        $user   = $request->user();
        $wallet = $user->wallet;

        $totalTransactions = $wallet ? DB::table('transactions')
            ->where('wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->count() : 0;

        $totalGoals = DB::table('saving_goals')
            ->where('user_id', $user->id)
            ->count();

        $completedGoals = DB::table('saving_goals')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return $this->success([
            'user' => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'contact_no'        => $user->contact_no,
                'email_verified_at' => $user->email_verified_at,
                'member_since'      => $user->created_at,
            ],
            'wallet' => $wallet ? [
                'id'       => $wallet->id,
                'balance'  => (float) $wallet->balance,
                'currency' => $wallet->currency,
            ] : null,
            'stats' => [
                'total_transactions' => $totalTransactions,
                'total_goals'        => $totalGoals,
                'completed_goals'    => $completedGoals,
            ],
        ]);
    }

    // PUT /api/v1/profile
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = validator($request->all(), [
            'name'       => ['required', 'string', 'max:255'],
            'contact_no' => ['required', 'string', 'max:15', 'unique:users,contact_no,' . $user->id],
            'email'      => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        DB::table('users')->where('id', $user->id)->update([
            'name'       => $request->name,
            'contact_no' => $request->contact_no,
            'email'      => $request->email,
            'updated_at' => now(),
        ]);

        return $this->success([
            'user' => [
                'id'         => $user->id,
                'name'       => $request->name,
                'email'      => $request->email,
                'contact_no' => $request->contact_no,
            ],
        ], 'Profile updated successfully.');
    }

    // PUT /api/v1/profile/password
    public function updatePassword(Request $request)
    {
        $validator = validator($request->all(), [
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect.', 422);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password'   => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        // Revoke all tokens — force re-login on all devices
        $user->tokens()->delete();

        return $this->success(null, 'Password updated. Please log in again.');
    }
}