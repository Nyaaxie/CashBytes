@extends('layouts.app')
@section('title', 'Pay Bills')
@section('content')

{{-- Search & Filter --}}
<form method="GET" action="{{ route('bills.index') }}" style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <input class="form-input" type="text" name="search" value="{{ request('search') }}"
        placeholder="Search biller..." style="max-width:240px;">
    <select class="form-select" name="category" style="max-width:180px;" onchange="this.form.submit()">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-outline">Filter</button>
    @if(request('search') || request('category'))
        <a href="{{ route('bills.index') }}" class="btn btn-outline">Clear</a>
    @endif
</form>

@if($grouped->isEmpty())
    <div class="card">
        <div class="empty-state"><div class="empty-state-icon">◻</div><h3>No billers found</h3><p>Try a different search</p></div>
    </div>
@else
    @foreach($grouped as $category => $billers)
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:0.72rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);margin-bottom:0.65rem;padding-left:0.25rem;">{{ $category }}</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.75rem;">
            @foreach($billers as $biller)
            <a href="{{ route('bills.pay', $biller->id) }}"
               style="display:flex;align-items:center;gap:0.85rem;padding:1rem 1.1rem;background:var(--white);border:1.5px solid var(--border);border-radius:10px;text-decoration:none;transition:border-color 0.15s,transform 0.15s;"
               onmouseover="this.style.borderColor='var(--gold)';this.style.transform='translateY(-2px)'"
               onmouseout="this.style.borderColor='var(--border)';this.style.transform=''">
                <div style="width:38px;height:38px;background:var(--cream);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                    @if($category === 'Electric') ⚡
                    @elseif($category === 'Water') 💧
                    @elseif($category === 'Internet') 📡
                    @elseif($category === 'Government') 🏛
                    @elseif($category === 'Credit Card') 💳
                    @else 📄 @endif
                </div>
                <div>
                    <div style="font-size:0.875rem;font-weight:600;color:var(--navy);">{{ $biller->name }}</div>
                    <div style="font-size:0.75rem;color:var(--muted);">{{ $category }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
@endif
@endsection