<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController 
{
    // ── SHOW LOGIN ──────────────────────────────────────────
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // ── LOGIN ───────────────────────────────────────────────
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withInput($request->only('password'))
            ->withErrors(['email' => 'Invalid email'])
            ->withErrors(['password' => 'Invalid password or email']);
    }

    // ── SHOW REGISTER ────────────────────────────────────────
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    // ── REGISTER ─────────────────────────────────────────────
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'contact_no' => ['required', 'string', 'max:15', 'unique:users,contact_no'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(8)],
        ], [
            'contact_no.unique' => 'This mobile number is already registered.',
            'email.unique'      => 'This email address is already in use.',
            'password.confirmed'=> 'Passwords do not match.',
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'contact_no' => $validated['contact_no'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
        ]);

        // Auto-create wallet on registration
        Wallet::create([
            'user_id'  => $user->id,
            'balance'  => 0.00,
            'currency' => 'PHP',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // ── LOGOUT ───────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('welcome');
    }
}