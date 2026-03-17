@extends('layouts.app')

@section('title', 'Fund Transfer')

@section('content')
<div style="max-width:760px;margin:0 auto;">

    {{-- Page Header --}}
    <div style="margin-bottom:2rem;">
        <h1 style="font-family:'DM Serif Display',serif;font-size:1.75rem;color:var(--navy);letter-spacing:-0.03em;">Fund Transfer</h1>
        <p style="color:var(--muted);font-size:0.875rem;margin-top:0.35rem;">Send money to another CashBytes user or to a bank account</p>
    </div>

    {{-- Balance Card --}}
    <div style="background:var(--navy);border-radius:14px;padding:1.25rem 1.5rem;margin-bottom:1.75rem;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:0.75rem;color:rgba(255,255,255,0.5);letter-spacing:0.05em;text-transform:uppercase;">Available Balance</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.75rem;color:white;letter-spacing:-0.03em;margin-top:0.2rem;">
                ₱{{ number_format($wallet->balance, 2) }}
            </div>
        </div>
        <div style="font-size:1.5rem;opacity:0.3;">💸</div>
    </div>

    {{-- Transfer Type Selector --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem;">

        {{-- CashBytes Transfer --}}
        <button onclick="showForm('cashbytes')"
            id="tab-cashbytes"
            style="padding:1.25rem;background:var(--navy);color:white;border:2px solid var(--navy);border-radius:12px;cursor:pointer;font-family:inherit;text-align:left;transition:all 0.15s;">
            <div style="font-size:1.5rem;margin-bottom:0.5rem;">👤</div>
            <div style="font-weight:700;font-size:0.95rem;">CashBytes User</div>
            <div style="font-size:0.75rem;opacity:0.7;margin-top:0.25rem;">Send to contact number or email</div>
        </button>

        {{-- Bank Transfer --}}
        <a href="{{ route('transfer.bank') }}"
            style="padding:1.25rem;background:white;color:var(--navy);border:2px solid var(--border);border-radius:12px;cursor:pointer;font-family:inherit;text-align:left;text-decoration:none;display:block;transition:all 0.15s;"
            onmouseover="this.style.borderColor='var(--navy)'"
            onmouseout="this.style.borderColor='var(--border)'">
            <div style="font-size:1.5rem;margin-bottom:0.5rem;">🏦</div>
            <div style="font-weight:700;font-size:0.95rem;">Bank Transfer</div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:0.25rem;">Send to BDO, BPI, Metrobank & more</div>
        </a>

    </div>

    {{-- CashBytes Transfer Form --}}
    <div id="form-cashbytes" style="background:white;border:1px solid var(--border);border-radius:14px;padding:1.5rem;">
        <h2 style="font-family:'DM Serif Display',serif;font-size:1.1rem;color:var(--navy);margin-bottom:1.25rem;">Send to CashBytes User</h2>

        @if ($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:0.85rem 1rem;margin-bottom:1.25rem;color:#dc2626;font-size:0.85rem;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('transfer.send') }}">
            @csrf

            <div style="margin-bottom:1.1rem;">
                <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">Recipient</label>
                <input type="text" name="receiver_contact"
                    value="{{ old('receiver_contact') }}"
                    placeholder="Contact number or email (e.g. 09171234567)"
                    required
                    style="width:100%;padding:0.65rem 0.875rem;border:1px solid {{ $errors->has('receiver_contact') ? '#fca5a5' : 'var(--border)' }};border-radius:8px;font-family:inherit;font-size:0.875rem;color:var(--navy);">
                @error('receiver_contact')
                    <div style="color:#dc2626;font-size:0.75rem;margin-top:0.3rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:1.1rem;">
                <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">Amount (₱)</label>
                <input type="number" name="amount" step="0.01" min="1"
                    value="{{ old('amount') }}"
                    placeholder="0.00"
                    required
                    style="width:100%;padding:0.75rem 0.875rem;border:1px solid {{ $errors->has('amount') ? '#fca5a5' : 'var(--border)' }};border-radius:8px;font-family:'DM Serif Display',serif;font-size:1.5rem;color:var(--navy);">
                <div style="font-size:0.75rem;color:var(--muted);margin-top:0.3rem;">Available: ₱{{ number_format($wallet->balance, 2) }}</div>
                @error('amount')
                    <div style="color:#dc2626;font-size:0.75rem;margin-top:0.3rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">
                    Note <span style="font-weight:400;color:var(--muted);">(optional)</span>
                </label>
                <input type="text" name="note"
                    value="{{ old('note') }}"
                    placeholder="e.g. Bayad sa utang, Para sa pagkain..."
                    style="width:100%;padding:0.65rem 0.875rem;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:0.875rem;color:var(--navy);">
            </div>

            <button type="submit"
                style="width:100%;padding:0.875rem;background:var(--navy);color:white;border:none;border-radius:10px;font-size:1rem;font-weight:600;cursor:pointer;font-family:inherit;letter-spacing:-0.01em;">
                Send Money →
            </button>
        </form>
    </div>

</div>

<script>
function showForm(type) {
    document.getElementById('form-cashbytes').style.display = type === 'cashbytes' ? 'block' : 'none';
    document.getElementById('tab-cashbytes').style.background = type === 'cashbytes' ? 'var(--navy)' : 'white';
    document.getElementById('tab-cashbytes').style.color = type === 'cashbytes' ? 'white' : 'var(--navy)';
}
</script>
@endsection