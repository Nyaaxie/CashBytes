<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ApiAuthController 
{
    use ApiResponse;

    // POST /api/v1/register
    public function register(Request $request)
    {
        $validator = validator($request->all(), [
            'name'       => ['required', 'string', 'max:255'],
            'contact_no' => ['required', 'string', 'max:15', 'unique:users,contact_no'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = User::create([
            'name'       => $request->name,
            'contact_no' => $request->contact_no,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);

        // Auto-create wallet
        $wallet = Wallet::create([
            'user_id'  => $user->id,
            'balance'  => 0.00,
            'currency' => 'PHP',
        ]);

        $token = $user->createToken('cashbytes-token')->plainTextToken;

        return $this->success([
            'user'   => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'contact_no' => $user->contact_no,
            ],
            'wallet' => [
                'id'       => $wallet->id,
                'balance'  => $wallet->balance,
                'currency' => $wallet->currency,
            ],
            'token'  => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful.', 201);
    }

    // POST /api/v1/login
    public function login(Request $request)
    {
        $validator = validator($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials.', 401);
        }

        // Revoke previous tokens (single session)
        $user->tokens()->delete();

        $token = $user->createToken('cashbytes-token')->plainTextToken;

        $wallet = $user->wallet;

        return $this->success([
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'contact_no' => $user->contact_no,
            ],
            'wallet' => $wallet ? [
                'id'       => $wallet->id,
                'balance'  => (float) $wallet->balance,
                'currency' => $wallet->currency,
            ] : null,
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    // POST /api/v1/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully.');
    }

    // POST /api/v1/logout-all (logout from all devices)
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return $this->success(null, 'Logged out from all devices.');
    }
}