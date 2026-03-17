@extends('layouts.app')
@section('title', 'Buy Load')
@section('content')
<div style="max-width:620px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Buy Load</span>
            <div style="font-size:0.8rem;color:var(--muted);">Balance: <strong style="color:var(--navy);">₱{{ number_format($wallet->balance, 2) }}</strong></div>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('load.buy') }}" id="loadForm">
                @csrf

                <div class="form-group">
                    <label class="form-label">Mobile Number</label>
                    <input class="form-input {{ $errors->has('mobile_number') ? 'is-invalid' : '' }}"
                        type="text" name="mobile_number" id="mobile_number"
                        value="{{ old('mobile_number') }}" placeholder="09171234567"
                        maxlength="11" required>
                    @error('mobile_number')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Network</label>
                    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:0.5rem;margin-top:0.1rem;">
                        @foreach(array_keys($networks) as $net)
                        <label style="cursor:pointer;">
                            <input type="radio" name="network" value="{{ $net }}"
                                {{ old('network') == $net ? 'checked' : '' }}
                                style="display:none;" onchange="updatePromos('{{ $net }}')">
                            <div class="network-btn" data-net="{{ $net }}"
                                style="padding:0.6rem;border:1.5px solid var(--border);border-radius:8px;text-align:center;font-size:0.8rem;font-weight:600;color:var(--muted);transition:all 0.15s;">
                                {{ $net }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('network')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" id="promoSection" style="display:none;">
                    <label class="form-label">Select Promo</label>
                    <div id="promoList" style="display:flex;flex-direction:column;gap:0.5rem;"></div>
                    <input type="hidden" name="promo_code" id="promo_code">
                    <input type="hidden" name="amount" id="amount">
                </div>

                {{-- Selected promo summary --}}
                <div id="promoSummary" style="display:none;background:var(--cream);border-radius:8px;padding:0.9rem 1rem;margin-bottom:1rem;font-size:0.875rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--muted);">Promo</span>
                        <span id="summaryPromo" style="font-weight:600;color:var(--navy);"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:0.4rem;">
                        <span style="color:var(--muted);">Amount</span>
                        <span id="summaryAmount" style="font-weight:600;color:var(--navy);"></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn" style="width:100%;justify-content:center;padding:0.88rem;" disabled>
                    Buy Load
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const networks = @json($networks);

function updatePromos(network) {
    // Highlight selected network
    document.querySelectorAll('.network-btn').forEach(btn => {
        btn.style.borderColor = 'var(--border)';
        btn.style.color = 'var(--muted)';
        btn.style.background = '';
    });
    const selected = document.querySelector(`.network-btn[data-net="${network}"]`);
    if (selected) {
        selected.style.borderColor = 'var(--gold)';
        selected.style.color = 'var(--navy)';
        selected.style.background = 'rgba(201,168,76,0.08)';
    }

    const promos = networks[network] || [];
    const promoList = document.getElementById('promoList');
    promoList.innerHTML = '';

    promos.forEach(p => {
        const div = document.createElement('div');
        div.innerHTML = `
            <label style="cursor:pointer;display:block;">
                <input type="radio" name="_promo_sel" value="${p.code}" style="display:none;"
                    onchange="selectPromo('${p.code}','${p.label}',${p.amount})">
                <div class="promo-item" data-code="${p.code}"
                    style="padding:0.85rem 1rem;border:1.5px solid var(--border);border-radius:8px;transition:all 0.15s;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:0.875rem;font-weight:600;color:var(--navy);">${p.label}</div>
                        <div style="font-size:0.75rem;color:var(--muted);margin-top:0.15rem;">${p.desc}</div>
                    </div>
                    <div style="font-weight:700;color:var(--navy);font-size:0.925rem;">₱${p.amount.toFixed(2)}</div>
                </div>
            </label>`;
        promoList.appendChild(div);
    });

    document.getElementById('promoSection').style.display = 'block';
    document.getElementById('promoSummary').style.display = 'none';
    document.getElementById('submitBtn').disabled = true;
}

function selectPromo(code, label, amount) {
    document.getElementById('promo_code').value = code;
    document.getElementById('amount').value = amount;
    document.getElementById('summaryPromo').textContent = label;
    document.getElementById('summaryAmount').textContent = '₱' + amount.toFixed(2);
    document.getElementById('promoSummary').style.display = 'block';
    document.getElementById('submitBtn').disabled = false;

    document.querySelectorAll('.promo-item').forEach(item => {
        item.style.borderColor = 'var(--border)';
        item.style.background = '';
    });
    const sel = document.querySelector(`.promo-item[data-code="${code}"]`);
    if (sel) { sel.style.borderColor = 'var(--gold)'; sel.style.background = 'rgba(201,168,76,0.06)'; }
}

// Restore on old() values
@if(old('network'))
    document.addEventListener('DOMContentLoaded', () => updatePromos('{{ old("network") }}'));
@endif
</script>
@endpush
@endsection