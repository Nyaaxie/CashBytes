<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CashBytes — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy: #0f1f3d; --navy-mid: #182f5a; --navy-light: #1e3a6e;
            --gold: #c9a84c; --gold-bg: rgba(201,168,76,0.08);
            --cream: #f7f5f0; --white: #ffffff;
            --muted: #6b7280; --border: #e5e2db;
            --sidebar-w: 240px;
            --success: #27ae60; --error: #c0392b;
            --debit: #e74c3c; --credit: #27ae60;
        }
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--navy); display: flex; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w); background: var(--navy);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0;
            z-index: 50; overflow-y: auto;
        }
        .sidebar-brand {
            padding: 1.75rem 1.5rem 1.25rem;
            display: flex; align-items: center; gap: 0.6rem;
            text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 0.5rem;
        }
        .brand-icon {
            width: 34px; height: 34px; background: var(--gold); border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'DM Serif Display', serif; font-size: 1rem; color: var(--navy); flex-shrink: 0;
        }
        .brand-name { font-family: 'DM Serif Display', serif; font-size: 1.25rem; color: var(--white); letter-spacing: -0.02em; }
        .sidebar-nav { flex: 1; padding: 0.5rem 0.75rem; display: flex; flex-direction: column; gap: 0.2rem; }
        .nav-section-label {
            font-size: 0.65rem; font-weight: 600; letter-spacing: 0.1em;
            text-transform: uppercase; color: rgba(255,255,255,0.3);
            padding: 0.75rem 0.75rem 0.35rem;
        }
        .nav-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.65rem 0.85rem; border-radius: 8px;
            text-decoration: none; font-size: 0.875rem; font-weight: 500;
            color: rgba(255,255,255,0.6);
            transition: background 0.15s, color 0.15s;
        }
        .nav-link:hover { background: rgba(255,255,255,0.07); color: var(--white); }
        .nav-link.active { background: rgba(201,168,76,0.15); color: var(--gold); }
        .nav-link .nav-icon { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }
        .sidebar-footer { padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.07); }
        .sidebar-user { display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.75rem; }
        .user-avatar {
            width: 34px; height: 34px; background: var(--gold-bg);
            border: 1.5px solid rgba(201,168,76,0.3); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 600; color: var(--gold); flex-shrink: 0;
        }
        .user-info { overflow: hidden; }
        .user-name { font-size: 0.825rem; font-weight: 600; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-email { font-size: 0.72rem; color: rgba(255,255,255,0.4); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .btn-logout {
            width: 100%; padding: 0.55rem; background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 7px;
            color: rgba(255,255,255,0.6); font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem; font-weight: 500; cursor: pointer;
            transition: background 0.15s, color 0.15s; text-align: center;
        }
        .btn-logout:hover { background: rgba(231,76,60,0.15); color: #e74c3c; border-color: rgba(231,76,60,0.2); }

        /* ── MAIN ── */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── TOPBAR ── */
        .topbar {
            background: var(--white); border-bottom: 1px solid var(--border);
            padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 40;
        }
        .topbar-title { font-family: 'DM Serif Display', serif; font-size: 1.35rem; color: var(--navy); letter-spacing: -0.02em; }
        .topbar-right { display: flex; align-items: center; gap: 1rem; }
        .wallet-badge {
            display: flex; align-items: center; gap: 0.5rem;
            background: var(--cream); border: 1px solid var(--border);
            border-radius: 8px; padding: 0.45rem 0.85rem;
        }
        .wallet-badge-label { font-size: 0.72rem; color: var(--muted); font-weight: 500; }
        .wallet-badge-amount { font-family: 'DM Serif Display', serif; font-size: 1rem; color: var(--navy); letter-spacing: -0.02em; }

        /* ── PAGE CONTENT ── */
        .page-content { flex: 1; padding: 2rem; }

        /* ── REUSABLE COMPONENTS ── */
        .card {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 12px; overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-title { font-family: 'DM Serif Display', serif; font-size: 1.05rem; color: var(--navy); letter-spacing: -0.02em; }
        .card-body { padding: 1.5rem; }

        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1.25rem; border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 0.875rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: background 0.15s, transform 0.1s; border: none; }
        .btn:active { transform: scale(0.98); }
        .btn-primary { background: var(--navy); color: var(--white); }
        .btn-primary:hover { background: var(--navy-mid); }
        .btn-gold { background: var(--gold); color: var(--navy); }
        .btn-gold:hover { background: #b8943d; }
        .btn-outline { background: transparent; color: var(--navy); border: 1.5px solid var(--border); }
        .btn-outline:hover { border-color: var(--navy); background: rgba(15,31,61,0.03); }
        .btn-sm { padding: 0.45rem 0.9rem; font-size: 0.8rem; }

        .form-group { margin-bottom: 1.15rem; }
        .form-label { display: block; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--navy); margin-bottom: 0.4rem; }
        .form-input, .form-select {
            width: 100%; padding: 0.75rem 1rem; border: 1.5px solid var(--border); border-radius: 8px;
            font-family: 'DM Sans', sans-serif; font-size: 0.925rem; color: var(--navy);
            background: var(--white); outline: none; transition: border-color 0.18s, box-shadow 0.18s;
        }
        .form-input::placeholder { color: #bbb; }
        .form-input:focus, .form-select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.12); }
        .form-input.is-invalid { border-color: var(--error); }
        .invalid-msg { color: var(--error); font-size: 0.78rem; margin-top: 0.3rem; }
        .form-hint { font-size: 0.78rem; color: var(--muted); margin-top: 0.3rem; }
        .form-row { display: flex; gap: 1rem; }
        .form-row .form-group { flex: 1; }

        .alert { padding: 0.8rem 1rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1.25rem; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: var(--success); }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: var(--error); }
        .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }

        .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.6rem; border-radius: 100px; font-size: 0.72rem; font-weight: 600; }
        .badge-success  { background: #f0fdf4; color: var(--success); }
        .badge-error    { background: #fef2f2; color: var(--error); }
        .badge-warning  { background: #fffbeb; color: #d97706; }
        .badge-muted    { background: var(--cream); color: var(--muted); }

        .txn-debit  { color: var(--debit);  font-weight: 600; }
        .txn-credit { color: var(--credit); font-weight: 600; }

        /* Progress bar */
        .progress-bar-track { height: 6px; background: var(--cream); border-radius: 100px; overflow: hidden; }
        .progress-bar-fill  { height: 100%; background: var(--gold); border-radius: 100px; transition: width 0.4s ease; }

        /* Empty state */
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--muted); }
        .empty-state-icon { font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.4; }
        .empty-state h3 { font-size: 1rem; color: var(--navy); margin-bottom: 0.35rem; font-family: 'DM Serif Display', serif; }
        .empty-state p { font-size: 0.875rem; }

        /* Receipt */
        .receipt-card { max-width: 460px; margin: 0 auto; }
        .receipt-icon { width: 64px; height: 64px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1.25rem; }
        .receipt-amount { font-family: 'DM Serif Display', serif; font-size: 2.2rem; color: var(--navy); text-align: center; letter-spacing: -0.04em; margin-bottom: 0.25rem; }
        .receipt-label { text-align: center; font-size: 0.875rem; color: var(--muted); margin-bottom: 1.75rem; }
        .receipt-details { display: flex; flex-direction: column; gap: 0; }
        .receipt-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border); }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-row-label { font-size: 0.8rem; color: var(--muted); }
        .receipt-row-value { font-size: 0.875rem; font-weight: 500; color: var(--navy); text-align: right; }
        .receipt-ref { font-size: 0.75rem; font-family: monospace; letter-spacing: 0.04em; }
        .receipt-actions { display: flex; gap: 0.75rem; margin-top: 1.75rem; }
        .receipt-actions .btn { flex: 1; justify-content: center; }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.25s; }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .page-content { padding: 1.25rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon">C</div>
        <span class="brand-name">CashBytes</span>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon">⊞</span> Dashboard
        </a>

        <div class="nav-section-label">Modules</div>
        <a href="{{ route('transfer.index') }}"
           class="nav-link {{ request()->routeIs('transfer.*') ? 'active' : '' }}">
            <span class="nav-icon">↗</span> Fund Transfer
        </a>
        <a href="{{ route('savings.index') }}"
           class="nav-link {{ request()->routeIs('savings.*') ? 'active' : '' }}">
            <span class="nav-icon">◎</span> Savings
        </a>
        <a href="{{ route('load.index') }}"
           class="nav-link {{ request()->routeIs('load.*') ? 'active' : '' }}">
            <span class="nav-icon">▤</span> Buy Load
        </a>
        <a href="{{ route('bills.index') }}"
           class="nav-link {{ request()->routeIs('bills.*') ? 'active' : '' }}">
            <span class="nav-icon">◻</span> Pay Bills
        </a>

        <div class="nav-section-label">Account</div>
        <a href="{{ route('transactions.index') }}"
           class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <span class="nav-icon">≡</span> Transactions
        </a>
        <a href="{{ route('profile.index') }}"
           class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <span class="nav-icon">○</span> Profile
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-email">{{ Auth::user()->email }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Sign Out</button>
        </form>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main">
    <header class="topbar">
        <div class="topbar-title">@yield('title', 'Dashboard')</div>
        <div class="topbar-right">
            <div class="wallet-badge">
                <span class="wallet-badge-label">Number: </span>
                <span class="wallet-badge-amount" style="font-family:'DM Sans',sans-serif;font-size:0.875rem;letter-spacing:0.01em;">{{ Auth::user()->contact_no }}</span>
            </div>
            <div style="width:1px;height:24px;background:var(--border);"></div>
            <div class="wallet-badge">
                <span class="wallet-badge-label">Balance</span>
                <span class="wallet-badge-amount">₱{{ number_format(Auth::user()->wallet?->balance ?? 0, 2) }}</span>
            </div>
        </div>
    </header>

    <div class="page-content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>