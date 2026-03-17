@extends('layouts.app')
@section('title', 'Savings Goals')
@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <div style="font-size:0.78rem;color:var(--muted);">{{ $goals->count() }} goal(s) total</div>
    </div>
    <a href="{{ route('savings.create') }}" class="btn btn-primary">+ New Goal</a>
</div>

@if($goals->isEmpty())
    <div class="card">
        <div class="empty-state" style="padding:4rem 1rem;">
            <div class="empty-state-icon">◎</div>
            <h3>No savings goals yet</h3>
            <p>Create a goal for travel, tuition, shopping, or anything you're working towards.</p>
            <a href="{{ route('savings.create') }}" class="btn btn-primary" style="margin-top:1.25rem;">Create First Goal</a>
        </div>
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
        @foreach($goals as $goal)
        <div class="card" style="transition:transform 0.15s,box-shadow 0.15s;"
             onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(15,31,61,0.08)'"
             onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                    <div>
                        <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:0.25rem;">{{ $goal->category }}</div>
                        <div style="font-family:'DM Serif Display',serif;font-size:1.1rem;color:var(--navy);letter-spacing:-0.02em;">{{ $goal->name }}</div>
                    </div>
                    <span class="badge badge-{{ $goal->status === 'completed' ? 'success' : ($goal->status === 'cancelled' ? 'error' : 'warning') }}">
                        {{ $goal->status }}
                    </span>
                </div>

                <div style="margin-bottom:0.75rem;">
                    <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:0.4rem;">
                        <span style="color:var(--muted);">Progress</span>
                        <span style="font-weight:600;color:var(--navy);">{{ $goal->progress }}%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width:{{ $goal->progress }}%"></div>
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:0.825rem;margin-bottom:1rem;">
                    <div>
                        <div style="color:var(--muted);font-size:0.72rem;">Saved</div>
                        <div style="font-weight:600;color:var(--navy);">₱{{ number_format($goal->current_amount, 2) }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="color:var(--muted);font-size:0.72rem;">Target</div>
                        <div style="font-weight:600;color:var(--navy);">₱{{ number_format($goal->target_amount, 2) }}</div>
                    </div>
                </div>

                @if($goal->target_date)
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:1rem;">
                    Target date: {{ \Carbon\Carbon::parse($goal->target_date)->format('M d, Y') }}
                </div>
                @endif

                {{-- Actions --}}
                <div style="display:flex;gap:0.5rem;">
                    <a href="{{ route('savings.show', $goal->id) }}"
                        class="btn btn-outline btn-sm" style="flex:1;justify-content:center;">
                        {{ $goal->status === 'active' ? 'View Details' : 'View Details' }}
                    </a>

                    {{-- Delete button --}}
                    <form method="POST" action="{{ route('savings.destroy', $goal->id) }}"
                        onsubmit="return confirmDelete('{{ addslashes($goal->name) }}', {{ $goal->current_amount }})">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            style="height:100%;padding:0 0.75rem;background:transparent;border:1px solid #fecaca;border-radius:8px;color:#dc2626;cursor:pointer;font-size:0.8rem;transition:all 0.15s;"
                            onmouseover="this.style.background='#fef2f2'"
                            onmouseout="this.style.background='transparent'"
                            title="Delete goal">✕</button>
                    </form>
                </div>

            </div>
        </div>
        @endforeach
    </div>
@endif

<script>
function confirmDelete(name, amount) {
    if (amount > 0) {
        return confirm('Are you sure you want to delete "' + name + '"?\n\n₱' + parseFloat(amount).toLocaleString('en-PH', {minimumFractionDigits:2}) + ' will be returned to your wallet.');
    }
    return confirm('Delete "' + name + '"? This cannot be undone.');
}
</script>

@endsection