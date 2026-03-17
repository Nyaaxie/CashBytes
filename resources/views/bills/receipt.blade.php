@extends('layouts.app')
@section('title', 'Payment Receipt')
@section('content')
<div class="receipt-card">
    <div class="card">
        <div class="card-body" style="padding:2rem 1.75rem;">
            <div class="receipt-icon">◻</div>
            <div class="receipt-amount">₱{{ number_format($transaction->amount, 2) }}</div>
            <div class="receipt-label">Bill Payment Successful</div>
            <div class="receipt-details">
                @if($billDetails)
                <div class="receipt-row">
                    <span class="receipt-row-label">Biller</span>
                    <span class="receipt-row-value">{{ $billDetails->biller_name }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Category</span>
                    <span class="receipt-row-value">{{ $billDetails->biller_category }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Account No.</span>
                    <span class="receipt-row-value">{{ $billDetails->account_number }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-row-label">Confirmation No.</span>
                    <span class="receipt-row-value receipt-ref">{{ $billDetails->confirmation_no }}</span>
                </div>
                @endif
                <div class="receipt-row">
                    <span class="receipt-row-label">Amount Paid</span>
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
                <a href="{{ route('bills.index') }}" class="btn btn-outline">Pay Another</a>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Done</a>
            </div>
        </div>
    </div>
</div>
@endsection