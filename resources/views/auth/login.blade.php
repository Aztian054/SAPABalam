<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sistem Loket Pertanahan Elektronik BMN BALAM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/images/logobpn2026.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Caveat+Brush&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy: #092B50;
            --navy-dark: #071D35;
            --blue: #164D7A;
            --gold: #C99A18;
            --gold-hover: #B88912;
            --bg-light: #F8FAFC;
            --text: #1E293B;
            --text-muted: #64748B;
            --border: #DCE3EC;
            --scrollbar-thumb: rgba(100, 116, 139, 0.4);
            --tagline: #4FB6F0;
        }

        html { font-size: clamp(16px, 0.6vw + 8px, 28px); }

        /*= Reset ringan & scrollbar =*/
        * { scrollbar-width: thin; scrollbar-color: var(--scrollbar-thumb) transparent; }
        *::-webkit-scrollbar { width: 10px; height: 10px; }
        *::-webkit-scrollbar-track { background: transparent; }
        *::-webkit-scrollbar-thumb {
            background-color: var(--scrollbar-thumb);
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }
        *::-webkit-scrollbar-thumb:hover { background-color: var(--gold); }
        *::-webkit-scrollbar-corner { background: transparent; }

        body.auth-page {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: var(--bg-light);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .auth-shell {
            display: grid;
            grid-template-columns: minmax(0, 45%) minmax(0, 55%);
            min-height: 100vh;
            min-height: 100dvh;
            overflow-x: hidden;
        }

        /*= ------------------------- PANEL KIRI: BRANDING ------------------------- =*/
        .auth-brand {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2rem;
            padding: clamp(1.75rem, 4vw, 3.5rem);
            color: #fff;
            background: var(--navy-dark);
        }
        .brand-bg {
            position: absolute;
            inset: 0;
            z-index: -3;
            background: url("{{ asset('asset/background/BGloginpage.png') }}") center/cover no-repeat;
            transform: scale(1.02);
            animation: kenburns 26s ease-in-out infinite alternate;
        }
        .brand-veil {
            position: absolute;
            inset: 0;
            z-index: -2;
            background: linear-gradient(135deg, rgba(7, 29, 53, 0.95) 0%, rgba(9, 43, 80, 0.80) 48%, rgba(22, 77, 122, 0.62) 100%);
            background-size: 180% 180%;
            animation: gradientShift 16s ease-in-out infinite alternate;
        }

        /* Ornamen dekoratif CSS-only */
        .ornaments { position: absolute; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
        .orn { position: absolute; display: block; }
        .orn-globe {
            width: 34rem;
            height: 34rem;
            right: -12rem;
            bottom: -13rem;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.14);
            animation: floatSlow 11s ease-in-out infinite;
        }
        .orn-globe::before,
        .orn-globe::after {
            content: "";
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 50%;
        }
        .orn-globe::before { inset: 10% 34%; }
        .orn-globe::after { inset: 34% 10%; }
        .orn-ring {
            border-radius: 50%;
            border: 1.5px solid rgba(201, 154, 24, 0.28);
        }
        .orn-ring-1 { width: 9rem; height: 9rem; top: 14%; right: 12%; animation: floatSlow 8s ease-in-out infinite; }
        .orn-ring-2 { width: 3.4rem; height: 3.4rem; top: 38%; right: 27%; border-color: rgba(255, 255, 255, 0.2); animation: floatSlow 6.5s ease-in-out infinite reverse; }
        .orn-grid {
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 4rem 4rem;
            mask-image: radial-gradient(circle at 20% 25%, #000 0%, transparent 62%);
            -webkit-mask-image: radial-gradient(circle at 20% 25%, #000 0%, transparent 62%);
        }
        .orn-line {
            width: 140%;
            height: 1px;
            left: -20%;
            top: 72%;
            background: linear-gradient(90deg, transparent, rgba(201, 154, 24, 0.35), transparent);
            transform: rotate(-12deg);
        }

        .brand-top { position: relative; display: flex; align-items: center; gap: 0.9rem; }
        .brand-logo {
            width: 3.375rem;
            height: 3.375rem;
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            padding: 0.375rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.28);
        }
        .brand-name {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            line-height: 1.2;
        }
        .brand-office {
            margin: 0.15rem 0 0;
            font-size: 0.85rem;
            color: rgba(226, 232, 240, 0.72);
            letter-spacing: 0.02em;
        }
        .brand-tagline {
            margin: 0.15rem 0 0;
            font-family: 'Caveat Brush', 'Kaushan Script', cursive;
            font-size: 1.3rem;
            font-weight: 400;
            line-height: 1.1;
            letter-spacing: 0.02em;
            color: var(--tagline);
        }

        .brand-copy { position: relative; max-width: 42rem; }
        .brand-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #F0C75A;
            margin-bottom: 1rem;
        }
        .brand-eyebrow::before {
            content: "";
            width: 1.75rem;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--gold), transparent);
        }
        .brand-copy h1 {
            margin: 0 0 1rem;
            font-size: clamp(2.4rem, 3.4vw, 4rem);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.02em;
            color: #fff;
        }
        .text-shimmer {
            background: linear-gradient(90deg, #F6D365 0%, #FFF3BF 25%, #C99A18 50%, #FFF3BF 75%, #F6D365 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 7s linear infinite;
        }
        .brand-copy p {
            margin: 0;
            font-size: 1.05rem;
            line-height: 1.7;
            color: rgba(226, 232, 240, 0.78);
        }
        .brand-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        .brand-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.2;
            color: rgba(241, 245, 249, 0.9);
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.16);
            text-decoration: none;
            cursor: pointer;
            backdrop-filter: blur(4px);
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .brand-action i { color: #F0C75A; font-size: 0.95rem; }
        .brand-action:hover {
            color: #fff;
            background: rgba(201, 154, 24, 0.16);
            border-color: rgba(201, 154, 24, 0.5);
        }
        .brand-action:focus-visible {
            outline: 2px solid rgba(240, 199, 90, 0.75);
            outline-offset: 2px;
        }
        .brand-action--primary {
            color: #fff;
            background: linear-gradient(180deg, #D3A62A 0%, var(--gold) 100%);
            border-color: transparent;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.28);
        }
        .brand-action--primary i { color: #fff; }
        .brand-action--primary:hover {
            color: #fff;
            background: linear-gradient(180deg, #D3A62A 0%, var(--gold) 100%);
            border-color: transparent;
            filter: brightness(1.05);
        }

        .brand-contact {
            position: relative;
            display: grid;
            gap: 0.55rem;
            margin-top: 0.5rem;
            padding-top: 1.15rem;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }
        .brand-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
            font-size: 0.8rem;
            line-height: 1.5;
            color: rgba(226, 232, 240, 0.82);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .brand-contact-item i {
            flex: 0 0 auto;
            margin-top: 0.1rem;
            font-size: 0.95rem;
            color: #F0C75A;
        }
        .brand-contact-item:hover {
            color: #fff;
            text-decoration: underline;
            text-underline-offset: 0.15em;
        }
        .brand-contact-item:focus-visible {
            outline: 2px solid rgba(240, 199, 90, 0.75);
            outline-offset: 2px;
            border-radius: 0.3rem;
        }
        .brand-contact-item a {
            color: inherit;
            text-decoration: none;
        }
        .brand-contact-item a:hover {
            color: #fff;
            text-decoration: underline;
            text-underline-offset: 0.15em;
        }
        .brand-contact-item a:focus-visible {
            outline: 2px solid rgba(240, 199, 90, 0.75);
            outline-offset: 2px;
            border-radius: 0.3rem;
        }
        .brand-social {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.35rem;
        }
        .brand-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 50%;
            color: #fff;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .brand-social a:hover {
            background: var(--gold);
            border-color: var(--gold);
            color: #fff;
        }
        .brand-social a:focus-visible {
            outline: 2px solid rgba(240, 199, 90, 0.75);
            outline-offset: 2px;
        }

        .brand-foot {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            padding-top: 1.1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 0.85rem;
            color: rgba(226, 232, 240, 0.62);
        }
        .brand-foot .secure {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 600;
            color: #F0C75A;
        }

        /*= ------------------------- PANEL KANAN: FORM ------------------------- =*/
        .auth-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.5rem, 5vw, 4rem);
            background: linear-gradient(160deg, #F8FAFC 0%, #EEF4FB 100%);
            overflow: hidden;
        }
        .panel-glow {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(33.75rem circle at var(--mx, 68%) var(--my, 28%), rgba(201, 154, 24, 0.11), transparent 62%);
            opacity: 0.85;
            transition: opacity 0.3s ease;
        }
        .auth-panel.is-tracking .panel-glow { opacity: 1; }

        .login-box {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 30rem;
        }
        .login-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 0.85rem;
        }
        .login-eyebrow::before {
            content: "";
            width: 1.5rem;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--gold), transparent);
        }
        .login-title {
            margin: 0 0 0.55rem;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--navy);
        }
        .login-sub {
            margin: 0 0 1.75rem;
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-muted);
        }

        .login-box .alert {
            border: none;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
        }

        .field { margin-bottom: 1.1rem; }
        .field label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            color: var(--text-muted);
        }
        .control { position: relative; display: flex; align-items: center; }
        .control-icon {
            position: absolute;
            left: 1.1rem;
            z-index: 1;
            font-size: 1.15rem;
            color: #94A3B8;
            pointer-events: none;
            transition: color 0.25s ease;
        }
        .control input {
            width: 100%;
            height: 3.4rem;
            padding: 0 3.1rem 0 3rem;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            background: #fff;
            color: var(--text);
            font-size: 1.05rem;
            outline: none;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }
        .control input::placeholder { color: #A3AEBF; }
        .control input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.25rem rgba(201, 154, 24, 0.15);
        }
        .control:focus-within .control-icon { color: var(--gold); }

        .toggle-pass {
            position: absolute;
            right: 0.5rem;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            border: 0;
            border-radius: 0.6rem;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .toggle-pass i { font-size: 1.15rem; }
        .toggle-pass:hover { background: rgba(148, 163, 184, 0.14); color: var(--navy); }
        .toggle-pass:focus-visible { outline: 2px solid rgba(201, 154, 24, 0.45); outline-offset: 1px; }

        .btn-login {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 3.5rem;
            margin-top: 0.5rem;
            border: 0;
            border-radius: 0.75rem;
            background: linear-gradient(180deg, #D3A62A 0%, var(--gold) 100%);
            color: #fff;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            cursor: pointer;
            overflow: hidden;
            box-shadow: 0 0.625rem 1.375rem rgba(201, 154, 24, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.875rem 1.75rem rgba(201, 154, 24, 0.34);
            filter: brightness(1.03);
        }
        .btn-login:active { transform: translateY(0); box-shadow: 0 0.375rem 0.875rem rgba(201, 154, 24, 0.3); }
        .btn-login:focus-visible { outline: 0.1875rem solid rgba(201, 154, 24, 0.4); outline-offset: 2px; }
        .btn-login:disabled { cursor: progress; opacity: 0.92; transform: none; }
        .btn-label, .btn-loading {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-loading { display: none; }
        .btn-login.is-loading .btn-label { display: none; }
        .btn-login.is-loading .btn-loading { display: inline-flex; }
        .spinner {
            width: 1.1rem;
            height: 1.1rem;
            border: 2px solid rgba(255, 255, 255, 0.45);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        .secure-note {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding-top: 1.1rem;
            border-top: 1px solid rgba(148, 163, 184, 0.22);
            font-size: 0.85rem;
            line-height: 1.55;
            color: var(--text-muted);
        }
        .secure-note i { color: var(--gold); font-size: 1.05rem; margin-top: 0.05rem; }
        .panel-foot {
            margin: 2rem 0 0;
            text-align: center;
            font-size: 0.8rem;
            color: #94A3B8;
        }

        /*= ------------------------- ANIMASI ------------------------- =*/
        @keyframes kenburns {
            from { transform: scale(1.02); }
            to   { transform: scale(1.14); }
        }
        @keyframes gradientShift {
            from { background-position: 0% 40%; }
            to   { background-position: 100% 60%; }
        }
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-14px); }
        }
        @keyframes shimmer {
            0%   { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
        @keyframes riseIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .reveal {
            opacity: 0;
            animation: riseIn 0.7s cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
            animation-delay: var(--d, 0s);
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
            }
            .reveal { opacity: 1; }
            .brand-bg { transform: none; }
        }

        /*= ------------------------- RESPONSIVE ------------------------- =*/
        @media (max-width: 1199.98px) {
            .auth-shell { grid-template-columns: minmax(0, 43%) minmax(0, 57%); }
        }
        @media (max-width: 991.98px) {
            .auth-shell { grid-template-columns: minmax(0, 40%) minmax(0, 60%); }
            .brand-contact { display: none; }
            .brand-copy h1 { font-size: clamp(2rem, 3.2vw, 2.6rem); }
            .brand-actions { margin-top: 1.2rem; }
            .brand-logo { width: 2.75rem; height: 2.75rem; }
        }
        @media (max-width: 767.98px) {
            .auth-shell { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
            .auth-brand { min-height: 17.5rem; padding: 1.6rem 1.4rem; gap: 1.25rem; }
            .brand-tagline { font-size: 1.05rem; }
            .brand-copy h1 { font-size: 2rem; margin-bottom: 0.6rem; }
            .brand-copy p { font-size: 1rem; line-height: 1.6; }
            .brand-foot { display: none; }
            .brand-eyebrow { margin-bottom: 0.6rem; }
            .orn-globe { width: 22rem; height: 22rem; right: -9rem; bottom: -9rem; }
            .orn-ring-1 { width: 6rem; height: 6rem; top: 10%; right: 8%; }
            .orn-ring-2 { display: none; }
            .auth-panel { align-items: flex-start; padding: 2rem 1.25rem 2.5rem; }
            .login-box { max-width: 30rem; }
            .panel-foot { margin-top: 1.5rem; }
        }
        @media (max-width: 575.98px) {
            .auth-brand { min-height: 15rem; }
            .brand-copy h1 { font-size: 1.65rem; }
            .login-title { font-size: 1.75rem; }
            .login-sub { margin-bottom: 1.4rem; }
            .control input { height: 3rem; }
            .btn-login { height: 3.1rem; }
        }
        @media (max-height: 760px) {
            .auth-brand {
                padding-top: 1.75rem;
                padding-bottom: 1.75rem;
            }
            .auth-panel {
                padding-top: 1.5rem;
                padding-bottom: 1.5rem;
            }
            .login-sub { margin-bottom: 1.15rem; }
            .field { margin-bottom: 0.8rem; }
            .login-box .alert { margin-bottom: 0.75rem; }
            .secure-note { margin-top: 1rem; padding-top: 0.85rem; }
            .panel-foot { margin-top: 1rem; }
            .brand-copy h1 { margin-bottom: 0.65rem; }
            .brand-copy p { line-height: 1.6; }
        }

        /*= ------------------------- TENTANG & KONTAK ------------------------- =*/
        .about-panel {
            --bs-offcanvas-width: clamp(24rem, 32vw, 42rem);
            --bs-offcanvas-bg: #fff;
            --bs-offcanvas-color: var(--text);
            border-left: 1px solid var(--border);
        }
        .about-panel .offcanvas-header {
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            color: #fff;
            background: linear-gradient(135deg, var(--navy-dark) 0%, var(--blue) 100%);
        }
        .about-panel .offcanvas-body {
            padding: 1.25rem 1.5rem 1.75rem;
        }
        .about-panel .btn-close {
            filter: invert(1) grayscale(1) brightness(2);
            opacity: 0.85;
        }
        .about-panel .btn-close:hover { opacity: 1; }

        .about-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 0;
        }
        .about-brand img {
            flex: 0 0 auto;
            width: 3rem;
            height: 3rem;
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            padding: 0.3rem;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
        }
        .about-kicker {
            margin: 0 0 0.15rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #F0C75A;
        }
        .about-panel h2 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .about-office {
            margin: 0.15rem 0 0;
            font-size: 0.8rem;
            color: rgba(226, 232, 240, 0.78);
        }

        .about-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
        }
        .about-nav .nav-link {
            border-radius: 0.6rem;
            padding: 0.45rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }
        .about-nav .nav-link:hover {
            color: var(--navy);
            background: rgba(9, 43, 80, 0.06);
        }
        .about-nav .nav-link.active {
            color: #fff;
            background: var(--navy);
        }
        .about-nav .nav-link:focus-visible {
            outline: 2px solid rgba(201, 154, 24, 0.45);
            outline-offset: 2px;
        }

        .about-card {
            padding: 1rem 1.1rem;
            border: 1px solid var(--border);
            border-radius: 0.9rem;
            background: #fff;
        }
        .about-card + .about-card { margin-top: 0.85rem; }
        .about-card h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 0.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--navy);
        }
        .about-card h3 i { color: var(--gold); }
        .about-card p,
        .about-card li {
            margin: 0;
            font-size: 0.88rem;
            line-height: 1.65;
            color: var(--text-muted);
            overflow-wrap: anywhere;
        }
        .about-card p + p { margin-top: 0.6rem; }
        .about-list {
            margin: 0;
            padding-left: 1.1rem;
        }
        .about-list li + li { margin-top: 0.4rem; }

        .about-service {
            display: flex;
            gap: 0.85rem;
            align-items: flex-start;
        }
        .about-ico {
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.75rem;
            background: rgba(9, 43, 80, 0.07);
            color: var(--navy);
            font-size: 1.2rem;
        }

        .about-contact {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.85rem 0;
            border-bottom: 1px solid var(--border);
        }
        .about-contact:last-of-type { border-bottom: 0; }
        .about-contact i {
            flex: 0 0 auto;
            margin-top: 0.1rem;
            color: var(--gold);
            font-size: 1.05rem;
        }
        .about-contact strong {
            display: block;
            margin-bottom: 0.15rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-muted);
        }
        .about-contact a {
            color: var(--text);
            text-decoration: none;
            overflow-wrap: anywhere;
        }
        .about-contact a:hover {
            color: var(--gold);
            text-decoration: underline;
            text-underline-offset: 0.15em;
        }
        .about-contact a:focus-visible,
        .social-btn:focus-visible,
        .btn-about-primary:focus-visible {
            outline: 2px solid rgba(201, 154, 24, 0.45);
            outline-offset: 2px;
        }

        .social-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.35rem;
        }
        .social-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            border: 1px solid var(--border);
            border-radius: 50%;
            background: #fff;
            color: var(--navy);
            font-size: 1.1rem;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .social-btn:hover {
            background: var(--navy);
            border-color: var(--navy);
            color: #fff;
        }

        .btn-about-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            height: 3.1rem;
            margin-top: 1.25rem;
            border-radius: 0.75rem;
            background: linear-gradient(180deg, #D3A62A 0%, var(--gold) 100%);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 8px 18px rgba(201, 154, 24, 0.26);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }
        .btn-about-primary:hover {
            color: #fff;
            transform: translateY(-2px);
            filter: brightness(1.03);
            box-shadow: 0 12px 24px rgba(201, 154, 24, 0.32);
        }
        .btn-about-primary:active { transform: translateY(0); }

        @media (max-height: 820px) {
            .auth-brand {
                gap: 1.25rem;
                padding-top: 1.75rem;
                padding-bottom: 1.75rem;
            }
            .brand-actions { margin-top: 1rem; }
            .brand-contact { margin-top: 0.25rem; padding-top: 0.9rem; gap: 0.45rem; }
        }

        /* Bottom sheet di layar kecil */
        @media (max-width: 767.98px) {
            .about-panel {
                top: auto;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100%;
                max-width: 100%;
                height: 85vh;
                max-height: 85vh;
                border-left: 0;
                border-top: 1px solid var(--border);
                border-top-left-radius: 1rem;
                border-top-right-radius: 1rem;
                transform: translateY(100%);
            }
            .about-panel .offcanvas-header { border-top-left-radius: 1rem; border-top-right-radius: 1rem; }
        }
    </style>
</head>
<body class="auth-page">

<div class="auth-shell">

    {{-- ============================ PANEL KIRI: BRANDING ============================ --}}
    <section class="auth-brand" aria-label="Identitas Sistem SAPA Balam">
        <div class="brand-bg" aria-hidden="true"></div>
        <div class="brand-veil" aria-hidden="true"></div>
        <div class="ornaments" aria-hidden="true">
            <span class="orn orn-grid"></span>
            <span class="orn orn-globe"></span>
            <span class="orn orn-ring orn-ring-1"></span>
            <span class="orn orn-ring orn-ring-2"></span>
            <span class="orn orn-line"></span>
        </div>

        <header class="brand-top reveal" style="--d: .05s;">
            <img src="{{ asset('asset/images/logobpn2026.svg') }}" alt="Logo ATR/BPN" class="brand-logo">
            <div>
                <p class="brand-name">SAPA Balam</p>
                <p class="brand-office">Kantor Pertanahan Kota Bandar Lampung</p>
                <p class="brand-tagline">Melayani, Profesional, Terpercaya</p>
            </div>
        </header>

        <div class="brand-copy">
            <span class="brand-eyebrow reveal" style="--d: .15s;">Portal Layanan Pertanahan</span>
            <h1 class="reveal" style="--d: .22s;">
                Sistem Loket<br>
                <span class="text-shimmer">Pertanahan Elektronik</span>
            </h1>
            <p class="reveal" style="--d: .32s;">
                Sistem Loket Elektronik hadir untuk mempermudah masyarakat Bandar Lampung
                dalam pendaftaran permohonan tanah, pelacakan berkas warkah, hingga
                penyaluran pengaduan secara terpadu.
            </p>
            <div class="brand-actions reveal" style="--d: .42s;">
                <button type="button" class="brand-action" data-bs-target="#aboutPanel" data-about-tab="#aboutTabBtn"
                        aria-controls="aboutPanel">
                    <i class="bi bi-info-circle" aria-hidden="true"></i> Tentang
                </button>
                <button type="button" class="brand-action" data-bs-target="#aboutPanel" data-about-tab="#layananTabBtn"
                        aria-controls="aboutPanel">
                    <i class="bi bi-grid-1x2" aria-hidden="true"></i> Layanan
                </button>
                <button type="button" class="brand-action" data-bs-target="#aboutPanel" data-about-tab="#kontakTabBtn"
                        aria-controls="aboutPanel">
                    <i class="bi bi-telephone" aria-hidden="true"></i> Kontak
                </button>
                <a class="brand-action brand-action--primary"
                   href="https://kot-bandarlampung.atrbpn.go.id/"
                   target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Website Resmi
                </a>
            </div>
        </div>

        <div class="brand-contact reveal" style="--d: .48s;">
            <a class="brand-contact-item"
               href="https://www.google.com/maps/search/?api=1&amp;query=Jalan%20Dokter%20Warsito%20No.5%2C%20Talang%2C%20Telukbetung%20Selatan%2C%20Bandar%20Lampung%2035211"
               target="_blank" rel="noopener noreferrer">
                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                <span>Jl. Dokter Warsito No.5, Telukbetung Selatan, Bandar Lampung</span>
            </a>
            <div class="brand-contact-item">
                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                <span>
                    <a href="https://wa.me/6287878784040" target="_blank" rel="noopener noreferrer">0878 7878 4040</a>
                    <span aria-hidden="true"> &middot; </span>
                    <a href="https://wa.me/6281272339628" target="_blank" rel="noopener noreferrer">0812 7233 9628</a>
                </span>
            </div>
            <a class="brand-contact-item" href="mailto:kot-bandarlampung@atrbpn.go.id">
                <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                <span>kot-bandarlampung@atrbpn.go.id</span>
            </a>
            <div class="brand-social">
                <a href="https://www.instagram.com/kantahkotabandarlampung/"
                   target="_blank" rel="noopener noreferrer" aria-label="Instagram @kantahkotabandarlampung">
                    <i class="bi bi-instagram" aria-hidden="true"></i>
                </a>
                <a href="https://x.com/atrbpnbalam"
                   target="_blank" rel="noopener noreferrer" aria-label="X @AtrbpnBalam">
                    <i class="bi bi-twitter-x" aria-hidden="true"></i>
                </a>
                <a href="https://www.facebook.com/ATRBPN.Balam"
                   target="_blank" rel="noopener noreferrer" aria-label="Facebook ATRBPN.Balam">
                    <i class="bi bi-facebook" aria-hidden="true"></i>
                </a>
                <a href="https://www.tiktok.com/@kantahkotabandarlampung"
                   target="_blank" rel="noopener noreferrer" aria-label="TikTok @kantahkotabandarlampung">
                    <i class="bi bi-tiktok" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <footer class="brand-foot reveal" style="--d: .52s;">
            <span class="secure"><i class="bi bi-shield-lock-fill"></i> Akses Terenkripsi &amp; Aman</span>
            <span>&copy; {{ date('Y') }} Kantor Pertanahan Kota Bandar Lampung</span>
        </footer>
    </section>

    {{-- ============================ PANEL KANAN: FORM LOGIN ============================ --}}
    <main class="auth-panel">
        <div class="panel-glow" aria-hidden="true"></div>

        <div class="login-box">
            <span class="login-eyebrow reveal" style="--d: .18s;">Portal Internal</span>
            <h2 class="login-title reveal" style="--d: .24s;">Selamat Datang Kembali</h2>
            <p class="login-sub reveal" style="--d: .30s;">
                Masuk menggunakan akun terdaftar sesuai peran operasional Anda.
            </p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="reveal" style="--d: .38s;" id="loginForm">
                @csrf

                <div class="field">
                    <label for="usernameInput">Username atau Email</label>
                    <div class="control">
                        <i class="bi bi-person control-icon" aria-hidden="true"></i>
                        <input type="text" name="username" id="usernameInput"
                               placeholder="Masukkan username atau email"
                               value="{{ old('username') }}" autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label for="passwordInput">Password</label>
                    <div class="control">
                        <i class="bi bi-lock control-icon" aria-hidden="true"></i>
                        <input type="password" name="password" id="passwordInput"
                               placeholder="Masukkan password"
                               autocomplete="current-password" required>
                        <button type="button" class="toggle-pass" id="togglePassword"
                                aria-label="Tampilkan password" aria-pressed="false">
                            <i class="bi bi-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginButton">
                    <span class="btn-label">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Masuk Sekarang
                    </span>
                    <span class="btn-loading" aria-hidden="true">
                        <span class="spinner"></span> Memproses…
                    </span>
                </button>
            </form>

            <div class="secure-note reveal" style="--d: .46s;">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                <span>Koneksi aman. Jangan bagikan kredensial akun Anda kepada siapa pun.</span>
            </div>

            <p class="panel-foot reveal" style="--d: .52s;">
                &copy; {{ date('Y') }} Kantor Pertanahan Kota Bandar Lampung
            </p>
        </div>
    </main>

</div>

{{-- ============================ PANEL TENTANG & KONTAK ============================ --}}
<div class="offcanvas offcanvas-end about-panel" tabindex="-1" id="aboutPanel"
     aria-labelledby="aboutPanelLabel">
    <div class="offcanvas-header">
        <div class="about-brand">
            <img src="{{ asset('asset/images/logobpn2026.svg') }}" alt="Logo ATR/BPN">
            <div>
                <p class="about-kicker">Portal Layanan Pertanahan</p>
                <h2 id="aboutPanelLabel">Sistem Loket Pertanahan Elektronik</h2>
                <p class="about-office">Kantor Pertanahan Kota Bandar Lampung</p>
                <p class="brand-tagline">Melayani, Profesional, Terpercaya</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                aria-label="Tutup panel"></button>
    </div>

    <div class="offcanvas-body">
        <div class="nav nav-pills about-nav" role="tablist" aria-label="Informasi Tentang dan Kontak">
            <button type="button" class="nav-link active" id="aboutTabBtn" role="tab"
                    data-bs-toggle="tab" data-bs-target="#aboutTab"
                    aria-controls="aboutTab" aria-selected="true">Tentang</button>
            <button type="button" class="nav-link" id="layananTabBtn" role="tab"
                    data-bs-toggle="tab" data-bs-target="#layananTab"
                    aria-controls="layananTab" aria-selected="false">Layanan</button>
            <button type="button" class="nav-link" id="kontakTabBtn" role="tab"
                    data-bs-toggle="tab" data-bs-target="#kontakTab"
                    aria-controls="kontakTab" aria-selected="false">Kontak</button>
        </div>

        <div class="tab-content">
            {{-- Tab Tentang --}}
            <div class="tab-pane fade show active" id="aboutTab" role="tabpanel"
                 aria-labelledby="aboutTabBtn" tabindex="0">
                <div class="about-card">
                    <p>
                        Sistem Loket Elektronik adalah komitmen Kantor Pertanahan Kota Bandar Lampung
                        untuk menghadirkan pelayanan publik yang modern dan transparan.
                    </p>
                </div>

                <div class="about-card">
                    <h3><i class="bi bi-eye-fill" aria-hidden="true"></i> Visi</h3>
                    <p>
                        Terwujudnya Penataan Ruang dan Pengelolaan Pertanahan yang Terpercaya dan
                        Berstandar Dunia dalam Melayani Masyarakat untuk Mendukung Tercapainya:
                        &ldquo;Indonesia Maju yang Berdaulat, Mandiri dan Berkepribadian Berlandaskan
                        Gotong Royong&rdquo;.
                    </p>
                </div>

                <div class="about-card">
                    <h3><i class="bi bi-list-check" aria-hidden="true"></i> Misi</h3>
                    <ol class="about-list">
                        <li>
                            Menyelenggarakan Penataan Ruang dan Pengelolaan Pertanahan yang Produktif,
                            Berkelanjutan dan Berkeadilan.
                        </li>
                        <li>
                            Menyelenggarakan Pelayanan Pertanahan dan Penataan Ruang yang Berstandar Dunia.
                        </li>
                    </ol>
                </div>

                <div class="about-card">
                    <h3><i class="bi bi-award-fill" aria-hidden="true"></i> Moto</h3>
                    <p>Melayani, Profesional, Terpercaya.</p>
                </div>
            </div>

            {{-- Tab Layanan --}}
            <div class="tab-pane fade" id="layananTab" role="tabpanel"
                 aria-labelledby="layananTabBtn" tabindex="0">
                <div class="about-card about-service">
                    <span class="about-ico"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
                    <div>
                        <h3>Permohonan Online</h3>
                        <p>
                            Mulai dari pendaftaran awal, ploting, hingga penyerahan berkas kini
                            terintegrasi dalam satu sistem elektronik.
                        </p>
                    </div>
                </div>

                <div class="about-card about-service">
                    <span class="about-ico"><i class="bi bi-chat-square-text" aria-hidden="true"></i></span>
                    <div>
                        <h3>Layanan Pengaduan</h3>
                        <p>
                            Hubungan interaktif masyarakat dengan Kantor Pertanahan. Laporkan
                            kendala Anda, kami berikan solusi terbaik.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tab Kontak --}}
            <div class="tab-pane fade" id="kontakTab" role="tabpanel"
                 aria-labelledby="kontakTabBtn" tabindex="0">
                <div class="about-card">
                    <p>Silakan hubungi atau kunjungi kami untuk informasi dan layanan lebih lanjut.</p>
                </div>

                <div class="about-card">
                    <div class="about-contact">
                        <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                        <div>
                            <strong>Alamat</strong>
                            <a href="https://www.google.com/maps/search/?api=1&amp;query=Jalan%20Dokter%20Warsito%20No.5%2C%20Talang%2C%20Telukbetung%20Selatan%2C%20Bandar%20Lampung%2035211"
                               target="_blank" rel="noopener noreferrer">
                                Jalan Dokter Warsito No.5, Talang, Kec. Telukbetung Selatan,
                                Kota Bandar Lampung, Lampung 35211
                            </a>
                        </div>
                    </div>

                    <div class="about-contact">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i>
                        <div>
                            <strong>WhatsApp</strong>
                            <a href="https://wa.me/6287878784040" target="_blank" rel="noopener noreferrer">087878784040</a>
                            <span aria-hidden="true"> / </span>
                            <a href="https://wa.me/6281272339628" target="_blank" rel="noopener noreferrer">081272339628</a>
                        </div>
                    </div>

                    <div class="about-contact">
                        <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                        <div>
                            <strong>Email</strong>
                            <a href="mailto:kot-bandarlampung@atrbpn.go.id">kot-bandarlampung@atrbpn.go.id</a>
                        </div>
                    </div>

                    <div class="about-contact">
                        <i class="bi bi-share-fill" aria-hidden="true"></i>
                        <div>
                            <strong>Media Sosial</strong>
                            <div class="social-row">
                                <a class="social-btn" href="https://www.instagram.com/kantahkotabandarlampung/"
                                   target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-instagram" aria-hidden="true"></i>
                                    <span class="visually-hidden">Instagram @kantahkotabandarlampung</span>
                                </a>
                                <a class="social-btn" href="https://x.com/atrbpnbalam"
                                   target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-twitter-x" aria-hidden="true"></i>
                                    <span class="visually-hidden">X @AtrbpnBalam</span>
                                </a>
                                <a class="social-btn" href="https://www.facebook.com/ATRBPN.Balam"
                                   target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-facebook" aria-hidden="true"></i>
                                    <span class="visually-hidden">Facebook ATRBPN.Balam</span>
                                </a>
                                <a class="social-btn" href="https://www.tiktok.com/@kantahkotabandarlampung"
                                   target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-tiktok" aria-hidden="true"></i>
                                    <span class="visually-hidden">TikTok @kantahkotabandarlampung</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <a class="btn-about-primary" href="https://kot-bandarlampung.atrbpn.go.id/"
                   target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Kunjungi Website Resmi
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        var finePointer = window.matchMedia('(pointer: fine)');

        /*= Glow redup mengikuti kursor pada panel form =*/
        var panel = document.querySelector('.auth-panel');
        if (panel && !reduceMotion.matches && finePointer.matches) {
            var raf = null;
            var pointer = null;

            var renderGlow = function () {
                raf = null;
                if (!pointer) return;
                var rect = panel.getBoundingClientRect();
                var x = Math.min(Math.max(pointer.clientX - rect.left, 0), rect.width);
                var y = Math.min(Math.max(pointer.clientY - rect.top, 0), rect.height);
                panel.style.setProperty('--mx', (x / rect.width * 100).toFixed(2) + '%');
                panel.style.setProperty('--my', (y / rect.height * 100).toFixed(2) + '%');
            };

            panel.addEventListener('pointermove', function (event) {
                pointer = event;
                panel.classList.add('is-tracking');
                if (raf === null) raf = window.requestAnimationFrame(renderGlow);
            }, { passive: true });

            panel.addEventListener('pointerleave', function () {
                pointer = null;
                if (raf !== null) {
                    window.cancelAnimationFrame(raf);
                    raf = null;
                }
                panel.classList.remove('is-tracking');
            });
        }

        /*= Tampilkan / sembunyikan password =*/
        var toggle = document.getElementById('togglePassword');
        var password = document.getElementById('passwordInput');
        var toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggle && password) {
            toggle.addEventListener('click', function () {
                var show = password.type === 'password';
                password.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
                if (toggleIcon) {
                    toggleIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                }
                password.focus();
            });
        }

        /*= Loading state saat submit (mencegah klik ganda) =*/
        var form = document.getElementById('loginForm');
        var button = document.getElementById('loginButton');

        if (form && button) {
            form.addEventListener('submit', function () {
                if (form.dataset.submitting === '1') {
                    return;
                }
                form.dataset.submitting = '1';
                button.disabled = true;
                button.classList.add('is-loading');
                button.setAttribute('aria-busy', 'true');
            });
        }

        /*= Tombol panel kiri membuka tab tertentu di panel Tentang & Kontak =*/
        var aboutPanel = document.getElementById('aboutPanel');
        var bs = window.bootstrap || null;

        if (aboutPanel && bs && bs.Offcanvas) {
            Array.prototype.forEach.call(
                document.querySelectorAll('[data-about-tab]'),
                function (btn) {
                    btn.addEventListener('click', function () {
                        var trigger = document.querySelector(btn.getAttribute('data-about-tab'));
                        if (trigger && bs.Tab) {
                            bs.Tab.getOrCreateInstance(trigger).show();
                        }
                        bs.Offcanvas.getOrCreateInstance(aboutPanel).show();
                    });
                }
            );
        }
    })();
</script>

</body>
</html>
