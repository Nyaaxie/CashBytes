@extends('layouts.app')
@section('title', 'Load Receipt')
@section('content')
<div class="receipt-card">
    <div class="card">
        <div class="card-body" style="padding:2rem 1.75rem;">
            <div class="receipt-icon">▤</div>
            <div class="receipt-amount">₱{{ number_format($transaction->amount, 2) }}</div>
            <div class="receipt-label">Load Sent Successfully</div>
            <div class="receipt-details">
                @if($loadDetails)
                <div class="receipt-row">
                    <span class="receipt-row-label">Mobile Number</span>
                    <span class="receipt-row-value">{{ $loadDetails->mobile_number }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Network</span>
                    <span class="receipt-row-value">{{ $loadDetails->network }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Promo</span>
                    <span class="receipt-row-value">{{ $loadDetails->promo_code }}</span>
                </div>
                @endif
                <div class="receipt-row">
                    <span class="receipt-row-label">Amount Charged</span>
                    <span class="receipt-row-value txn-debit">−₱{{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Remaining Balance</span>
                    <span class="receipt-row-value">₱{{ number_format($wallet->balance, 2) }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Reference No.</span>
                    <span class="receipt-row-value receipt-ref">{{ $transaction->reference_no }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Date & Time</span>
                    <span class="receipt-row-value">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y g:i A') }}</span>
                </div>
            </div>
            <div class="receipt-actions">
                <a href="{{ route('load.index') }}" class="btn btn-outline">Buy Again</a>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Done</a>
            </div>
        </div>
    </div>
</div>
@endsection