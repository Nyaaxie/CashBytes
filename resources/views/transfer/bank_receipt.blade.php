@extends('layouts.app')
@section('title', 'Bank Transfer Receipt')
@section('content')
<div style="max-width:520px;margin:0 auto;">

    {{-- Success Icon --}}
    <div style="text-align:center;margin-bottom:2rem;">
        <div style="width:64px;height:64px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.75rem;">✓</div>
        <h1 style="font-family:'DM Serif Display',serif;font-size:1.75rem;color:var(--navy);letter-spacing:-0.03em;">Transfer Sent!</h1>
        <p style="color:var(--muted);font-size:0.875rem;margin-top:0.35rem;">Your bank transfer has been processed</p>
    </div>

    {{-- Amount Card --}}
    <div style="background:var(--navy);border-radius:16px;padding:2rem;margin-bottom:1.5rem;text-align:center;">
        <div style="font-size:0.75rem;color:rgba(255,255,255,0.5);letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.5rem;">Amount Sent</div>
        <div style="font-family:'DM Serif Display',serif;font-size:3rem;color:white;letter-spacing:-0.03em;">
            ₱{{ number_format($transaction->amount, 2) }}
        </div>
        <div style="margin-top:0.75rem;display:inline-block;padding:0.3rem 0.85rem;background:rgba(255,255,255,0.1);border-radius:100px;">
            <span style="font-size:0.72rem;color:rgba(255,255,255,0.6);font-family:'JetBrains Mono',monospace;">{{ $transaction->reference_no }}</span>
        </div>
    </div>

    {{-- Details --}}
    <div style="background:white;border:1px solid var(--border);border-radius:14px;padding:1.5rem;margin-bottom:1.5rem;">
        <h2 style="font-family:'DM Serif Display',serif;font-size:1rem;color:var(--navy);margin-bottom:1rem;">Transfer Details</h2>
        <div style="display:flex;flex-direction:column;">
            <div style="display:flex;justify-content:space-between;padding:0.7rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Bank</span>
                <span style="font-size:0.875rem;font-weight:600;color:var(--navy);">{{ $bankName }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.7rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Account Name</span>
                <span style="font-size:0.875rem;font-weight:600;color:var(--navy);">{{ $accountName }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.7rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Account Number</span>
                <span style="font-size:0.875rem;color:var(--navy);font-family:'JetBrains Mono',monospace;">{{ $accountNo }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.7rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Account Type</span>
                <span style="font-size:0.875rem;color:var(--navy);text-transform:capitalize;">{{ $accountType }}</span>
            </div>
            @if($purpose)
            <div style="display:flex;justify-content:space-between;padding:0.7rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Purpose</span>
                <span style="font-size:0.875rem;color:var(--navy);">{{ $purpose }}</span>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;padding:0.7rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Status</span>
                <span style="font-size:0.875rem;font-weight:600;color:#16a34a;">● Completed</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.7rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:0.82rem;color:var(--muted);">Date & Time</span>
                <span style="font-size:0.875rem;color:var(--navy);">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y h:i A') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.7rem 0;">
                <span style="font-size:0.82rem;color:var(--muted);">New Balance</span>
                <span style="font-size:0.875rem;font-weight:700;color:var(--navy);">₱{{ number_format($wallet->balance, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Info --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.5rem;">
        <div style="font-size:0.78rem;color:#3b82f6;line-height:1.6;">
            ℹ Actual crediting to the receiving bank may take 1-3 banking days. Keep your reference number <strong>{{ $transaction->reference_no }}</strong> for tracking.
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:0.75rem;">
        <a href="{{ route('transfer.bank') }}"
            style="flex:1;display:block;text-align:center;padding:0.85rem;background:var(--navy);color:white;text-decoration:none;border-radius:10px;font-size:0.875rem;font-weight:600;">
            New Transfer
        </a>
        <a href="{{ route('dashboard') }}"
            style="flex:1;display:block;text-align:center;padding:0.85rem;border:1px solid var(--border);color:var(--navy);text-decoration:none;border-radius:10px;font-size:0.875rem;">
            Back to Dashboard
        </a>
    </div>

</div>
@endsection