@extends('layouts.app')
@section('title', 'Transactions')
@section('content')

{{-- Summary --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="card" style="border-left:3px solid var(--credit);">
        <div class="card-body" style="padding:1rem 1.25rem;">
            <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.3rem;">Total Received</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.4rem;color:var(--navy);">₱{{ number_format($totalCredits, 2) }}</div>
        </div>
    </div>
    <div class="card" style="border-left:3px solid var(--debit);">
        <div class="card-body" style="padding:1rem 1.25rem;">
            <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.3rem;">Total Spent</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.4rem;color:var(--navy);">₱{{ number_format($totalDebits, 2) }}</div>
        </div>
    </div>
    <div class="card" style="border-left:3px solid var(--gold);">
        <div class="card-body" style="padding:1rem 1.25rem;">
            <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.3rem;">Current Balance</div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.4rem;color:var(--navy);">₱{{ number_format($wallet->balance, 2) }}</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('transactions.index') }}" style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-bottom:1.25rem;">
    <input class="form-input" type="text" name="search" value="{{ request('search') }}"
        placeholder="Search reference or description..." style="max-width:260px;">
    <select class="form-select" name="type" style="max-width:150px;">
        <option value="">All Types</option>
        <option value="transfer"      {{ request('type') == 'transfer'      ? 'selected' : '' }}>Transfer</option>
        <option value="bank_transfer" {{ request('type') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
        <option value="save"          {{ request('type') == 'save'          ? 'selected' : '' }}>Savings</option>
        <option value="load"          {{ request('type') == 'load'          ? 'selected' : '' }}>Load</option>
        <option value="bill_payment"  {{ request('type') == 'bill_payment'  ? 'selected' : '' }}>Bills</option>
    </select>
    <select class="form-select" name="status" style="max-width:140px;">
        <option value="">All Status</option>
        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
        <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
        <option value="failed"    {{ request('status') == 'failed'    ? 'selected' : '' }}>Failed</option>
    </select>
    <input class="form-input" type="date" name="date_from" value="{{ request('date_from') }}" style="max-width:155px;">
    <input class="form-input" type="date" name="date_to"   value="{{ request('date_to') }}"   style="max-width:155px;">
    <button type="submit" class="btn btn-outline">Filter</button>
    @if(request()->hasAny(['search','type','status','date_from','date_to']))
        <a href="{{ route('transactions.index') }}" class="btn btn-outline">Clear</a>
    @endif
</form>

{{-- Table --}}
<div class="card">
    @if($transactions->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">≡</div>
            <h3>No transactions found</h3>
            <p>Try adjusting your filters</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);">
                        <th style="text-align:left;padding:0.85rem 1.25rem;font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);">Description</th>
                        <th style="text-align:left;padding:0.85rem 1rem;font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);">Type</th>
                        <th style="text-align:left;padding:0.85rem 1rem;font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);">Reference</th>
                        <th style="text-align:left;padding:0.85rem 1rem;font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);">Date</th>
                        <th style="text-align:right;padding:0.85rem 1.25rem;font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);">Amount</th>
                        <th style="text-align:center;padding:0.85rem 1rem;font-size:0.72rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $txn)
                    <tr style="border-bottom:1px solid var(--border);"
                        onmouseover="this.style.background='var(--cream)'"
                        onmouseout="this.style.background=''">

                        {{-- Description --}}
                        <td style="padding:0.9rem 1.25rem;">
                            <div style="font-size:0.875rem;font-weight:500;color:var(--navy);">{{ $txn->label }}</div>
                            @if($txn->sublabel)
                                <div style="font-size:0.75rem;color:var(--muted);margin-top:0.15rem;">{{ $txn->sublabel }}</div>
                            @endif
                        </td>

                        {{-- Type badge --}}
                        <td style="padding:0.9rem 1rem;">
                            @php
                                $typeLabels = [
                                    'transfer'      => 'Transfer',
                                    'bank_transfer' => 'Bank',
                                    'save'          => 'Savings',
                                    'load'          => 'Load',
                                    'bill_payment'  => 'Bills',
                                ];
                            @endphp
                            <span class="badge badge-muted">{{ $typeLabels[$txn->type] ?? ucfirst($txn->type) }}</span>
                        </td>

                        <td style="padding:0.9rem 1rem;font-size:0.75rem;font-family:monospace;color:var(--muted);">{{ $txn->reference_no }}</td>
                        <td style="padding:0.9rem 1rem;font-size:0.8rem;color:var(--muted);">{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, Y') }}</td>

                        {{-- Amount --}}
                        <td style="padding:0.9rem 1.25rem;text-align:right;">
                            <span class="{{ $txn->direction === 'debit' ? 'txn-debit' : 'txn-credit' }}">
                                {{ $txn->direction === 'debit' ? '−' : '+' }}₱{{ number_format($txn->amount, 2) }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td style="padding:0.9rem 1rem;text-align:center;">
                            <span class="badge badge-{{ $txn->status === 'completed' ? 'success' : ($txn->status === 'pending' ? 'warning' : 'error') }}">
                                {{ $txn->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($transactions->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="font-size:0.8rem;color:var(--muted);">
                Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
            </div>
            <div style="display:flex;align-items:center;gap:0.3rem;">
                @php $btnBase = "display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 0.6rem;border-radius:7px;font-size:0.82rem;font-family:'DM Sans',sans-serif;font-weight:500;text-decoration:none;border:1px solid var(--border);transition:all 0.15s;"; @endphp

                @if($transactions->onFirstPage())
                    <span style="{{ $btnBase }} background:var(--cream);color:#ccc;cursor:not-allowed;">‹</span>
                @else
                    <a href="{{ $transactions->previousPageUrl() }}"
                       style="{{ $btnBase }} background:var(--white);color:var(--navy);"
                       onmouseover="this.style.background='var(--navy)';this.style.color='white';this.style.borderColor='var(--navy)'"
                       onmouseout="this.style.background='var(--white)';this.style.color='var(--navy)';this.style.borderColor='var(--border)'">‹</a>
                @endif

                @foreach($transactions->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                    @if($page == $transactions->currentPage())
                        <span style="{{ $btnBase }} background:var(--navy);color:white;border-color:var(--navy);font-weight:700;cursor:default;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           style="{{ $btnBase }} background:var(--white);color:var(--navy);"
                           onmouseover="this.style.background='var(--navy)';this.style.color='white';this.style.borderColor='var(--navy)'"
                           onmouseout="this.style.background='var(--white)';this.style.color='var(--navy)';this.style.borderColor='var(--border)'">{{ $page }}</a>
                    @endif
                @endforeach

                @if($transactions->hasMorePages())
                    <a href="{{ $transactions->nextPageUrl() }}"
                       style="{{ $btnBase }} background:var(--white);color:var(--navy);"
                       onmouseover="this.style.background='var(--navy)';this.style.color='white';this.style.borderColor='var(--navy)'"
                       onmouseout="this.style.background='var(--white)';this.style.color='var(--navy)';this.style.borderColor='var(--border)'">›</a>
                @else
                    <span style="{{ $btnBase }} background:var(--cream);color:#ccc;cursor:not-allowed;">›</span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

@endsection