@extends('layouts.app')
@section('title', 'Pay ' . $biller->name)
@section('content')
<div style="max-width:520px;">
    <div style="margin-bottom:1rem;">
        <a href="{{ route('bills.index') }}" class="btn btn-outline btn-sm">← Back to Billers</a>
    </div>
    <div class="card">
        <div class="card-header">
            <div>
                <span class="card-title">{{ $biller->name }}</span>
                <div style="font-size:0.78rem;color:var(--muted);margin-top:0.15rem;">{{ $biller->category }}</div>
            </div>
            <div style="font-size:0.8rem;color:var(--muted);">Balance: <strong style="color:var(--navy);">₱{{ number_format($wallet->balance, 2) }}</strong></div>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('bills.process', $biller->id) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Account Number</label>
                    <input class="form-input {{ $errors->has('account_number') ? 'is-invalid' : '' }}"
                        type="text" name="account_number"
                        value="{{ old('account_number', $pastPayment?->account_number ?? '') }}"
                        placeholder="Your account number with {{ $biller->name }}" required>
                    @if($pastPayment)
                        <div class="form-hint">Last used: {{ $pastPayment->account_number }}</div>
                    @endif
                    @error('account_number')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-weight:600;color:var(--muted);">₱</span>
                        <input class="form-input {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                            type="number" name="amount" value="{{ old('amount') }}"
                            placeholder="0.00" min="1" step="0.01" style="padding-left:2rem;" required>
                    </div>
                    @error('amount')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>
                <div style="background:var(--cream);border-radius:8px;padding:0.85rem 1rem;margin-bottom:1.25rem;font-size:0.825rem;color:var(--muted);">
                    ⚠ Make sure your account number is correct. Payments cannot be reversed.
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:0.88rem;">Pay {{ $biller->name }}</button>
            </form>
        </div>
    </div>
</div>
@endsection