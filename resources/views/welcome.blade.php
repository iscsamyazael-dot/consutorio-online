<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ultra Farmacia — Tu Salud, Nuestra Prioridad</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #00C896;
            --green-dark: #00A87E;
            --navy: #0A1628;
            --navy-mid: #0F2040;
            --navy-light: #152952;
            --white: #FFFFFF;
            --gray: #A8B8D0;
            --accent: #FFD166;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--navy);
            color: var(--white);
            overflow-x: hidden;
            min-height: 100vh;
            line-height: 1.6;
            letter-spacing: 0.01em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── Animated background ── */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%, rgba(0,200,150,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 90% 80%, rgba(0,200,150,0.08) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 60% 10%, rgba(255,209,102,0.06) 0%, transparent 50%);
        }

        .grid-overlay {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(0,200,150,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,200,150,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* ── Floating pills ── */
        .floaters {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .pill {
            position: absolute;
            border-radius: 50px;
            opacity: 0.12;
            animation: float linear infinite;
        }
        .pill-1 { width:60px; height:24px; background:var(--green); top:15%; left:5%; animation-duration:18s; animation-delay:-3s; }
        .pill-2 { width:80px; height:30px; background:var(--accent); top:70%; left:85%; animation-duration:22s; animation-delay:-8s; }
        .pill-3 { width:40px; height:16px; background:var(--green); top:45%; left:92%; animation-duration:15s; animation-delay:-1s; }
        .pill-4 { width:55px; height:22px; background:var(--white); top:85%; left:10%; animation-duration:20s; animation-delay:-12s; }
        .pill-5 { width:70px; height:28px; background:var(--accent); top:30%; left:78%; animation-duration:17s; animation-delay:-6s; }
        .cross {
            position: absolute;
            font-size: 48px;
            color: var(--green);
            opacity: 0.07;
            animation: floatCross linear infinite;
            font-weight: 900;
        }
        .cross-1 { top:10%; left:20%; animation-duration:25s; }
        .cross-2 { top:60%; left:60%; font-size:64px; animation-duration:30s; animation-delay:-10s; }
        .cross-3 { top:80%; left:40%; animation-duration:20s; animation-delay:-5s; }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(-10deg); }
            100% { transform: translateY(-120px) rotate(10deg); }
        }
        @keyframes floatCross {
            0% { transform: translateY(0) rotate(0deg); opacity: 0.07; }
            50% { opacity: 0.12; }
            100% { transform: translateY(-30px) rotate(15deg); opacity: 0.07; }
        }

        /* ── Navbar ── */
        nav {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 48px;
            border-bottom: 1px solid rgba(0,200,150,0.1);
            backdrop-filter: blur(12px);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .logo-icon {
            width: 44px;
            height: 44px;
            background: var(--green);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 900;
            color: var(--navy);
            box-shadow: 0 0 24px rgba(0,200,150,0.4);
        }
        .logo-img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            filter: brightness(0) invert(1) sepia(1) saturate(3) hue-rotate(115deg) brightness(1.1);
            drop-shadow: 0 0 12px rgba(0,200,150,0.5);
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
        .logo-text {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: var(--white);
        }
        .logo-text span { color: var(--green); }

        .nav-links {
            display: flex;
            gap: 32px;
            list-style: none;
        }
        .nav-links a {
            color: var(--gray);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--green); }

        .nav-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .btn-ghost {
            padding: 10px 22px;
            border: 1px solid rgba(0,200,150,0.3);
            border-radius: 8px;
            color: var(--green);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            background: transparent;
        }
        .btn-ghost:hover { background: rgba(0,200,150,0.1); border-color: var(--green); }

        .btn-primary {
            padding: 10px 22px;
            background: var(--green);
            border-radius: 8px;
            color: var(--navy);
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(0,200,150,0.35);
        }
        .btn-primary:hover { background: var(--green-dark); transform: translateY(-1px); box-shadow: 0 6px 24px rgba(0,200,150,0.45); }

        /* ── Hero ── */
        .hero {
            position: relative;
            z-index: 5;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 100px 24px 80px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0,200,150,0.1);
            border: 1px solid rgba(0,200,150,0.25);
            border-radius: 50px;
            padding: 8px 18px;
            font-size: 13px;
            color: var(--green);
            font-weight: 600;
            margin-bottom: 28px;
            animation: fadeDown 0.7s ease both;
        }
        .badge-dot {
            width: 8px; height: 8px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }

        .hero-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(44px, 7vw, 82px);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -1px;
            margin-bottom: 28px;
            animation: fadeDown 0.7s ease 0.1s both;
        }
        .hero-title .highlight {
            color: var(--green);
            position: relative;
        }
        .hero-title .highlight::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--green), transparent);
            border-radius: 2px;
        }

        .hero-subtitle {
            font-size: 18px;
            color: var(--gray);
            max-width: 540px;
            line-height: 1.85;
            letter-spacing: 0.015em;
            margin-bottom: 40px;
            animation: fadeDown 0.7s ease 0.2s both;
        }

        .hero-cta {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeDown 0.7s ease 0.3s both;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            background: var(--green);
            color: var(--navy);
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            box-shadow: 0 8px 32px rgba(0,200,150,0.4);
            transition: all 0.25s;
        }
        .btn-hero-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 40px rgba(0,200,150,0.5); }

        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.12);
            color: var(--white);
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            backdrop-filter: blur(8px);
            transition: all 0.25s;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.25); transform: translateY(-2px); }

        /* ── Stats ── */
        .stats-bar {
            position: relative;
            z-index: 5;
            display: flex;
            justify-content: center;
            gap: 0;
            padding: 0 24px 80px;
            animation: fadeDown 0.7s ease 0.4s both;
        }
        .stat-item {
            flex: 1;
            max-width: 200px;
            text-align: center;
            padding: 28px 20px;
            border: 1px solid rgba(0,200,150,0.1);
            background: rgba(0,200,150,0.03);
        }
        .stat-item:first-child { border-radius: 16px 0 0 16px; }
        .stat-item:last-child { border-radius: 0 16px 16px 0; }
        .stat-num {
            font-family: 'Syne', sans-serif;
            font-size: 36px;
            font-weight: 800;
            color: var(--green);
            line-height: 1.1;
            letter-spacing: -0.5px;
        }
        .stat-label { font-size: 13px; color: var(--gray); margin-top: 6px; letter-spacing: 0.02em; line-height: 1.5; }

        /* ── Services ── */
        .section {
            position: relative;
            z-index: 5;
            padding: 0 48px 100px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--green);
            margin-bottom: 12px;
        }
        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.5px;
            margin-bottom: 48px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .card {
            background: linear-gradient(135deg, rgba(15,32,64,0.8), rgba(10,22,40,0.9));
            border: 1px solid rgba(0,200,150,0.1);
            border-radius: 20px;
            padding: 32px;
            transition: all 0.3s;
            cursor: default;
            backdrop-filter: blur(8px);
        }
        .card:hover {
            border-color: rgba(0,200,150,0.35);
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 40px rgba(0,200,150,0.08);
        }
        .card-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 20px;
        }
        .icon-green { background: rgba(0,200,150,0.15); }
        .icon-yellow { background: rgba(255,209,102,0.15); }
        .icon-blue { background: rgba(100,160,255,0.15); }
        .icon-pink { background: rgba(255,100,150,0.15); }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: 0.01em;
            margin-bottom: 10px;
        }
        .card-desc {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.8;
            letter-spacing: 0.01em;
        }

        /* ── Banner ── */
        .cta-banner {
            position: relative;
            z-index: 5;
            margin: 0 48px 80px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            border-radius: 24px;
            padding: 60px 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            overflow: hidden;
        }
        .cta-banner::before {
            content: '✚';
            position: absolute;
            right: -20px;
            top: -40px;
            font-size: 200px;
            color: rgba(255,255,255,0.07);
            font-weight: 900;
        }
        .cta-banner-text h2 {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: var(--navy);
            margin-bottom: 8px;
        }
        .cta-banner-text p {
            color: rgba(10,22,40,0.7);
            font-size: 16px;
        }
        .btn-dark {
            white-space: nowrap;
            padding: 16px 36px;
            background: var(--navy);
            color: var(--white);
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            flex-shrink: 0;
        }
        .btn-dark:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.4); }

        /* ── Footer ── */
        footer {
            position: relative;
            z-index: 5;
            text-align: center;
            padding: 32px;
            border-top: 1px solid rgba(255,255,255,0.06);
            color: var(--gray);
            font-size: 13px;
        }
        footer span { color: var(--green); }

        /* ── Animations ── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            nav { padding: 16px 20px; }
            .nav-links { display: none; }
            .hero { padding: 60px 20px 50px; }
            .stats-bar { gap: 0; padding: 0 20px 60px; }
            .stat-num { font-size: 26px; }
            .section { padding: 0 20px 60px; }
            .cta-banner { flex-direction: column; text-align: center; margin: 0 20px 60px; padding: 40px 28px; }
            .cta-banner::before { display: none; }
        }
    </style>
</head>
<body>

<div class="bg-layer"></div>
<div class="grid-overlay"></div>

<div class="floaters">
    <div class="pill pill-1"></div>
    <div class="pill pill-2"></div>
    <div class="pill pill-3"></div>
    <div class="pill pill-4"></div>
    <div class="pill pill-5"></div>
    <div class="cross cross-1">✚</div>
    <div class="cross cross-2">✚</div>
    <div class="cross cross-3">✚</div>
</div>

<!-- Nav -->
<nav>
    <a href="/" class="logo">
        <img src="data:image/png;base64,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" alt="Ultra Farmacia Logo" class="logo-img">
        <span class="logo-text">Ultra<span>Farmacia</span></span>
    </a>
    <ul class="nav-links">
        <li><a href="#">Productos</a></li>
        <li><a href="#">Consultas</a></li>
        <li><a href="#">Recetas</a></li>
        <li><a href="#">Sucursales</a></li>
    </ul>
    <div class="nav-actions">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-ghost">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Iniciar sesión</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-primary">Registrarse</a>
                @endif
            @endauth
        @endif
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="badge">
        <span class="badge-dot"></span>
        Farmacia Digital #1 en México
    </div>
    <h1 class="hero-title">
        Tu Salud es<br>
        <span class="highlight">Nuestra</span> Prioridad
    </h1>
    <p class="hero-subtitle">
        Medicamentos, consultas y recetas digitales al alcance de tu mano. Atención rápida, segura y de confianza las 24 horas.
    </p>
    <div class="hero-cta">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-hero-primary">
                    ✚ &nbsp;Ir al Panel
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-hero-primary">
                    ✚ &nbsp;Crear Cuenta Gratis
                </a>
                <a href="{{ route('login') }}" class="btn-hero-secondary">
                    → &nbsp;Iniciar Sesión
                </a>
            @endauth
        @endif
    </div>
</section>

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-item">
        <div class="stat-num">+5K</div>
        <div class="stat-label">Pacientes activos</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">+800</div>
        <div class="stat-label">Medicamentos</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">24/7</div>
        <div class="stat-label">Atención en línea</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">98%</div>
        <div class="stat-label">Satisfacción</div>
    </div>
</div>

<!-- Services -->
<section class="section">
    <p class="section-label">Nuestros Servicios</p>
    <h2 class="section-title">Todo lo que necesitas,<br>en un solo lugar</h2>
    <div class="cards-grid">
        <div class="card">
            <div class="card-icon icon-green">💊</div>
            <div class="card-title">Medicamentos Online</div>
            <div class="card-desc">Encuentra y solicita tus medicamentos desde casa con entrega a domicilio rápida y segura.</div>
        </div>
        <div class="card">
            <div class="card-icon icon-yellow">📋</div>
            <div class="card-title">Recetas Digitales</div>
            <div class="card-desc">Gestiona y presenta tus recetas médicas de forma digital, sin filas ni papeles.</div>
        </div>
        <div class="card">
            <div class="card-icon icon-blue">🩺</div>
            <div class="card-title">Consulta Médica</div>
            <div class="card-desc">Conecta con médicos certificados para consultas en línea rápidas y a bajo costo.</div>
        </div>
        <div class="card">
            <div class="card-icon icon-pink">❤️</div>
            <div class="card-title">Seguimiento Clínico</div>
            <div class="card-desc">Lleva el historial de salud de toda tu familia en un expediente digital seguro.</div>
        </div>
    </div>
</section>

<!-- CTA Banner -->
<div class="cta-banner">
    <div class="cta-banner-text">
        <h2>¿Listo para cuidar tu salud?</h2>
        <p>Regístrate gratis y obtén tu primera consulta sin costo.</p>
    </div>
    @if (Route::has('register'))
        @guest
            <a href="{{ route('register') }}" class="btn-dark">Comenzar ahora →</a>
        @endguest
    @endif
</div>

<!-- Footer -->
<footer>
    © {{ date('Y') }} <span>Ultra Farmacia</span> — Tu Salud, Nuestra Prioridad. Todos los derechos reservados.
</footer>

</body>
</html>