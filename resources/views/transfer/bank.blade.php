@extends('layouts.app')

@section('title', 'Bank Transfer')

@section('content')
<div style="max-width:760px;margin:0 auto;">

    {{-- Page Header --}}
    <div style="margin-bottom:2rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.35rem;">
            <a href="{{ route('transfer.index') }}" style="color:var(--muted);text-decoration:none;font-size:0.85rem;">← Fund Transfer</a>
            <span style="color:var(--muted);">/</span>
            <span style="font-size:0.85rem;color:var(--navy);">Bank Transfer</span>
        </div>
        <h1 style="font-family:'DM Serif Display',serif;font-size:1.75rem;color:var(--navy);letter-spacing:-0.03em;">Bank Transfer</h1>
        <p style="color:var(--muted);font-size:0.875rem;margin-top:0.35rem;">Send money directly to any Philippine bank account</p>
    </div>

    {{-- Balance Card --}}
    <div style="background:var(--navy);border-radius:14px;padding:1.25rem 1.5rem;margin-bottom:1.75rem;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:0.75rem;color:rgba(255,255,255,0.5);letter-spacing:0.05em;text-transform:uppercase;">Available Balance</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.75rem;color:white;letter-spacing:-0.03em;margin-top:0.2rem;">
                ₱{{ number_format($wallet->balance, 2) }}
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:0.7rem;color:rgba(255,255,255,0.4);">Processing time</div>
            <div style="font-size:0.85rem;color:var(--gold);font-weight:600;margin-top:0.1rem;">Instant</div>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:0.85rem 1rem;margin-bottom:1.25rem;color:#16a34a;font-size:0.85rem;">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Saved Bank Accounts --}}
    <div style="background:white;border:1px solid var(--border);border-radius:14px;padding:1.5rem;margin-bottom:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
            <h2 style="font-family:'DM Serif Display',serif;font-size:1.1rem;color:var(--navy);">Saved Bank Accounts</h2>
            <button onclick="document.getElementById('add-account-panel').style.display='block';this.closest('button');document.getElementById('add-btn').style.display='none';"
                id="add-btn"
                style="padding:0.45rem 1rem;background:var(--navy);color:white;border:none;border-radius:8px;font-size:0.8rem;cursor:pointer;font-family:inherit;">
                + Add Account
            </button>
        </div>

        @if($bankAccounts->isEmpty())
            <div style="text-align:center;padding:2rem;color:var(--muted);">
                <div style="font-size:2rem;margin-bottom:0.5rem;">🏦</div>
                <div style="font-size:0.875rem;">No bank accounts saved yet.</div>
                <div style="font-size:0.8rem;margin-top:0.25rem;">Add one below to get started.</div>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                @foreach($bankAccounts as $account)
                <div style="border:2px solid {{ $account->is_default ? 'var(--gold)' : 'var(--border)' }};border-radius:10px;padding:1rem 1.25rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:0.85rem;">
                        <div style="width:42px;height:42px;background:{{ $account->is_default ? '#fef9ec' : '#f7f5f0' }};border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">🏦</div>
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;color:var(--navy);">{{ $account->bank_name }}</div>
                            <div style="font-size:0.8rem;color:var(--muted);margin-top:0.1rem;">{{ $account->account_name }} · {{ $account->account_number }}</div>
                            <div style="font-size:0.72rem;color:var(--muted);margin-top:0.1rem;text-transform:capitalize;">{{ $account->account_type }} account</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                        @if($account->is_default)
                            <span style="padding:0.2rem 0.6rem;background:#fef9ec;color:var(--gold);font-size:0.7rem;font-weight:700;border-radius:100px;border:1px solid #fde68a;">DEFAULT</span>
                        @else
                            <form method="POST" action="{{ route('transfer.bank.setDefault', $account->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" style="padding:0.3rem 0.65rem;background:transparent;border:1px solid var(--border);border-radius:6px;font-size:0.72rem;color:var(--muted);cursor:pointer;font-family:inherit;">Set Default</button>
                            </form>
                        @endif
                        <button onclick="selectAccount({{ $account->id }}, '{{ $account->bank_name }}', '{{ $account->account_name }}', '{{ $account->account_number }}')"
                            style="padding:0.3rem 0.85rem;background:var(--navy);color:white;border:none;border-radius:6px;font-size:0.78rem;cursor:pointer;font-family:inherit;">
                            Send
                        </button>
                        <form method="POST" action="{{ route('transfer.bank.deleteAccount', $account->id) }}" onsubmit="return confirm('Remove this bank account?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:0.3rem 0.65rem;background:transparent;border:1px solid #fecaca;border-radius:6px;font-size:0.72rem;color:#dc2626;cursor:pointer;font-family:inherit;">✕</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        {{-- Add Account Panel (hidden by default) --}}
        <div id="add-account-panel" style="display:none;margin-top:1.5rem;border-top:1px solid var(--border);padding-top:1.5rem;">
            <h3 style="font-size:0.95rem;font-weight:600;color:var(--navy);margin-bottom:1rem;">Add New Bank Account</h3>
            <form method="POST" action="{{ route('transfer.bank.addAccount') }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">Bank Name</label>
                        <select name="bank_name" required style="width:100%;padding:0.65rem 0.875rem;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:0.875rem;color:var(--navy);background:white;">
                            <option value="">Select a bank...</option>
                            <option value="BDO">Banco de Oro (BDO)</option>
                            <option value="BPI">Bank of the Philippine Islands (BPI)</option>
                            <option value="Metrobank">Metropolitan Bank & Trust (Metrobank)</option>
                            <option value="PNB">Philippine National Bank (PNB)</option>
                            <option value="Landbank">Land Bank of the Philippines</option>
                            <option value="DBP">Development Bank of the Philippines (DBP)</option>
                            <option value="UnionBank">UnionBank of the Philippines</option>
                            <option value="Chinabank">China Banking Corporation (Chinabank)</option>
                            <option value="RCBC">Rizal Commercial Banking Corporation (RCBC)</option>
                            <option value="Eastwest">EastWest Bank</option>
                            <option value="Security Bank">Security Bank Corporation</option>
                            <option value="PSBank">Philippine Savings Bank (PSBank)</option>
                            <option value="UCPB">United Coconut Planters Bank (UCPB)</option>
                            <option value="AUB">Asia United Bank (AUB)</option>
                            <option value="GCash">GCash (GXI)</option>
                            <option value="Maya">Maya (PayMaya)</option>
                            <option value="Seabank">SeaBank Philippines</option>
                            <option value="Tonik">Tonik Digital Bank</option>
                            <option value="GoTyme">GoTyme Bank</option>
                            <option value="OwnBank">OwnBank</option>
                        </select>
                        @error('bank_name')<div style="color:#dc2626;font-size:0.75rem;margin-top:0.3rem;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">Account Name</label>
                        <input type="text" name="account_name" placeholder="Juan dela Cruz" required
                            style="width:100%;padding:0.65rem 0.875rem;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:0.875rem;color:var(--navy);">
                        @error('account_name')<div style="color:#dc2626;font-size:0.75rem;margin-top:0.3rem;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">Account Number</label>
                        <input type="text" name="account_number" placeholder="0012345678" required
                            style="width:100%;padding:0.65rem 0.875rem;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:0.875rem;color:var(--navy);">
                        @error('account_number')<div style="color:#dc2626;font-size:0.75rem;margin-top:0.3rem;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">Account Type</label>
                        <select name="account_type" style="width:100%;padding:0.65rem 0.875rem;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:0.875rem;color:var(--navy);background:white;">
                            <option value="savings">Savings</option>
                            <option value="checking">Checking</option>
                        </select>
                    </div>
                    <div style="grid-column:1/-1;display:flex;gap:0.75rem;justify-content:flex-end;">
                        <button type="button" onclick="document.getElementById('add-account-panel').style.display='none';document.getElementById('add-btn').style.display='inline-block';"
                            style="padding:0.6rem 1.25rem;border:1px solid var(--border);background:white;border-radius:8px;font-size:0.875rem;cursor:pointer;font-family:inherit;color:var(--muted);">
                            Cancel
                        </button>
                        <button type="submit"
                            style="padding:0.6rem 1.5rem;background:var(--navy);color:white;border:none;border-radius:8px;font-size:0.875rem;cursor:pointer;font-family:inherit;font-weight:600;">
                            Save Account
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Transfer Form (shown after selecting account) --}}
    <div id="transfer-form-panel" style="display:none;background:white;border:1px solid var(--border);border-radius:14px;padding:1.5rem;margin-bottom:1.5rem;">
        <h2 style="font-family:'DM Serif Display',serif;font-size:1.1rem;color:var(--navy);margin-bottom:1.25rem;">Transfer Details</h2>

        {{-- Selected Account Display --}}
        <div id="selected-account-display" style="background:var(--cream);border-radius:10px;padding:1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.85rem;">
            <div style="font-size:1.5rem;">🏦</div>
            <div>
                <div id="display-bank-name" style="font-weight:600;font-size:0.9rem;color:var(--navy);"></div>
                <div id="display-account-details" style="font-size:0.8rem;color:var(--muted);margin-top:0.1rem;"></div>
            </div>
            <button onclick="document.getElementById('transfer-form-panel').style.display='none';"
                style="margin-left:auto;padding:0.3rem 0.65rem;background:transparent;border:1px solid var(--border);border-radius:6px;font-size:0.72rem;color:var(--muted);cursor:pointer;font-family:inherit;">
                Change
            </button>
        </div>

        <form method="POST" action="{{ route('transfer.bank.confirm') }}">
            @csrf
            <input type="hidden" name="bank_account_id" id="bank_account_id_input">

            @if($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:0.85rem 1rem;margin-bottom:1.25rem;color:#dc2626;font-size:0.85rem;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div style="margin-bottom:1.1rem;">
                <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">Amount (₱)</label>
                <input type="number" name="amount" step="0.01" min="1" placeholder="0.00"
                    value="{{ old('amount') }}" required
                    style="width:100%;padding:0.75rem 0.875rem;border:1px solid var(--border);border-radius:8px;font-family:'DM Serif Display',serif;font-size:1.5rem;color:var(--navy);">
                <div style="font-size:0.75rem;color:var(--muted);margin-top:0.3rem;">Available: ₱{{ number_format($wallet->balance, 2) }}</div>
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="font-size:0.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:0.4rem;">Purpose <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
                <input type="text" name="purpose" placeholder="e.g. Bayad sa utang, Tuition fee..."
                    value="{{ old('purpose') }}"
                    style="width:100%;padding:0.65rem 0.875rem;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:0.875rem;color:var(--navy);">
            </div>

            <button type="submit"
                style="width:100%;padding:0.875rem;background:var(--navy);color:white;border:none;border-radius:10px;font-size:1rem;font-weight:600;cursor:pointer;font-family:inherit;letter-spacing:-0.01em;">
                Review Transfer →
            </button>
        </form>
    </div>

    {{-- Info Box --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:1rem 1.25rem;">
        <div style="font-size:0.8rem;font-weight:600;color:#1d4ed8;margin-bottom:0.4rem;">ℹ Bank Transfer Info</div>
        <div style="font-size:0.78rem;color:#3b82f6;line-height:1.6;">
            Transfers are processed instantly within the CashBytes system. Actual bank crediting
            may take up to 1-3 banking days depending on the receiving bank. A reference number
            will be provided for tracking.
        </div>
    </div>

</div>

<script>
function selectAccount(id, bank, name, number) {
    document.getElementById('bank_account_id_input').value = id;
    document.getElementById('display-bank-name').textContent = bank;
    document.getElementById('display-account-details').textContent = name + ' · ' + number;
    document.getElementById('transfer-form-panel').style.display = 'block';
    document.getElementById('transfer-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Auto-open form if there were validation errors (user had already selected)
@if(old('bank_account_id'))
    document.addEventListener('DOMContentLoaded', function() {
        // re-open with old values if validation failed
        const id = {{ old('bank_account_id') }};
        document.getElementById('bank_account_id_input').value = id;
        document.getElementById('transfer-form-panel').style.display = 'block';
    });
@endif
</script>
@endsection