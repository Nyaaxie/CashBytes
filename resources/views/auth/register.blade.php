<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CashBytes — Create Account</title>
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
        .steps { display: flex; flex-direction: column; gap: 1rem; position: relative; z-index: 1; }
        .step { display: flex; align-items: flex-start; gap: 0.85rem; }
        .step-num {
            width: 24px; height: 24px; background: rgba(201,168,76,0.15);
            border: 1px solid rgba(201,168,76,0.3); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem; font-weight: 600; color: var(--gold); flex-shrink: 0; margin-top: 1px;
        }
        .step-text { font-size: 0.85rem; color: rgba(255,255,255,0.6); line-height: 1.5; }
        .step-text strong { display: block; color: rgba(255,255,255,0.85); font-weight: 500; margin-bottom: 0.1rem; }
        .panel-right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem 2.5rem; overflow-y: auto; }
        .auth-box { width: 100%; max-width: 420px; animation: fadeUp 0.6s ease both; padding: 0.5rem 0; }
        .auth-header { margin-bottom: 1.75rem; }
        .auth-header h2 { font-family: 'DM Serif Display', serif; font-size: 2rem; color: var(--navy); letter-spacing: -0.03em; margin-bottom: 0.35rem; }
        .auth-header p { color: var(--muted); font-size: 0.875rem; font-weight: 300; }
        .form-group { margin-bottom: 1.1rem; }
        .form-row { display: flex; gap: 0.75rem; }
        .form-row .form-group { flex: 1; }
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
        .alert { padding: 0.78rem 1rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.25rem; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: var(--error); }
        .btn-submit {
            width: 100%; padding: 0.88rem; background: var(--navy); color: var(--white);
            border: none; border-radius: 8px; font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem; font-weight: 600; cursor: pointer; letter-spacing: 0.01em;
            transition: background 0.18s, transform 0.12s; margin-top: 0.4rem;
        }
        .btn-submit:hover { background: var(--navy-mid); }
        .btn-submit:active { transform: scale(0.99); }
        .auth-switch { text-align: center; margin-top: 1.4rem; font-size: 0.875rem; color: var(--muted); }
        .auth-switch a { color: var(--gold); text-decoration: none; font-weight: 600; }
        .auth-switch a:hover { text-decoration: underline; }
        .hint { font-size: 0.75rem; color: var(--muted); margin-top: 0.3rem; }
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
            <h1>Get started<br><em>today.</em></h1>
            <p>Join thousands of Filipinos managing their money smarter with CashBytes.</p>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text"><strong>Create your account</strong>Fill in your details below</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text"><strong>Your wallet is ready</strong>Automatically created on signup</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text"><strong>Start transacting</strong>Transfer, save, load, and pay bills</div>
            </div>
        </div>
    </div>

    <div class="panel-right">
        <div class="auth-box">
            <div class="auth-header">
                <h2>Create account</h2>
                <p>Start managing your money with CashBytes</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Juan dela Cruz" autocomplete="name" required>
                    @error('name')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="contact_no">Mobile Number</label>
                    <input class="form-input {{ $errors->has('contact_no') ? 'is-invalid' : '' }}"
                        type="tel" inputmode="numeric" pattern="09[0-9]{9}" id="contact_no" name="contact_no" value="{{ old('contact_no') }}"
                        placeholder="09171234567" minlength="11" maxlength="11" required>
                    @error('contact_no')
                        <div class="invalid-msg">{{ $message }}</div>
                    @else
                        <div class="hint">PH mobile number starting with 09</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        type="email" id="email" name="email" value="{{ old('email') }}"
                        placeholder="juan@email.com" autocomplete="email" required>
                    @error('email')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                            type="password" id="password" name="password"
                            placeholder="••••••••" autocomplete="new-password" required>
                        @error('password')<div class="invalid-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm</label>
                        <input class="form-input"
                            type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="••••••••" autocomplete="new-password" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Create Account</button>
            </form>

            <div class="auth-switch">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </div>
        </div>
    </div>
</body>
</html>