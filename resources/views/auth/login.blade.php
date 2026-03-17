<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CashBytes — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy: #0f1f3d; --navy-mid: #182f5a; --gold: #c9a84c;
            --cream: #f7f5f0; --white: #ffffff; --muted: #6b7280;
            --border: #e5e2db; --error: #c0392b;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--navy); min-height: 100vh; display: flex; }
        .panel-left {
            width: 44%; background: var(--navy);
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 3rem 3.5rem; position: relative; overflow: hidden;
        }
        .panel-left::before {
            content: ''; position: absolute; top: -100px; right: -100px;
            width: 360px; height: 360px; border-radius: 50%;
            border: 1px solid rgba(201,168,76,0.12); pointer-events: none;
        }
        .panel-left::after {
            content: ''; position: absolute; bottom: -80px; left: -80px;
            width: 280px; height: 280px; border-radius: 50%;
            border: 1px solid rgba(201,168,76,0.08); pointer-events: none;
        }
        .brand { display: flex; align-items: center; gap: 0.65rem; text-decoration: none; position: relative; z-index: 1; }
        .brand-icon {
            width: 38px; height: 38px; background: var(--gold); border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'DM Serif Display', serif; font-size: 1.1rem; color: var(--navy);
        }
        .brand-name { font-family: 'DM Serif Display', serif; font-size: 1.4rem; color: var(--white); letter-spacing: -0.02em; }
        .panel-hero { position: relative; z-index: 1; }
        .panel-hero h1 { font-family: 'DM Serif Display', serif; font-size: 2.6rem; color: var(--white); line-height: 1.12; letter-spacing: -0.03em; margin-bottom: 1rem; }
        .panel-hero h1 em { font-style: italic; color: var(--gold); }
        .panel-hero p { color: rgba(255,255,255,0.5); font-size: 0.9rem; line-height: 1.75; font-weight: 300; max-width: 300px; }
        .features { display: flex; flex-direction: column; gap: 0.8rem; position: relative; z-index: 1; }
        .feature { display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem; color: rgba(255,255,255,0.6); }
        .feature-dot { width: 5px; height: 5px; background: var(--gold); border-radius: 50%; flex-shrink: 0; }
        .panel-right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2.5rem; }
        .auth-box { width: 100%; max-width: 400px; animation: fadeUp 0.6s ease both; }
        .auth-header { margin-bottom: 2rem; }
        .auth-header h2 { font-family: 'DM Serif Display', serif; font-size: 2rem; color: var(--navy); letter-spacing: -0.03em; margin-bottom: 0.35rem; }
        .auth-header p { color: var(--muted); font-size: 0.875rem; font-weight: 300; }
        .form-group { margin-bottom: 1.15rem; }
        .form-label { display: block; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--navy); margin-bottom: 0.4rem; }
        .form-input {
            width: 100%; padding: 0.78rem 1rem; border: 1.5px solid var(--border); border-radius: 8px;
            font-family: 'DM Sans', sans-serif; font-size: 0.925rem; color: var(--navy);
            background: var(--white); outline: none; transition: border-color 0.18s, box-shadow 0.18s;
        }
        .form-input::placeholder { color: #bbb; }
        .form-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.13); }
        .form-input.is-invalid { border-color: var(--error); }
        .invalid-msg { color: var(--error); font-size: 0.78rem; margin-top: 0.3rem; }
        .remember-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; }
        .remember-row input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--gold); cursor: pointer; }
        .remember-row label { font-size: 0.875rem; color: var(--muted); cursor: pointer; font-weight: 400; }
        .alert { padding: 0.78rem 1rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.25rem; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: var(--error); }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #27ae60; }
        .btn-submit {
            width: 100%; padding: 0.88rem; background: var(--navy); color: var(--white);
            border: none; border-radius: 8px; font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem; font-weight: 600; cursor: pointer; letter-spacing: 0.01em;
            transition: background 0.18s, transform 0.12s;
        }
        .btn-submit:hover { background: var(--navy-mid); }
        .btn-submit:active { transform: scale(0.99); }
        .auth-switch { text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: var(--muted); }
        .auth-switch a { color: var(--gold); text-decoration: none; font-weight: 600; }
        .auth-switch a:hover { text-decoration: underline; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .panel-left { display: none; } .panel-right { padding: 2rem 1.5rem; } }
    </style>
</head>
<body>
    <div class="panel-left">
        <a href="{{ route('welcome') }}" class="brand">
            <div class="brand-icon">C</div>
            <span class="brand-name">CashBytes</span>
        </a>
        <div class="panel-hero">
            <h1>Welcome<br><em>back.</em></h1>
            <p>Sign in to manage your wallet, send money, and stay on top of your finances.</p>
        </div>
        <div class="features">
            <div class="feature"><span class="feature-dot"></span>Instant fund transfers</div>
            <div class="feature"><span class="feature-dot"></span>Smart savings goals</div>
            <div class="feature"><span class="feature-dot"></span>Buy load & pay bills</div>
        </div>
    </div>

    <div class="panel-right">
        <div class="auth-box">
            <div class="auth-header">
                <h2>Sign in</h2>
                <p>Enter your credentials to access your account</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        type="email" id="email" name="email" value="{{ old('email') }}"
                        placeholder="juan@email.com" autocomplete="email" required>
                    @error('email')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        type="password" id="password" name="password"
                        placeholder="••••••••" autocomplete="current-password" required>
                    @error('password')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>
                <div class="remember-row">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <div class="auth-switch">
                Don't have an account? <a href="{{ route('register') }}">Create one</a>
            </div>
        </div>
    </div>
</body>
</html>