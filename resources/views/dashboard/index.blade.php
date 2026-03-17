@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-bottom:1.75rem;">

    {{-- Balance --}}
    <div class="card" style="border-left:3px solid var(--gold);">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <div style="font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.35rem;">Wallet Balance</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.8rem;color:var(--navy);letter-spacing:-0.03em;">₱{{ number_format($wallet->balance, 2) }}</div>
            <div style="font-size:0.78rem;color:var(--muted);margin-top:0.25rem;">PHP · {{ $wallet->currency }}</div>
        </div>
    </div>

    {{-- Monthly Spending --}}
    <div class="card">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <div style="font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.35rem;">This Month</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.8rem;color:var(--navy);letter-spacing:-0.03em;">₱{{ number_format($monthlySpending, 2) }}</div>
            <div style="font-size:0.78rem;color:var(--muted);margin-top:0.25rem;">Total spent {{ now()->format('F Y') }}</div>
        </div>
    </div>

    {{-- Total Transactions --}}
    <div class="card">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <div style="font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.35rem;">Transactions</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.8rem;color:var(--navy);letter-spacing:-0.03em;">{{ $totalTransactions }}</div>
            <div style="font-size:0.78rem;color:var(--muted);margin-top:0.25rem;">Completed all time</div>
        </div>
    </div>

    {{-- Active Goals --}}
    <div class="card">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <div style="font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.35rem;">Savings Goals</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.8rem;color:var(--navy);letter-spacing:-0.03em;">{{ $savingsGoals->count() }}</div>
            <div style="font-size:0.78rem;color:var(--muted);margin-top:0.25rem;">Active goals</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:1.25rem;">

    {{-- Recent Transactions --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Transactions</span>
            <a href="{{ route('transactions.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        @if($recentTransactions->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">↔</div>
                <h3>No transactions yet</h3>
                <p>Your activity will appear here</p>
            </div>
        @else
            <div style="padding:0 0.25rem;">
                @foreach($recentTransactions as $txn)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.9rem 1.25rem;border-bottom:1px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:0.85rem;">
                        <div style="width:36px;height:36px;border-radius:9px;background:var(--cream);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                            @if($txn->type === 'transfer') ↗
                            @elseif($txn->type === 'save') ◎
                            @elseif($txn->type === 'load') ▤
                            @else ◻ @endif
                        </div>
                        <div>
                            <div style="font-size:0.875rem;font-weight:500;color:var(--navy);">{{ $txn->description }}</div>
                            <div style="font-size:0.75rem;color:var(--muted);">{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, g:i A') }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="{{ $txn->direction === 'debit' ? 'txn-debit' : 'txn-credit' }}" style="font-size:0.9rem;text-align:right;">
                            {{ $txn->direction === 'debit' ? '−' : '+' }}₱{{ number_format($txn->amount, 2) }}
                        </div>
                        <div style="text-align:right;">
                            <span class="badge badge-{{ $txn->status === 'completed' ? 'success' : ($txn->status === 'pending' ? 'warning' : 'error') }}">
                                {{ $txn->status }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Savings Goals --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Savings Goals</span>
            <a href="{{ route('savings.create') }}" class="btn btn-outline btn-sm">+ New</a>
        </div>
        @if($savingsGoals->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">◎</div>
                <h3>No savings goals</h3>
                <p>Create your first goal to start saving</p>
            </div>
        @else
            <div style="padding:0.5rem 1.25rem;display:flex;flex-direction:column;gap:1rem;">
                @foreach($savingsGoals as $goal)
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem;">
                        <div>
                            <div style="font-size:0.875rem;font-weight:500;color:var(--navy);">{{ $goal->name }}</div>
                            <div style="font-size:0.75rem;color:var(--muted);">{{ $goal->category }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.8rem;font-weight:600;color:var(--navy);">₱{{ number_format($goal->current_amount, 2) }}</div>
                            <div style="font-size:0.72rem;color:var(--muted);">of ₱{{ number_format($goal->target_amount, 2) }}</div>
                        </div>
                    </div>
                    @php $pct = $goal->target_amount > 0 ? min(100, ($goal->current_amount / $goal->target_amount) * 100) : 0; @endphp
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width:{{ $pct }}%"></div>
                    </div>
                    <div style="font-size:0.72rem;color:var(--muted);margin-top:0.25rem;">{{ number_format($pct, 0) }}% complete</div>
                </div>
                @endforeach
                <a href="{{ route('savings.index') }}" style="font-size:0.8rem;color:var(--gold);text-decoration:none;font-weight:600;padding-bottom:0.25rem;">View all goals →</a>
            </div>
        @endif
    </div>

</div>

{{-- Quick Actions --}}
<div style="margin-top:1.25rem;">
    <div style="font-size:0.75rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.85rem;">Quick Actions</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.75rem;">
        @foreach([
            ['route' => 'transfer.index', 'icon' => '↗', 'label' => 'Send Money'],
            ['route' => 'savings.create', 'icon' => '◎', 'label' => 'New Goal'],
            ['route' => 'load.index',     'icon' => '▤', 'label' => 'Buy Load'],
            ['route' => 'bills.index',    'icon' => '◻', 'label' => 'Pay Bills'],
        ] as $action)
        <a href="{{ route($action['route']) }}" style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;padding:1.1rem;background:var(--white);border:1px solid var(--border);border-radius:10px;text-decoration:none;transition:border-color 0.15s,transform 0.15s;"
           onmouseover="this.style.borderColor='var(--gold)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
            <span style="font-size:1.4rem;">{{ $action['icon'] }}</span>
            <span style="font-size:0.8rem;font-weight:500;color:var(--navy);">{{ $action['label'] }}</span>
        </a>
        @endforeach
    </div>
</div>
@endsection