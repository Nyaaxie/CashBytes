@extends('layouts.app')
@section('title', 'Confirm Bank Transfer')
@section('content')
<div style="max-width:520px;margin:0 auto;">

    <div style="margin-bottom:2rem;">
        <a href="{{ route('transfer.bank') }}" style="color:var(--muted);text-decoration:none;font-size:0.85rem;">← Back</a>
        <h1 style="font-family:'DM Serif Display',serif;font-size:1.75rem;color:var(--navy);letter-spacing:-0.03em;margin-top:0.5rem;">Confirm Transfer</h1>
        <p style="color:var(--muted);font-size:0.875rem;margin-top:0.35rem;">Please review the details before confirming</p>
    </div>

    {{-- Amount Card --}}
    <div style="background:var(--navy);border-radius:16px;padding:2rem;margin-bottom:1.5rem;text-align:center;">
        <div style="font-size:0.75rem;color:rgba(255,255,255,0.5);letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.5rem;">You are sending</div>
        <div style="font-family:'DM Serif Display',serif;font-size:3rem;color:white;letter-spacing:-0.03em;">
            ₱{{ number_format($amount, 2) }}
        </div>
        <div style="margin-top:0.5rem;font-size:0.8rem;color:rgba(255,255,255,0.4);">
            Remaining balance: ₱{{ number_format($wallet->balance - $amount, 2) }}
        </div>
    </div>

    {{-- Details --}}
    <div style="background:white;border:1px solid var(--border);border-radius:14px;padding:1.5rem;margin-bottom:1.5rem;">
        <div style="display:flex;flex-direction:column;">
            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Bank</span>
                <span style="font-size:0.875rem;font-weight:600;color:var(--navy);">{{ $bankAccount->bank_name }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Account Name</span>
                <span style="font-size:0.875rem;font-weight:600;color:var(--navy);">{{ $bankAccount->account_name }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Account Number</span>
                <span style="font-size:0.875rem;font-weight:600;color:var(--navy);font-family:'JetBrains Mono',monospace;">{{ $bankAccount->account_number }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Account Type</span>
                <span style="font-size:0.875rem;color:var(--navy);text-transform:capitalize;">{{ $bankAccount->account_type }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Amount</span>
                <span style="font-size:0.875rem;font-weight:700;color:var(--navy);">₱{{ number_format($amount, 2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;">
                <span style="font-size:0.82rem;color:var(--muted);">Purpose</span>
                <span style="font-size:0.875rem;color:var(--navy);">{{ $purpose ?: '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Confirm Form — posts directly to send --}}
    <form method="POST" action="{{ route('transfer.bank.send') }}">
        @csrf
        <input type="hidden" name="bank_account_id" value="{{ $bankAccount->id }}">
        <input type="hidden" name="amount"          value="{{ $amount }}">
        <input type="hidden" name="purpose"         value="{{ $purpose }}">

        <button type="submit"
            style="width:100%;padding:0.95rem;background:var(--gold);color:var(--navy);border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;font-family:inherit;letter-spacing:-0.01em;margin-bottom:0.75rem;">
            Confirm &amp; Send ₱{{ number_format($amount, 2) }}
        </button>

        <a href="{{ route('transfer.bank') }}"
            style="display:block;text-align:center;padding:0.85rem;border:1px solid var(--border);border-radius:10px;color:var(--muted);text-decoration:none;font-size:0.875rem;">
            Cancel
        </a>
    </form>

</div>
@endsection