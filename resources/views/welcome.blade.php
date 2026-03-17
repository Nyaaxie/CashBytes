<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CashBytes — Your Money, Simplified</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:        #0f1f3d;
            --navy-deep:   #091528;
            --gold:        #c9a84c;
            --gold-light:  #e8c96a;
            --cream:       #f7f5f0;
            --white:       #ffffff;
            --muted:       #6b7280;
            --border:      #e5e2db;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--navy);
            overflow-x: hidden;
        }

        /* ── NAV ── */
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 5%;
            background: rgba(247,245,240,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            display: flex; align-items: center; gap: 0.6rem;
            text-decoration: none;
        }

        .brand-icon {
            width: 36px; height: 36px;
            background: var(--navy);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'DM Serif Display', serif;
            font-size: 1rem; color: var(--gold);
        }

        .brand-name {
            font-family: 'DM Serif Display', serif;
            font-size: 1.3rem;
            color: var(--navy);
            letter-spacing: -0.02em;
        }

        .nav-links {
            display: flex; align-items: center; gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            transition: color 0.2s;
        }

        .nav-links a:hover { color: var(--navy); }

        .nav-cta {
            display: flex; gap: 0.75rem;
        }

        .btn-ghost {
            padding: 0.55rem 1.25rem;
            border: 1.5px solid var(--border);
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--navy);
            background: transparent;
            cursor: pointer;
            text-decoration: none;
            transition: border-color 0.2s, background 0.2s;
        }

        .btn-ghost:hover {
            border-color: var(--navy);
            background: rgba(15,31,61,0.04);
        }

        .btn-solid {
            padding: 0.55rem 1.25rem;
            border: 1.5px solid var(--navy);
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--white);
            background: var(--navy);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-solid:hover { background: #182f5a; }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 8rem 5% 5rem;
            position: relative;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 70% 50%, rgba(201,168,76,0.06) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 90% 80%, rgba(15,31,61,0.04) 0%, transparent 60%);
        }

        /* Decorative grid lines */
        .hero-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(15,31,61,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15,31,61,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 100%);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 620px;
            animation: fadeUp 0.8s ease both;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            background: rgba(201,168,76,0.12);
            border: 1px solid rgba(201,168,76,0.3);
            border-radius: 100px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #8a6a1f;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .hero-badge::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--gold);
            border-radius: 50%;
        }

        .hero h1 {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(2.8rem, 5vw, 4.2rem);
            line-height: 1.1;
            letter-spacing: -0.04em;
            color: var(--navy);
            margin-bottom: 1.25rem;
        }

        .hero h1 em {
            font-style: italic;
            color: var(--gold);
        }

        .hero p {
            font-size: 1.05rem;
            line-height: 1.75;
            color: var(--muted);
            max-width: 480px;
            margin-bottom: 2.25rem;
            font-weight: 300;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 0.85rem 2rem;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
            letter-spacing: 0.01em;
        }

        .btn-hero:hover { background: #182f5a; transform: translateY(-1px); }

        .btn-hero-outline {
            padding: 0.85rem 2rem;
            background: transparent;
            color: var(--navy);
            border: 1.5px solid var(--navy);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-hero-outline:hover { background: rgba(15,31,61,0.05); }

        /* Floating card */
        .hero-card {
            position: absolute;
            right: 8%;
            top: 50%;
            transform: translateY(-50%);
            width: 300px;
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            box-shadow: 0 20px 60px rgba(15,31,61,0.08);
            animation: floatCard 0.9s ease 0.2s both;
        }

        .hero-card-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.4rem;
        }

        .hero-card-balance {
            font-family: 'DM Serif Display', serif;
            font-size: 2rem;
            color: var(--navy);
            letter-spacing: -0.03em;
            margin-bottom: 1.25rem;
        }

        .hero-card-balance span {
            font-size: 1rem;
            color: var(--muted);
            font-family: 'DM Sans', sans-serif;
        }

        .hero-card-divider {
            height: 1px;
            background: var(--border);
            margin-bottom: 1.25rem;
        }

        .hero-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .hero-card-row:last-child { margin-bottom: 0; }

        .txn-label {
            font-size: 0.82rem;
            color: var(--muted);
        }

        .txn-label strong {
            display: block;
            font-size: 0.88rem;
            color: var(--navy);
            font-weight: 500;
        }

        .txn-amount { font-size: 0.88rem; font-weight: 600; }
        .txn-amount.debit  { color: #c0392b; }
        .txn-amount.credit { color: #27ae60; }

        /* ── MODULES ── */
        .modules {
            padding: 6rem 5%;
            background: var(--white);
            border-top: 1px solid var(--border);
        }

        .section-label {
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 0.75rem;
        }

        .section-title {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            color: var(--navy);
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
        }

        .section-sub {
            color: var(--muted);
            font-size: 0.95rem;
            max-width: 460px;
            line-height: 1.7;
            margin-bottom: 3rem;
            font-weight: 300;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
        }

        .module-card {
            padding: 1.75rem;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: var(--cream);
            transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
            cursor: default;
        }

        .module-card:hover {
            border-color: var(--gold);
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(201,168,76,0.1);
        }

        .module-icon {
            width: 44px; height: 44px;
            background: var(--navy);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.1rem;
            font-size: 1.2rem;
        }

        .module-card h3 {
            font-family: 'DM Serif Display', serif;
            font-size: 1.15rem;
            color: var(--navy);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .module-card p {
            font-size: 0.875rem;
            color: var(--muted);
            line-height: 1.6;
            font-weight: 300;
        }

        /* ── HOW IT WORKS ── */
        .how {
            padding: 6rem 5%;
            background: var(--navy);
            position: relative;
            overflow: hidden;
        }

        .how::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            border-radius: 50%;
            border: 1px solid rgba(201,168,76,0.1);
        }

        .how .section-title { color: var(--white); }
        .how .section-sub   { color: rgba(255,255,255,0.5); }
        .how .section-label { color: var(--gold); }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            position: relative;
            z-index: 1;
        }

        .step { display: flex; flex-direction: column; gap: 1rem; }

        .step-number {
            font-family: 'DM Serif Display', serif;
            font-size: 2.5rem;
            color: rgba(201,168,76,0.25);
            line-height: 1;
        }

        .step h4 {
            font-family: 'DM Serif Display', serif;
            font-size: 1.1rem;
            color: var(--white);
            letter-spacing: -0.02em;
        }

        .step p {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.5);
            line-height: 1.65;
            font-weight: 300;
        }

        /* ── FOOTER ── */
        footer {
            padding: 2rem 5%;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        footer p {
            font-size: 0.82rem;
            color: var(--muted);
        }

        footer a {
            font-size: 0.82rem;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        footer a:hover { color: var(--navy); }

        .footer-links { display: flex; gap: 1.5rem; }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes floatCard {
            from { opacity: 0; transform: translateY(calc(-50% + 20px)); }
            to   { opacity: 1; transform: translateY(-50%); }
        }

        @media (max-width: 900px) {
            .hero-card { display: none; }
            .hero { padding-top: 7rem; }
        }

        @media (max-width: 600px) {
            nav .nav-links { display: none; }
            .hero h1 { font-size: 2.4rem; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav>
    <a href="{{ route('welcome') }}" class="brand">
        <div class="brand-icon">C</div>
        <span class="brand-name">CashBytes</span>
    </a>

    <div class="nav-links">
        <a href="#modules">Features</a>
        <a href="#how">How it works</a>
    </div>

    <div class="nav-cta">
        <a href="{{ route('login') }}" class="btn-ghost">Sign In</a>
        <a href="{{ route('register') }}" class="btn-solid">Get Started</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>

    <div class="hero-content">
        <div class="hero-badge">Now available in the Philippines</div>
        <h1>Your money,<br><em>simplified.</em></h1>
        <p>Transfer funds, grow your savings, buy load, and pay bills — all from one secure and elegant platform.</p>
        <div class="hero-actions">
            <a href="{{ route('register') }}" class="btn-hero">Open an Account</a>
            <a href="{{ route('login') }}" class="btn-hero-outline">Sign In</a>
        </div>
    </div>

    <!-- Floating balance card -->
    <div class="hero-card">
        <div class="hero-card-label">Available Balance</div>
        <div class="hero-card-balance"><span>₱</span> 24,500.00</div>
        <div class="hero-card-divider"></div>
        <div class="hero-card-row">
            <div class="txn-label">
                <strong>Globe Load</strong>
                Today, 10:32 AM
            </div>
            <div class="txn-amount debit">−₱99</div>
        </div>
        <div class="hero-card-row">
            <div class="txn-label">
                <strong>Meralco Bill</strong>
                Yesterday
            </div>
            <div class="txn-amount debit">−₱1,540</div>
        </div>
        <div class="hero-card-row">
            <div class="txn-label">
                <strong>Received</strong>
                Mar 5, 2026
            </div>
            <div class="txn-amount credit">+₱3,000</div>
        </div>
    </div>
</section>

<!-- MODULES -->
<section class="modules" id="modules">
    <div class="section-label">What you can do</div>
    <div class="section-title">Everything you need</div>
    <p class="section-sub">Four core modules designed to handle your everyday financial transactions with ease.</p>

    <div class="modules-grid">
        <div class="module-card">
            <div class="module-icon">💸</div>
            <h3>Fund Transfer</h3>
            <p>Send money instantly to any CashBytes wallet. Secure, fast, and always traceable.</p>
        </div>
        <div class="module-card">
            <div class="module-icon">🎯</div>
            <h3>Savings Goals</h3>
            <p>Set targets for travel, tuition, or anything. Watch your progress grow every time you save.</p>
        </div>
        <div class="module-card">
            <div class="module-icon">📱</div>
            <h3>Buy Load</h3>
            <p>Top up Globe, Smart, DITO, TNT, and Sun anytime. Pick a promo or enter a custom amount.</p>
        </div>
        <div class="module-card">
            <div class="module-icon">🧾</div>
            <h3>Pay Bills</h3>
            <p>Settle Meralco, PLDT, Maynilad, SSS, and more. Keep all your receipts in one place.</p>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="how" id="how">
    <div class="section-label">Simple process</div>
    <div class="section-title">Up and running in minutes</div>
    <p class="section-sub">No complicated setup. Just three steps between you and your money.</p>

    <div class="steps">
        <div class="step">
            <div class="step-number">01</div>
            <h4>Create your account</h4>
            <p>Register with your name, mobile number, and email. Your wallet is created automatically.</p>
        </div>
        <div class="step">
            <div class="step-number">02</div>
            <h4>Fund your wallet</h4>
            <p>Add money to your CashBytes wallet and start transacting right away.</p>
        </div>
        <div class="step">
            <div class="step-number">03</div>
            <h4>Transact freely</h4>
            <p>Transfer, save, load, and pay bills — all tracked and secured with every transaction.</p>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <a href="{{ route('welcome') }}" class="brand">
        <div class="brand-icon">C</div>
        <span class="brand-name">CashBytes</span>
    </a>
    <p>© {{ date('Y') }} CashBytes. All rights reserved.</p>
    <div class="footer-links">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">Support</a>
    </div>
</footer>

</body>
</html>