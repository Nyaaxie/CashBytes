@extends('layouts.app')
@section('title', 'Savings Receipt')
@section('content')
<div class="receipt-card">
    <div class="card">
        <div class="card-body" style="padding:2rem 1.75rem;">
            <div class="receipt-icon">◎</div>
            <div class="receipt-amount">₱{{ number_format($transaction->amount, 2) }}</div>
            <div class="receipt-label">Added to Savings</div>
            <div class="receipt-details">
                <div class="receipt-row">
                    <span class="receipt-row-label">Description</span>
                    <span class="receipt-row-value">{{ $transaction->description }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Amount Saved</span>
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
                <a href="{{ route('savings.index') }}" class="btn btn-outline">View Goals</a>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Done</a>
            </div>
        </div>
    </div>
</div>
@endsection