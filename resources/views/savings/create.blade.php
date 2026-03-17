@extends('layouts.app')
@section('title', 'New Savings Goal')
@section('content')
<div style="max-width:520px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Create Savings Goal</span></div>
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('savings.store') }}">
                @csrf

                {{-- Goal Name --}}
                <div class="form-group">
                    <label class="form-label">Goal Name</label>
                    <input class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        type="text" name="name" value="{{ old('name') }}"
                        placeholder="e.g. Boracay Trip, Tuition Fee" required>
                    @error('name')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Category --}}
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category" id="category-select" required
                        onchange="toggleCustomCategory(this.value)">
                        <option value="">Select category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Custom Category Input (hidden by default) --}}
                <div class="form-group" id="custom-category-group"
                    style="display:{{ old('category') == 'Custom' ? 'block' : 'none' }};">
                    <label class="form-label">Custom Category Name</label>
                    <input class="form-input {{ $errors->has('custom_category') ? 'is-invalid' : '' }}"
                        type="text" name="custom_category"
                        id="custom-category-input"
                        value="{{ old('custom_category') }}"
                        placeholder="e.g. Wedding, Car Fund, Gadget..."
                        maxlength="50">
                    @error('custom_category')
                        <div class="invalid-msg">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">This will be saved as your goal's category.</div>
                </div>

                {{-- Target Amount --}}
                <div class="form-group">
                    <label class="form-label">Target Amount</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-weight:600;color:var(--muted);">₱</span>
                        <input class="form-input {{ $errors->has('target_amount') ? 'is-invalid' : '' }}"
                            type="number" name="target_amount" value="{{ old('target_amount') }}"
                            placeholder="0.00" min="1" step="0.01" style="padding-left:2rem;" required>
                    </div>
                    @error('target_amount')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Target Date --}}
                <div class="form-group">
                    <label class="form-label">
                        Target Date
                        <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted);">(optional)</span>
                    </label>
                    <input class="form-input" type="date" name="target_date"
                        value="{{ old('target_date') }}"
                        min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    @error('target_date')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:0.75rem;margin-top:0.5rem;">
                    <a href="{{ route('savings.index') }}" class="btn btn-outline" style="flex:1;justify-content:center;">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">Create Goal</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function toggleCustomCategory(value) {
    const group = document.getElementById('custom-category-group');
    const input = document.getElementById('custom-category-input');


    if (value === 'Custom') {
        group.style.display = 'block';
        input.required = true;
        input.focus();
    } else {
        group.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}

// On page load — restore state if validation failed and Custom was selected
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('category-select');
    if (select.value === 'Custom') {
        document.getElementById('custom-category-group').style.display = 'block';
        document.getElementById('custom-category-input').required = true;
    }
});
</script>
@endsection