<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Hotel'))</title>

    @php
        $brandLogoPath = 'branding/logo.png';
        $brandFaviconPath = 'branding/favicon.png';
        $brandLogoUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($brandLogoPath)
            ? \Illuminate\Support\Facades\Storage::url($brandLogoPath)
            : null;
        $brandFaviconUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($brandFaviconPath)
            ? \Illuminate\Support\Facades\Storage::url($brandFaviconPath)
            : null;
    @endphp

    @if ($brandFaviconUrl)
        <link rel="icon" type="image/png" href="{{ $brandFaviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Cpath d='M20 3L34 15H6L20 3Z' fill='%237D1A34'/%3E%3Cpath d='M14 15L34 15L34 20L17 20Z' fill='%23836C31'/%3E%3Cpath d='M14 21L34 21L34 26L17 26Z' fill='%23836C31' opacity='.75'/%3E%3Cpath d='M14 27L34 27L34 32L17 32Z' fill='%23836C31' opacity='.6'/%3E%3Cpath d='M14 33L34 33L34 37L18 37Z' fill='%23836C31' opacity='.45'/%3E%3C/svg%3E">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Cinzel:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #7D1A34;
            --primary-dark: #5C1327;
            --primary-tint: #F4E7EA;
            --secondary: #836C31;
            --secondary-light: #A68844;
            --secondary-tint: #F1ECDD;
            --dark: #1E1F22;
            --light: #F9F8F6;
            --white: #FFFFFF;

            --success: #2F6F4F;
            --success-bg: #E6F1EB;
            --danger: #A5352C;
            --danger-bg: #F8E9E7;

            --border: #E7E2D8;
            --text-muted: #756F63;

            --font-display: 'Cormorant Garamond', Georgia, serif;
            --font-accent: 'Cinzel', Georgia, serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

            --radius: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 2px rgba(30,31,34,0.06), 0 1px 6px rgba(30,31,34,0.04);
            --shadow-md: 0 8px 24px rgba(30,31,34,0.10);
            --header-h: 116px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-body);
            background: var(--light);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        ::selection { background: var(--secondary-tint); color: var(--primary-dark); }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.001ms !important; transition-duration: 0.001ms !important; }
        }

        /* ══════════════════════════════════════════════
           PRELOADER — full-screen fade, brand mark breathing
           in the center until window.load fires.
           ══════════════════════════════════════════════ */
        #sitePreloader {
            position: fixed; inset: 0; z-index: 9999;
            background: var(--dark);
            display: flex; align-items: center; justify-content: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        #sitePreloader.hide { opacity: 0; visibility: hidden; pointer-events: none; }
        .preloader-mark { width: 56px; height: 56px; animation: preloaderBreathe 1.4s ease-in-out infinite; }
        .preloader-mark img { width: 100%; height: 100%; object-fit: contain; }
        @keyframes preloaderBreathe {
            0%, 100% { opacity: 0.55; transform: scale(0.92); }
            50% { opacity: 1; transform: scale(1.04); }
        }

        /* ══════════════════════════════════════════════
           HEADER — utility bar + main nav
           ══════════════════════════════════════════════ */
        .site-header {
            position: sticky; top: 0; z-index: 500;
            background: var(--dark);
            transition: box-shadow 0.25s ease;
        }
        .site-header.scrolled { box-shadow: var(--shadow-md); }

        .header-utility {
            border-bottom: 1px solid rgba(255,255,255,0.07);
            transition: max-height 0.25s ease, opacity 0.2s ease, padding 0.25s ease;
            overflow: hidden;
            max-height: 40px;
        }
        .site-header.scrolled .header-utility { max-height: 0; opacity: 0; padding: 0; }
        .header-utility-inner {
            max-width: 1200px; margin: 0 auto; padding: 0.5rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.72rem; color: #a9a49a; letter-spacing: 0.03em;
        }
        .utility-contact { display: flex; align-items: center; gap: 1.4rem; }
        .utility-contact span { display: flex; align-items: center; gap: 0.4rem; }
        .utility-contact svg { width: 13px; height: 13px; opacity: 0.7; flex-shrink: 0; }
        .utility-social { display: flex; align-items: center; gap: 0.85rem; }
        .utility-social a { color: #a9a49a; display: flex; transition: color 0.15s; }
        .utility-social a:hover { color: var(--secondary-light); }
        .utility-social svg { width: 14px; height: 14px; }

        .site-header-inner {
            max-width: 1200px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.75rem;
            gap: 1.5rem;
        }

        .site-brand { display: flex; align-items: center; gap: 0.7rem; flex-shrink: 0; }
        .site-brand .brand-mark-img,
        .site-brand svg { width: 32px; height: 32px; flex-shrink: 0; }
        .site-brand .brand-mark-img { object-fit: contain; }
        .site-brand-text {
            font-family: var(--font-accent); font-size: 0.98rem; font-weight: 600;
            letter-spacing: 0.045em; color: var(--white); line-height: 1.15; white-space: nowrap;
        }
        .site-brand-text small {
            display: block; font-family: var(--font-body);
            font-size: 0.58rem; font-weight: 500; letter-spacing: 0.16em;
            text-transform: uppercase; color: var(--secondary-light); margin-top: 3px;
        }

        .site-nav { display: flex; align-items: center; gap: 2.1rem; flex: 1; justify-content: center; }
        .site-nav a {
            position: relative;
            font-size: 0.82rem; font-weight: 500; letter-spacing: 0.03em;
            color: #cfcdc8; padding: 0.3rem 0;
        }
        .site-nav a::after {
            content: ''; position: absolute; left: 0; bottom: -2px;
            width: 100%; height: 1.5px; background: var(--secondary-light);
            transform: scaleX(0); transform-origin: left; transition: transform 0.2s ease;
        }
        .site-nav a:hover { color: var(--white); }
        .site-nav a:hover::after, .site-nav a.active::after { transform: scaleX(1); }
        .site-nav a.active { color: var(--white); }

        .site-auth { display: flex; align-items: center; gap: 1rem; flex-shrink: 0; }
        .auth-name {
            font-size: 0.8rem; color: #cfcdc8; font-weight: 500;
            display: flex; align-items: center; gap: 0.4rem;
        }
        .auth-name svg { width: 15px; height: 15px; opacity: 0.7; }

        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.55rem 1.2rem; border-radius: 6px;
            font-family: var(--font-body); font-size: 0.8rem; font-weight: 600;
            border: none; cursor: pointer; transition: all 0.15s; white-space: nowrap;
        }
        .btn-primary { background: var(--secondary); color: var(--white); }
        .btn-primary:hover { background: var(--secondary-light); }
        .btn-ghost { background: transparent; color: var(--white); border: 1px solid rgba(255,255,255,0.28); }
        .btn-ghost:hover { border-color: var(--secondary-light); color: var(--secondary-light); }
        .btn-logout {
            background: none; border: none; cursor: pointer; color: #8f8b80;
            font-size: 0.78rem; font-family: var(--font-body); padding: 0;
        }
        .btn-logout:hover { color: var(--secondary-light); }

        .nav-toggle {
            display: none; background: none; border: none; cursor: pointer;
            color: var(--white); padding: 0.3rem;
        }
        .nav-toggle svg { width: 22px; height: 22px; }

        /* ══════════════════════════════════════════════
           SIGNATURE: the plaque — used on interior pages
           only (home page uses @section('hero') instead)
           ══════════════════════════════════════════════ */
        .page-plate {
            position: relative;
            max-width: 640px; margin: 3.5rem auto 3rem;
            padding: 2.4rem 2.6rem;
            text-align: center;
        }
        .plate-corner {
            position: absolute; width: 26px; height: 26px;
            opacity: 0; transform: scale(0.5);
            animation: plateCornerIn 0.5s ease forwards;
        }
        .plate-corner.tl { top: 0; left: 0; border-top: 2px solid var(--secondary); border-left: 2px solid var(--secondary); animation-delay: 0.05s; }
        .plate-corner.tr { top: 0; right: 0; border-top: 2px solid var(--secondary); border-right: 2px solid var(--secondary); animation-delay: 0.15s; }
        .plate-corner.bl { bottom: 0; left: 0; border-bottom: 2px solid var(--secondary); border-left: 2px solid var(--secondary); animation-delay: 0.15s; }
        .plate-corner.br { bottom: 0; right: 0; border-bottom: 2px solid var(--secondary); border-right: 2px solid var(--secondary); animation-delay: 0.25s; }
        @keyframes plateCornerIn { to { opacity: 1; transform: scale(1); } }

        .plate-eyebrow {
            font-family: var(--font-accent); font-size: 0.68rem; font-weight: 500;
            letter-spacing: 0.18em; text-transform: uppercase; color: var(--secondary);
            margin-bottom: 0.6rem;
        }
        .plate-title {
            font-family: var(--font-display); font-size: 2.5rem; font-weight: 600;
            color: var(--dark); line-height: 1.1; position: relative; display: inline-block;
        }
        .plate-title::after {
            content: ''; position: absolute; left: 50%; bottom: -10px;
            height: 2px; width: 64px; margin-left: -32px;
            background: var(--secondary);
            transform: scaleX(0); transform-origin: center;
            animation: plateRuleIn 0.6s 0.35s ease forwards;
        }
        @keyframes plateRuleIn { to { transform: scaleX(1); } }
        .plate-subtitle {
            font-size: 0.88rem; color: var(--text-muted); margin-top: 1.15rem; line-height: 1.6;
        }

        /* ══════════════════════════════════════════════
           MAIN CONTENT — base styling for unclassed
           elements, so existing plain views look intentional
           ══════════════════════════════════════════════ */
        .page-content { flex: 1; }
        .content-container { max-width: 1040px; margin: 0 auto; padding: 0 1.75rem 4rem; }

        .content-container h1 {
            font-family: var(--font-display); font-size: 1.9rem; font-weight: 600; margin-bottom: 1rem;
        }
        .content-container h3 {
            font-family: var(--font-display); font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 0.75rem;
        }
        .content-container p { color: var(--dark); line-height: 1.65; margin-bottom: 0.5rem; }
        .content-container a:not(.btn) { color: var(--primary); font-weight: 600; }
        .content-container a:not(.btn):hover { color: var(--primary-dark); text-decoration: underline; }

        .content-container table {
            width: 100%; border-collapse: collapse; background: var(--white);
            border: 1px solid var(--border); border-radius: var(--radius);
            overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 1.75rem;
        }
        .content-container th, .content-container td { padding: 0.85rem 1.1rem; text-align: left; font-size: 0.87rem; }
        .content-container th {
            background: #fbfaf7; font-weight: 700; font-size: 0.7rem; text-transform: uppercase;
            letter-spacing: 0.06em; color: var(--text-muted); border-bottom: 1px solid var(--border);
        }
        .content-container td { border-bottom: 1px solid #f2efe9; }
        .content-container tr:last-child td { border-bottom: none; }

        .content-container form {
            background: var(--white); border: 1px solid var(--border); border-radius: var(--radius);
            padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.75rem;
        }
        .content-container form label {
            display: inline-flex; flex-direction: column; gap: 0.35rem;
            font-size: 0.78rem; font-weight: 600; color: #3a372f; margin: 0 1rem 1rem 0;
        }
        .content-container input, .content-container select, .content-container textarea {
            padding: 0.6rem 0.85rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
            font-size: 0.85rem; font-family: var(--font-body); color: var(--dark);
            background: var(--light); outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .content-container input:focus, .content-container select:focus, .content-container textarea:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-tint); background: var(--white);
        }
        .content-container input[type="checkbox"] { padding: 0; width: auto; }
        .content-container textarea { width: 100%; min-height: 90px; resize: vertical; }

        .content-container button[type="submit"], .content-container button:not([type]) {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.65rem 1.3rem; border-radius: var(--radius-sm);
            font-family: var(--font-body); font-size: 0.85rem; font-weight: 600;
            border: none; cursor: pointer; background: var(--primary); color: var(--white);
            box-shadow: 0 2px 8px rgba(125,26,52,0.25); transition: background 0.15s;
        }
        .content-container button[type="submit"]:hover, .content-container button:not([type]):hover { background: var(--primary-dark); }

        /* Flash / validation messages */
        .flash-banner { max-width: 1040px; margin: 0 auto 1.5rem; padding: 0 1.75rem; }
        .flash-banner.flash-wide { max-width: none; }
        .flash-banner .flash-inner {
            border-radius: var(--radius-sm); padding: 0.9rem 1.1rem; font-size: 0.85rem;
            display: flex; align-items: flex-start; gap: 0.6rem;
        }
        .flash-error .flash-inner { background: var(--danger-bg); color: var(--danger); }
        .flash-success .flash-inner { background: var(--success-bg); color: var(--success); }
        .flash-error ul { margin: 0; padding-left: 1.1rem; }

        /* ══════════════════════════════════════════════
           FOOTER — multi-column, 5-star appropriate
           ══════════════════════════════════════════════ */
        .site-footer { background: var(--dark); color: #a9a49a; margin-top: auto; }
        .footer-top {
            max-width: 1200px; margin: 0 auto; padding: 3.5rem 1.75rem 2.5rem;
            display: grid; grid-template-columns: 1.4fr 1fr 1fr 1.2fr; gap: 2.5rem;
        }
        .footer-col-brand { display: flex; flex-direction: column; gap: 0.9rem; }
        .footer-brand-row { display: flex; align-items: center; gap: 0.6rem; }
        .footer-brand-row .brand-mark-img, .footer-brand-row svg { width: 26px; height: 26px; }
        .footer-brand-row span { font-family: var(--font-accent); font-size: 0.88rem; letter-spacing: 0.05em; color: var(--white); }
        .footer-col-brand p { font-size: 0.8rem; line-height: 1.7; color: #8f8b80; }
        .footer-social { display: flex; gap: 0.7rem; margin-top: 0.3rem; }
        .footer-social a {
            width: 32px; height: 32px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.14);
            display: flex; align-items: center; justify-content: center; color: #cfcdc8;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }
        .footer-social a:hover { background: var(--secondary); border-color: var(--secondary); color: var(--white); }
        .footer-social svg { width: 14px; height: 14px; }

        .footer-heading {
            font-family: var(--font-accent); font-size: 0.7rem; font-weight: 600;
            letter-spacing: 0.12em; text-transform: uppercase; color: var(--secondary-light);
            margin-bottom: 1.1rem;
        }
        .footer-links { list-style: none; display: flex; flex-direction: column; gap: 0.65rem; }
        .footer-links a { font-size: 0.82rem; color: #a9a49a; transition: color 0.15s, padding-left 0.15s; }
        .footer-links a:hover { color: var(--white); padding-left: 3px; }

        .footer-contact { list-style: none; display: flex; flex-direction: column; gap: 0.85rem; }
        .footer-contact li { display: flex; align-items: flex-start; gap: 0.6rem; font-size: 0.8rem; line-height: 1.5; color: #a9a49a; }
        .footer-contact svg { width: 15px; height: 15px; margin-top: 2px; color: var(--secondary-light); flex-shrink: 0; }

        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); }
        .footer-bottom-inner {
            max-width: 1200px; margin: 0 auto; padding: 1.35rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;
            font-size: 0.72rem; letter-spacing: 0.02em; color: #756f63;
        }
        .footer-bottom-inner a { color: #756f63; }
        .footer-bottom-inner a:hover { color: var(--secondary-light); }

        /* ══════════════════════════════════════════════
           RESPONSIVE
           ══════════════════════════════════════════════ */
        @media (max-width: 980px) {
            .footer-top { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 860px) {
            .header-utility { display: none; }
            .site-nav {
                position: fixed; top: var(--header-h); left: 0; right: 0; z-index: 400;
                background: var(--dark); flex-direction: column; align-items: flex-start;
                padding: 1rem 1.75rem 1.5rem; gap: 1rem;
                transform: translateY(-12px); opacity: 0; pointer-events: none;
                transition: transform 0.2s ease, opacity 0.2s ease;
                border-bottom: 1px solid rgba(255,255,255,0.08);
            }
            .site-nav.open { transform: translateY(0); opacity: 1; pointer-events: auto; }
            .nav-toggle { display: flex; }
            .plate-title { font-size: 2rem; }
            .page-plate { padding: 2rem 1.2rem; margin: 2.25rem auto 2rem; }
        }
        @media (max-width: 560px) {
            .content-container { padding: 0 1.1rem 3rem; }
            .flash-banner { padding: 0 1.1rem; }
            .footer-top { grid-template-columns: 1fr; padding: 2.75rem 1.1rem 2rem; gap: 2rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Preloader ───────────────────────────────────────────────── --}}
<div id="sitePreloader">
    <div class="preloader-mark">
        @if ($brandLogoUrl)
            <img src="{{ $brandLogoUrl }}" alt="{{ config('app.name', 'Hotel') }}">
        @else
            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 3L34 15H6L20 3Z" fill="#7D1A34"/>
                <path d="M14 15L34 15L34 20L17 20Z" fill="#836C31" opacity="0.9"/>
                <path d="M14 21L34 21L34 26L17 26Z" fill="#836C31" opacity="0.75"/>
                <path d="M14 27L34 27L34 32L17 32Z" fill="#836C31" opacity="0.6"/>
                <path d="M14 33L34 33L34 37L18 37Z" fill="#836C31" opacity="0.45"/>
            </svg>
        @endif
    </div>
</div>

{{-- ── Header ──────────────────────────────────────────────────── --}}
<header class="site-header" id="siteHeader">
    <div class="header-utility">
        <div class="header-utility-inner">
            <div class="utility-contact">
                <span>
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    +8801792304729
                </span>
              
            </div>
            <div class="utility-social">
                <a href="#" aria-label="Facebook"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0022 12z"/></svg></a>
                <a href="#" aria-label="Instagram"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg></a>
                <a href="#" aria-label="Twitter/X"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 2H22l-7.4 8.4L23 22h-6.9l-5.4-6.9L4.4 22H1.3l7.9-9L1 2h7l4.9 6.3L18.9 2z"/></svg></a>
            </div>
        </div>
    </div>

    <div class="site-header-inner">
        <a href="{{ route('home') }}" class="site-brand">
            @if ($brandLogoUrl)
                <img src="{{ $brandLogoUrl }}" alt="{{ config('app.name', 'Hotel') }}" class="brand-mark-img">
            @else
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 3L34 15H6L20 3Z" fill="#7D1A34"/>
                    <path d="M14 15L34 15L34 20L17 20Z" fill="#836C31" opacity="0.9"/>
                    <path d="M14 21L34 21L34 26L17 26Z" fill="#836C31" opacity="0.75"/>
                    <path d="M14 27L34 27L34 32L17 32Z" fill="#836C31" opacity="0.6"/>
                    <path d="M14 33L34 33L34 37L18 37Z" fill="#836C31" opacity="0.45"/>
                </svg>
            @endif
            <span class="site-brand-text">
                {{ config('app.name', 'Hotel') }}
                <small>Residence &amp; Suites</small>
            </span>
        </a>

        <nav class="site-nav" id="siteNav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('customer.rooms.index') }}" class="{{ request()->routeIs('customer.rooms.*') ? 'active' : '' }}">Rooms</a>
            <a href="{{ route('home') }}#about" >About</a>
            <a href="{{ route('home') }}#facilities">Facilities</a>
            @auth
                <a href="{{ route('customer.bookings.index') }}" class="{{ request()->routeIs('customer.bookings.*') ? 'active' : '' }}">My Bookings</a>
            @endauth
        </nav>

        <div class="site-auth">
            @auth
                <span class="auth-name">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ auth()->user()->name }}
                </span>
                <button type="button" class="btn-logout" id="siteLogoutBtn">Sign out</button>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-ghost">Sign In</a>
                @endif
            @endauth
        </div>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>
</header>

{{-- ── Main ────────────────────────────────────────────────────── --}}
<main class="page-content">

    @if (View::hasSection('hero'))
        @yield('hero')
    @else
        <div class="page-plate">
            <span class="plate-corner tl"></span>
            <span class="plate-corner tr"></span>
            <span class="plate-corner bl"></span>
            <span class="plate-corner br"></span>
            <p class="plate-eyebrow">@yield('page-eyebrow', 'The Residence')</p>
            <h1 class="plate-title">@yield('page-title', config('app.name', 'Hotel'))</h1>
            <p class="plate-subtitle">@yield('page-subtitle', '')</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="flash-banner flash-error">
            <div class="flash-inner">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="flash-banner flash-error"><div class="flash-inner">{{ session('error') }}</div></div>
    @endif

    @if (session('success'))
        <div class="flash-banner flash-success"><div class="flash-inner">{{ session('success') }}</div></div>
    @endif

    @hasSection('hero')
        @yield('content')
    @else
        <div class="content-container">
            @yield('content')
        </div>
    @endif
</main>

{{-- ── Footer ──────────────────────────────────────────────────── --}}
<footer class="site-footer">
    <div class="footer-top">
        <div class="footer-col-brand">
            <div class="footer-brand-row">
                @if ($brandLogoUrl)
                    <img src="{{ $brandLogoUrl }}" alt="{{ config('app.name', 'Hotel') }}" class="brand-mark-img">
                @else
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 3L34 15H6L20 3Z" fill="#836C31"/></svg>
                @endif
                <span>{{ config('app.name', 'Hotel') }} Residence &amp; Suites</span>
            </div>
            <p>A five-star residence dedicated to quiet luxury — refined rooms, attentive service, and an address worth returning to.</p>
            <div class="footer-social">
                <a href="#" aria-label="Facebook"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0022 12z"/></svg></a>
                <a href="#" aria-label="Instagram"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg></a>
                <a href="#" aria-label="Twitter/X"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 2H22l-7.4 8.4L23 22h-6.9l-5.4-6.9L4.4 22H1.3l7.9-9L1 2h7l4.9 6.3L18.9 2z"/></svg></a>
            </div>
        </div>

        <div>
            <p class="footer-heading">Explore</p>
            <ul class="footer-links">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('customer.rooms.index') }}">Rooms &amp; Suites</a></li>
                <li><a href="{{ route('home') }}#facilities">Facilities</a></li>
                <li><a href="{{ route('home') }}#about">About Us</a></li>
            </ul>
        </div>

        <div>
            <p class="footer-heading">Account</p>
            <ul class="footer-links">
                @auth
                    <li><a href="{{ route('customer.bookings.index') }}">My Bookings</a></li>
                @else
                    @if (Route::has('login'))
                        <li><a href="{{ route('login') }}">Sign In</a></li>
                    @endif
                @endauth
                <li><a href="{{ route('customer.rooms.index') }}">Book a Stay</a></li>
            </ul>
        </div>

        <div>
            <p class="footer-heading">Contact</p>
            <ul class="footer-contact">
                <li>
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>House 00, Road 00, Rajshahi, Bangladesh</span>
                </li>
                <li>
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>+880 1XXX-XXXXXX</span>
                </li>
                <li>
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>reservations@{{ Str::slug(config('app.name', 'hotel')) }}.com</span>
                </li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-bottom-inner">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'Hotel') }}. All rights reserved.</span>
            <span>Crafted for a five-star experience.</span>
        </div>
    </div>
</footer>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Preloader — fade out once everything (including images) has loaded
window.addEventListener('load', () => {
    const pre = document.getElementById('sitePreloader');
    setTimeout(() => pre.classList.add('hide'), 250);
});
// Safety net: never let the preloader outlive 3.5s even on a slow connection
setTimeout(() => document.getElementById('sitePreloader')?.classList.add('hide'), 3500);

// Sticky header shadow + utility bar collapse on scroll
const siteHeader = document.getElementById('siteHeader');
window.addEventListener('scroll', () => {
    siteHeader.classList.toggle('scrolled', window.scrollY > 8);
});

// Mobile nav toggle
document.getElementById('navToggle').addEventListener('click', () => {
    document.getElementById('siteNav').classList.toggle('open');
});

// Sign out
const logoutBtn = document.getElementById('siteLogoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function () {
        fetch('/logout', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
            .then(res => res.json())
            .then(json => { window.location.href = json.redirect || '/login'; })
            .catch(() => { window.location.href = '/login'; });
    });
}
</script>

@stack('scripts')
</body>
</html>