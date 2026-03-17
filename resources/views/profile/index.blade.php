@extends('layouts.app')
@section('title', 'Profile')
@section('content')

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.75rem;">
    <div class="card"><div class="card-body" style="padding:1.1rem 1.25rem;">
        <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.3rem;">Transactions</div>
        <div style="font-family:'DM Serif Display',serif;font-size:1.5rem;color:var(--navy);">{{ $totalTransactions }}</div>
    </div></div>
    <div class="card"><div class="card-body" style="padding:1.1rem 1.25rem;">
        <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.3rem;">Total Goals</div>
        <div style="font-family:'DM Serif Display',serif;font-size:1.5rem;color:var(--navy);">{{ $totalSavingsGoals }}</div>
    </div></div>
    <div class="card"><div class="card-body" style="padding:1.1rem 1.25rem;">
        <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.3rem;">Goals Completed</div>
        <div style="font-family:'DM Serif Display',serif;font-size:1.5rem;color:var(--navy);">{{ $completedGoals }}</div>
    </div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;max-width:860px;">

    {{-- Profile Info --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Account Information</span></div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Mobile Number</label>
                    <input class="form-input {{ $errors->has('contact_no') ? 'is-invalid' : '' }}"
                        type="text" name="contact_no" value="{{ old('contact_no', $user->contact_no) }}" required>
                    @error('contact_no')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Wallet info (read-only) --}}
                <div style="background:var(--cream);border-radius:8px;padding:0.9rem 1rem;margin-bottom:1.1rem;">
                    <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.35rem;">Wallet Balance</div>
                    <div style="font-family:'DM Serif Display',serif;font-size:1.4rem;color:var(--navy);">₱{{ number_format($wallet->balance, 2) }}</div>
                    <div style="font-size:0.75rem;color:var(--muted);margin-top:0.2rem;">Wallet ID: #{{ $wallet->id }}</div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Save Changes</button>
            </form>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Change Password</span></div>
        <div class="card-body">
            @if($errors->has('current_password') || $errors->has('password'))
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input class="form-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                        type="password" name="current_password" placeholder="••••••••" required>
                    @error('current_password')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        type="password" name="password" placeholder="••••••••" required>
                    <div class="form-hint">Minimum 8 characters</div>
                    @error('password')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input class="form-input" type="password" name="password_confirmation" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Update Password</button>
            </form>

            {{-- Account Info --}}
            <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border);">
                <div style="font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.75rem;">Account Details</div>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    <div style="display:flex;justify-content:space-between;font-size:0.825rem;">
                        <span style="color:var(--muted);">Member since</span>
                        <span style="font-weight:500;">{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:0.825rem;">
                        <span style="color:var(--muted);">Email verified</span>
                        <span class="badge {{ $user->email_verified_at ? 'badge-success' : 'badge-error' }}">
                            {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection