@extends('layouts.app')
@section('title', $goal->name)
@section('content')
<div style="display:grid;grid-template-columns:1fr 1.2fr;gap:1.25rem;max-width:860px;">

    {{-- Left Column --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Goal Info Card --}}
        <div class="card">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem;">
                    <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);">{{ $goal->category }}</div>
                    <span class="badge badge-{{ $goal->status === 'completed' ? 'success' : ($goal->status === 'cancelled' ? 'error' : 'warning') }}">
                        {{ $goal->status }}
                    </span>
                </div>
                <div style="font-family:'DM Serif Display',serif;font-size:1.5rem;color:var(--navy);margin-bottom:1.25rem;">{{ $goal->name }}</div>

                <div style="margin-bottom:1rem;">
                    <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:0.5rem;">
                        <span style="color:var(--muted);">{{ $goal->progress }}% complete</span>
                        <span style="font-weight:600;">₱{{ number_format($goal->current_amount, 2) }} / ₱{{ number_format($goal->target_amount, 2) }}</span>
                    </div>
                    <div class="progress-bar-track" style="height:8px;">
                        <div class="progress-bar-fill" style="width:{{ $goal->progress }}%"></div>
                    </div>
                </div>

                @if($goal->target_date)
                <div style="font-size:0.8rem;color:var(--muted);margin-bottom:1rem;">
                    Target date: {{ \Carbon\Carbon::parse($goal->target_date)->format('M d, Y') }}
                </div>
                @endif

                <div style="display:flex;gap:0.75rem;margin-top:0.5rem;">
                    <div style="flex:1;background:var(--cream);border-radius:8px;padding:0.85rem;text-align:center;">
                        <div style="font-size:0.72rem;color:var(--muted);margin-bottom:0.2rem;">Remaining</div>
                        <div style="font-weight:600;color:var(--navy);">₱{{ number_format($goal->target_amount - $goal->current_amount, 2) }}</div>
                    </div>
                    <div style="flex:1;background:var(--cream);border-radius:8px;padding:0.85rem;text-align:center;">
                        <div style="font-size:0.72rem;color:var(--muted);margin-bottom:0.2rem;">Wallet</div>
                        <div style="font-weight:600;color:var(--navy);">₱{{ number_format($wallet->balance, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deposit & Withdraw --}}
        @if($goal->status === 'active')
        <div class="card">

            {{-- Tab Header --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border);">
                <button id="btn-deposit" onclick="showPanel('deposit')"
                    style="padding:0.85rem;background:var(--navy);color:white;border:none;border-right:1px solid var(--border);font-family:inherit;font-size:0.875rem;font-weight:600;cursor:pointer;transition:all 0.15s;">
                    ↑ Deposit
                </button>
                <button id="btn-withdraw" onclick="showPanel('withdraw')"
                    style="padding:0.85rem;background:transparent;color:var(--muted);border:none;font-family:inherit;font-size:0.875rem;font-weight:500;cursor:pointer;transition:all 0.15s;">
                    ↓ Withdraw
                </button>
            </div>

            {{-- Deposit Panel --}}
            <div id="panel-deposit" class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->has('amount'))
                    <div class="alert alert-error">{{ $errors->first('amount') }}</div>
                @endif
                <form method="POST" action="{{ route('savings.allocate', $goal->id) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Amount to Deposit</label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-weight:600;color:var(--muted);">₱</span>
                            <input class="form-input {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                                type="number" name="amount" value="{{ old('amount') }}"
                                placeholder="0.00" min="1" step="0.01" style="padding-left:2rem;" required>
                        </div>
                        <div class="form-hint">Max: ₱{{ number_format($goal->target_amount - $goal->current_amount, 2) }} remaining</div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Save to Goal</button>
                </form>
            </div>

            {{-- Withdraw Panel --}}
            <div id="panel-withdraw" class="card-body" style="display:none;">
                @if($errors->has('withdraw_amount'))
                    <div class="alert alert-error">{{ $errors->first('withdraw_amount') }}</div>
                @endif
                @if($goal->current_amount > 0)
                    <form method="POST" action="{{ route('savings.withdraw', $goal->id) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Amount to Withdraw</label>
                            <div style="position:relative;">
                                <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-weight:600;color:var(--muted);">₱</span>
                                <input class="form-input {{ $errors->has('withdraw_amount') ? 'is-invalid' : '' }}"
                                    type="number" name="amount" value="{{ old('withdraw_amount') }}"
                                    placeholder="0.00" min="1" step="0.01" style="padding-left:2rem;" required>
                            </div>
                            <div class="form-hint">Max: ₱{{ number_format($goal->current_amount, 2) }} saved</div>
                        </div>
                        <button type="submit"
                            style="width:100%;padding:0.75rem;background:transparent;border:1.5px solid var(--navy);border-radius:8px;color:var(--navy);font-family:inherit;font-size:0.875rem;font-weight:600;cursor:pointer;transition:all 0.15s;"
                            onmouseover="this.style.background='var(--navy)';this.style.color='white'"
                            onmouseout="this.style.background='transparent';this.style.color='var(--navy)'">
                            Withdraw to Wallet
                        </button>
                    </form>
                @else
                    <div style="text-align:center;padding:1.5rem 1rem;color:var(--muted);font-size:0.875rem;">
                        No saved amount to withdraw yet.
                    </div>
                @endif
            </div>

        </div>
        @endif

        @if($goal->status === 'completed')
        {{-- Completed: show Withdraw full balance back to wallet --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Withdraw Balance</span></div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->has('withdraw_amount'))
                    <div class="alert alert-error">{{ $errors->first('withdraw_amount') }}</div>
                @endif
                <div style="font-size:0.8rem;color:var(--muted);margin-bottom:1rem;">
                    Your goal is complete! Withdraw your savings back to your wallet.
                </div>
                <form method="POST" action="{{ route('savings.withdraw', $goal->id) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Amount to Withdraw</label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-weight:600;color:var(--muted);">₱</span>
                            <input class="form-input {{ $errors->has('withdraw_amount') ? 'is-invalid' : '' }}"
                                type="number" name="amount" value="{{ old('withdraw_amount', $goal->current_amount) }}"
                                placeholder="0.00" min="1" step="0.01" style="padding-left:2rem;" required>
                        </div>
                        <div class="form-hint">Max: ₱{{ number_format($goal->current_amount, 2) }} saved</div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        Withdraw to Wallet
                    </button>
                </form>
            </div>
        </div>
        @else
        {{-- Active/Cancelled: show Delete Goal --}}
        <div class="card" style="border-color:#fecaca;">
            <div class="card-body" style="padding:1rem 1.25rem;">
                <div style="font-size:0.8rem;font-weight:600;color:#dc2626;margin-bottom:0.35rem;">Delete Goal</div>
                <div style="font-size:0.78rem;color:var(--muted);margin-bottom:0.85rem;">
                    @if($goal->current_amount > 0)
                        ₱{{ number_format($goal->current_amount, 2) }} will be returned to your wallet.
                    @else
                        This goal has no saved amount. It will be permanently deleted.
                    @endif
                </div>
                <form method="POST" action="{{ route('savings.destroy', $goal->id) }}"
                    onsubmit="return confirmDelete('{{ addslashes($goal->name) }}', {{ $goal->current_amount }})">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        style="width:100%;padding:0.6rem;background:transparent;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-family:inherit;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.15s;"
                        onmouseover="this.style.background='#fef2f2'"
                        onmouseout="this.style.background='transparent'">
                        Delete this Goal
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

    {{-- Contribution History --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Contribution History</span></div>
        @if($allocations->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">◎</div>
                <h3>No contributions yet</h3>
                <p>Add funds to start building toward your goal</p>
            </div>
        @else
            <div>
                @foreach($allocations as $alloc)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.9rem 1.25rem;border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-size:0.825rem;font-weight:500;color:var(--navy);">Contribution</div>
                        <div style="font-size:0.75rem;color:var(--muted);">{{ \Carbon\Carbon::parse($alloc->created_at)->format('M d, Y g:i A') }}</div>
                        <div style="font-size:0.7rem;color:var(--muted);font-family:monospace;">{{ $alloc->reference_no }}</div>
                    </div>
                    <div class="txn-credit">+₱{{ number_format($alloc->amount, 2) }}</div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<div style="margin-top:1rem;">
    <a href="{{ route('savings.index') }}" class="btn btn-outline btn-sm">← Back to Goals</a>
</div>

<script>
function showPanel(panel) {
    const isDeposit = panel === 'deposit';

    document.getElementById('panel-deposit').style.display  = isDeposit ? 'block' : 'none';
    document.getElementById('panel-withdraw').style.display = isDeposit ? 'none'  : 'block';

    document.getElementById('btn-deposit').style.background = isDeposit ? 'var(--navy)' : 'transparent';
    document.getElementById('btn-deposit').style.color      = isDeposit ? 'white' : 'var(--muted)';
    document.getElementById('btn-withdraw').style.background = isDeposit ? 'transparent' : 'var(--navy)';
    document.getElementById('btn-withdraw').style.color      = isDeposit ? 'var(--muted)' : 'white';
}

// Auto-open withdraw tab if it had a validation error
@if($errors->has('withdraw_amount'))
    document.addEventListener('DOMContentLoaded', function() { showPanel('withdraw'); });
@endif

function confirmDelete(name, amount) {
    if (amount > 0) {
        return confirm('Delete "' + name + '"?\n\n₱' + parseFloat(amount).toLocaleString('en-PH', {minimumFractionDigits:2}) + ' will be returned to your wallet.');
    }
    return confirm('Delete "' + name + '"? This cannot be undone.');
}
</script>

@endsection