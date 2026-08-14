<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Hotel') }} Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Cinzel:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ══════════════════════════════════════════════════════════
           DESIGN TOKENS — derived from the ADI International hotel
           mark: maroon roofline / gold rays / charcoal type / warm
           paper background.
           ══════════════════════════════════════════════════════════ */
        :root {
            /* Brand */
            --primary: #7D1A34;         /* maroon — primary actions, active states */
            --primary-dark: #5C1327;    /* maroon hover/pressed */
            --primary-tint: #F4E7EA;    /* maroon @ 8% for soft fills */
            --secondary: #836C31;       /* gold — accents, secondary emphasis */
            --secondary-light: #A68844; /* gold hover/lighter */
            --secondary-tint: #F1ECDD;  /* gold @ 10% for soft fills */
            --dark: #1E1F22;            /* charcoal — sidebar, primary text */
            --light: #F9F8F6;           /* warm paper — page background */
            --white: #FFFFFF;

            /* Semantic (muted to sit inside the warm palette, not clash with it) */
            --success: #2F6F4F;
            --success-bg: #E6F1EB;
            --danger: #A5352C;
            --danger-bg: #F8E9E7;
            --warning: #97711F;
            --warning-bg: #F6EEDD;
            --info: #3A5A78;
            --info-bg: #E8EEF3;

            --border: #E7E2D8;
            --text-muted: #756F63;

            /* Type */
            --font-display: 'Cormorant Garamond', Georgia, serif;
            --font-accent: 'Cinzel', Georgia, serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

            /* Layout */
            --sidebar-w: 264px;
            --sidebar-w-collapsed: 76px;
            --topbar-h: 68px;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 2px rgba(30,31,34,0.06), 0 1px 6px rgba(30,31,34,0.04);
            --shadow-md: 0 8px 24px rgba(30,31,34,0.10);
            --shadow-lg: 0 20px 60px rgba(30,31,34,0.18);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-body);
            background: var(--light);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; }

        ::selection { background: var(--secondary-tint); color: var(--primary-dark); }

        /* Respect reduced-motion preference */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.001ms !important; transition-duration: 0.001ms !important; }
        }

        /* ══════════════════════════════════════════════════════════
           TOP LOADING BAR — thin brand-gradient bar that sweeps in
           during any AJAX call. Controlled via PageLoader.start()/done().
           ══════════════════════════════════════════════════════════ */
        #pageLoader {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            z-index: 3000;
            transition: width 0.3s ease, opacity 0.3s ease;
            opacity: 0;
        }
        #pageLoader.active { opacity: 1; }

        /* ══════════════════════════════════════════════════════════
           SIDEBAR
           ══════════════════════════════════════════════════════════ */
        .admin-sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--dark);
            color: #cfcdc8;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 200;
            transition: transform 0.28s ease;
            border-right: 1px solid rgba(255,255,255,0.06);
        }

        .sidebar-brand {
            height: var(--topbar-h);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1.35rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        /* Abstract mark echoing the roofline + rays motif from the hotel crest */
        .brand-mark { width: 30px; height: 30px; flex-shrink: 0; }

        .brand-name {
            font-family: var(--font-accent);
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: var(--white);
            line-height: 1.15;
            white-space: nowrap;
        }

        .brand-name small {
            display: block;
            font-family: var(--font-body);
            font-size: 0.6rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--secondary-light);
            margin-top: 2px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1.1rem 0.85rem;
            overflow-y: auto;
        }

        .nav-section-label {
            font-family: var(--font-accent);
            font-size: 0.62rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #7d786d;
            padding: 0.9rem 0.7rem 0.4rem;
        }
        .nav-section-label:first-child { padding-top: 0.2rem; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.62rem 0.75rem;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: #b8b5ac;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
            margin-bottom: 2px;
            position: relative;
        }

        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.85; }

        .nav-item:hover { background: rgba(255,255,255,0.06); color: var(--white); }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(125,26,52,0.35), rgba(125,26,52,0.08));
            color: var(--white);
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -0.85rem; top: 0.35rem; bottom: 0.35rem;
            width: 3px;
            background: var(--secondary-light);
            border-radius: 0 3px 3px 0;
        }

        .nav-item .nav-badge {
            margin-left: auto;
            font-size: 0.62rem;
            font-weight: 700;
            background: var(--secondary);
            color: var(--dark);
            padding: 0.1rem 0.42rem;
            border-radius: 20px;
        }

        .sidebar-footer {
            padding: 0.9rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.55rem 0.6rem;
            border-radius: var(--radius-sm);
            background: rgba(255,255,255,0.04);
        }

        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            display: flex; align-items: center; justify-content: center;
            font-family: var(--font-accent);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--white);
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-info strong {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--white);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .user-info span {
            font-size: 0.68rem;
            color: #8f8b80;
            text-transform: capitalize;
        }

        .logout-btn {
            background: none; border: none; cursor: pointer;
            color: #8f8b80; padding: 0.3rem; border-radius: 6px;
            display: flex; transition: color 0.15s, background 0.15s;
        }
        .logout-btn:hover { color: var(--white); background: var(--primary); }
        .logout-btn svg { width: 17px; height: 17px; }

        /* ══════════════════════════════════════════════════════════
           MAIN / TOPBAR
           ══════════════════════════════════════════════════════════ */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
        }

        .topbar {
            height: var(--topbar-h);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .topbar-left h2 {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            line-height: 1.1;
            position: relative;
            display: inline-block;
        }
        /* A gold hairline that draws itself in under the page title on load — the layout's signature flourish. */
        .topbar-left h2::after {
            content: '';
            position: absolute;
            left: 0; bottom: -5px;
            height: 2px;
            width: 100%;
            background: var(--secondary);
            transform: scaleX(0);
            transform-origin: left;
            animation: drawUnderline 0.6s 0.15s ease forwards;
        }
        @keyframes drawUnderline { to { transform: scaleX(1); } }

        .topbar-left p { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem; }

        .topbar-right { display: flex; align-items: center; gap: 0.9rem; }

        .topbar-date {
            font-size: 0.78rem;
            color: var(--text-muted);
            background: var(--light);
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            white-space: nowrap;
        }

        .hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            color: var(--dark); padding: 0.3rem;
        }
        .hamburger svg { width: 22px; height: 22px; }

        .page-content { flex: 1; padding: 1.75rem; }

        .overlay-bg {
            display: none;
            position: fixed; inset: 0;
            background: rgba(30,31,34,0.5);
            z-index: 190;
        }
        .overlay-bg.open { display: block; }

        /* ══════════════════════════════════════════════════════════
           TOASTS
           ══════════════════════════════════════════════════════════ */
        #toastStack {
            position: fixed;
            bottom: 1.5rem; right: 1.5rem;
            z-index: 4000;
            display: flex;
            flex-direction: column-reverse;
            gap: 0.6rem;
            max-width: 360px;
        }

        .toast {
            background: var(--white);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-md);
            padding: 0.85rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            border-left: 4px solid var(--info);
            overflow: hidden;
            position: relative;
            animation: toastIn 0.25s ease;
        }
        @keyframes toastIn { from { opacity: 0; transform: translateX(16px); } to { opacity: 1; transform: translateX(0); } }
        .toast.leaving { animation: toastOut 0.2s ease forwards; }
        @keyframes toastOut { to { opacity: 0; transform: translateX(16px); } }

        .toast.success { border-left-color: var(--success); }
        .toast.error   { border-left-color: var(--danger); }
        .toast.warning { border-left-color: var(--warning); }

        .toast-icon {
            width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px;
            color: var(--info);
        }
        .toast.success .toast-icon { color: var(--success); }
        .toast.error   .toast-icon { color: var(--danger); }
        .toast.warning .toast-icon { color: var(--warning); }

        .toast-body { flex: 1; font-size: 0.83rem; color: var(--dark); line-height: 1.4; }
        .toast-close { background: none; border: none; cursor: pointer; color: var(--text-muted); flex-shrink: 0; padding: 0; }
        .toast-close svg { width: 14px; height: 14px; }

        .toast-progress {
            position: absolute; left: 0; bottom: 0; height: 2px;
            background: currentColor; opacity: 0.35;
            animation: toastProgress linear forwards;
        }
        @keyframes toastProgress { from { width: 100%; } to { width: 0%; } }

        /* ══════════════════════════════════════════════════════════
           MODAL
           ══════════════════════════════════════════════════════════ */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(30,31,34,0.5);
            backdrop-filter: blur(3px);
            z-index: 1000;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding: 4vh 1rem;
            overflow-y: auto;
        }
        .modal-overlay.open { display: flex; }

        .modal {
            background: var(--white);
            border-radius: 18px;
            width: 100%;
            max-width: 540px;
            box-shadow: var(--shadow-lg);
            animation: modalIn 0.2s ease;
        }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.96) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.15rem 1.4rem;
            border-bottom: 1px solid var(--border);
        }
        .modal-header h3 { font-family: var(--font-display); font-size: 1.35rem; font-weight: 600; color: var(--dark); }

        .modal-close {
            background: var(--light); border: none; cursor: pointer;
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); transition: background 0.15s;
        }
        .modal-close:hover { background: var(--primary-tint); color: var(--primary); }
        .modal-close svg { width: 15px; height: 15px; }

        .modal-body { padding: 1.4rem; max-height: 65vh; overflow-y: auto; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 0.7rem; padding: 1rem 1.4rem; border-top: 1px solid var(--border); }

        /* ══════════════════════════════════════════════════════════
           BUTTONS
           ══════════════════════════════════════════════════════════ */
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.6rem 1.15rem; border-radius: var(--radius-sm);
            font-family: var(--font-body); font-size: 0.85rem; font-weight: 600;
            border: none; cursor: pointer; transition: all 0.15s; text-decoration: none;
            white-space: nowrap;
        }
        .btn svg { width: 16px; height: 16px; }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .btn-primary { background: var(--primary); color: var(--white); box-shadow: 0 2px 8px rgba(125,26,52,0.25); }
        .btn-primary:hover:not(:disabled) { background: var(--primary-dark); }

        .btn-gold { background: var(--secondary); color: var(--white); }
        .btn-gold:hover:not(:disabled) { background: var(--secondary-light); }

        .btn-secondary { background: var(--light); color: var(--dark); border: 1px solid var(--border); }
        .btn-secondary:hover:not(:disabled) { background: #f1efe9; }

        .btn-danger { background: var(--danger-bg); color: var(--danger); }
        .btn-danger:hover:not(:disabled) { background: #f1d8d5; }

        .btn-sm { padding: 0.38rem 0.75rem; font-size: 0.78rem; }

        .btn-spinner {
            width: 14px; height: 14px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ══════════════════════════════════════════════════════════
           FORMS
           ══════════════════════════════════════════════════════════ */
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #3a372f; margin-bottom: 0.4rem; }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 0.65rem 0.9rem;
            border: 1.5px solid var(--border); border-radius: var(--radius-sm);
            font-size: 0.875rem; font-family: var(--font-body); color: var(--dark);
            background: var(--light); outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-tint);
            background: var(--white);
        }
        .form-group .hint { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.3rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        .form-check { display: flex; align-items: center; gap: 0.55rem; }
        .form-check input { width: auto; }
        .form-check label { margin: 0; font-weight: 500; }

        /* ══════════════════════════════════════════════════════════
           TABLE + LIVE SEARCH + SKELETON LOADER
           ══════════════════════════════════════════════════════════ */
        .table-wrap {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-toolbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);
            gap: 1rem; flex-wrap: wrap;
        }
        .table-toolbar-title { font-family: var(--font-display); font-size: 1.2rem; font-weight: 600; color: var(--dark); }
        .table-toolbar-actions { display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; }

        .search-box {
            display: flex; align-items: center; gap: 0.5rem;
            border: 1.5px solid var(--border); border-radius: 20px;
            padding: 0.45rem 0.9rem; background: var(--light);
            transition: border-color 0.15s;
        }
        .search-box:focus-within { border-color: var(--primary); }
        .search-box svg { width: 15px; height: 15px; color: var(--text-muted); flex-shrink: 0; }
        .search-box input { border: none; background: none; outline: none; font-size: 0.85rem; color: var(--dark); width: 190px; font-family: var(--font-body); }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
            color: var(--text-muted); padding: 0.75rem 1.25rem; background: #fbfaf7;
            text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap;
        }
        tbody td { padding: 0.85rem 1.25rem; font-size: 0.86rem; color: var(--dark); border-bottom: 1px solid #f2efe9; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fdfcfa; }
        tbody tr.row-hidden { display: none; }

        .table-empty-row td { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); font-size: 0.85rem; }

        /* Skeleton shimmer for "smart loader" table state */
        .skeleton-bar {
            height: 12px; border-radius: 4px; width: 100%;
            background: linear-gradient(90deg, #f0ede5 25%, #e7e2d6 37%, #f0ede5 63%);
            background-size: 400% 100%;
            animation: shimmer 1.4s ease infinite;
        }
        @keyframes shimmer { 0% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }

        .inline-spinner {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 2rem; color: var(--text-muted); font-size: 0.85rem; width: 100%;
        }
        .inline-spinner .dot-spinner {
            width: 16px; height: 16px; border-radius: 50%;
            border: 2px solid var(--border); border-top-color: var(--primary);
            animation: spin 0.7s linear infinite;
        }

        /* ══════════════════════════════════════════════════════════
           BADGES + SMART STATUS CHIP
           ══════════════════════════════════════════════════════════ */
        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.68rem; font-weight: 700; padding: 0.22rem 0.6rem;
            border-radius: 20px; text-transform: capitalize; white-space: nowrap;
        }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-green  { background: var(--success-bg); color: var(--success); }
        .badge-red    { background: var(--danger-bg);  color: var(--danger); }
        .badge-blue   { background: var(--info-bg);    color: var(--info); }
        .badge-gold   { background: var(--secondary-tint); color: var(--secondary); }
        .badge-gray   { background: #eeece6; color: var(--text-muted); }

        /* Status chip: click to reveal a shared popover of options (used for room status, booking status, etc.) */
        .status-chip {
            display: inline-flex; align-items: center; gap: 0.35rem;
            font-size: 0.72rem; font-weight: 700; padding: 0.28rem 0.65rem 0.28rem 0.5rem;
            border-radius: 20px; cursor: pointer; border: 1px solid transparent;
            text-transform: capitalize; transition: filter 0.15s;
        }
        .status-chip:hover { filter: brightness(0.97); }
        .status-chip::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .status-chip svg { width: 11px; height: 11px; margin-left: 0.1rem; opacity: 0.6; }
        .status-chip.is-updating { opacity: 0.55; pointer-events: none; }

        /* Single shared popover, anchored to <body> with position:fixed so it is never
           clipped by a scrolling/rounded-corner ancestor like .table-wrap. */
        .status-popover {
            position: fixed; z-index: 3500;
            background: var(--white); border: 1px solid var(--border); border-radius: 10px;
            box-shadow: var(--shadow-md); padding: 0.35rem; min-width: 150px;
            display: none;
        }
        .status-popover.open { display: block; animation: popoverIn 0.12s ease; }
        @keyframes popoverIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
        .status-popover button {
            display: flex; align-items: center; gap: 0.5rem; width: 100%;
            padding: 0.45rem 0.6rem; border: none; background: none; cursor: pointer;
            border-radius: 6px; font-size: 0.8rem; font-family: var(--font-body); color: var(--dark);
            text-align: left; text-transform: capitalize;
        }
        .status-popover button:hover { background: var(--light); }
        .status-popover button .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

        /* Toggle switch: for binary states (active/inactive, enabled/disabled) */
        .toggle-switch {
            position: relative; width: 40px; height: 22px; border-radius: 20px;
            background: var(--border); border: none; cursor: pointer; flex-shrink: 0;
            transition: background 0.2s; padding: 0; vertical-align: middle;
        }
        .toggle-switch.is-on { background: var(--success); }
        .toggle-switch.is-updating { opacity: 0.6; pointer-events: none; }
        .toggle-knob {
            position: absolute; top: 2px; left: 2px; width: 18px; height: 18px;
            border-radius: 50%; background: var(--white); box-shadow: 0 1px 3px rgba(0,0,0,0.25);
            transition: transform 0.2s;
        }
        .toggle-switch.is-on .toggle-knob { transform: translateX(18px); }
        .toggle-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); margin-left: 0.55rem; vertical-align: middle; }

        /* ══════════════════════════════════════════════════════════
           STAT CARDS
           ══════════════════════════════════════════════════════════ */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1.1rem; margin-bottom: 1.75rem; }
        .stat-card { background: var(--white); border-radius: var(--radius); padding: 1.3rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stat-icon svg { width: 22px; height: 22px; }
        .stat-info p { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.2rem; }
        .stat-info strong { font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: var(--dark); line-height: 1; }
        .stat-info small { display: block; font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem; }

        /* ══════════════════════════════════════════════════════════
           RESPONSIVE
           ══════════════════════════════════════════════════════════ */
        @media (max-width: 900px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); box-shadow: 6px 0 24px rgba(0,0,0,0.2); }
            .main-wrap { margin-left: 0; }
            .hamburger { display: flex; }
            .form-row { grid-template-columns: 1fr; }
            .topbar-date { display: none; }
        }
        @media (max-width: 560px) {
            .page-content { padding: 1.1rem; }
            .search-box input { width: 130px; }
            .modal { border-radius: 14px; }
            #toastStack { left: 1rem; right: 1rem; max-width: none; }
        }
    </style>

    @stack('styles')
</head>
<body>

<div id="pageLoader"></div>
<div class="overlay-bg" id="overlayBg" onclick="Sidebar.close()"></div>

{{-- ── Sidebar ─────────────────────────────────────────────────── --}}
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <svg class="brand-mark" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 3L34 15H6L20 3Z" fill="#7D1A34"/>
            <path d="M14 15L34 15L34 20L17 20Z" fill="#836C31" opacity="0.9"/>
            <path d="M14 21L34 21L34 26L17 26Z" fill="#836C31" opacity="0.75"/>
            <path d="M14 27L34 27L34 32L17 32Z" fill="#836C31" opacity="0.6"/>
            <path d="M14 33L34 33L34 37L18 37Z" fill="#836C31" opacity="0.45"/>
        </svg>
        <div class="brand-name">
            {{ config('app.name', 'Hotel') }}
            <small>Admin Panel</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        @include('layouts.admin.sidebar')
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div class="user-info">
                <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                <span>{{ auth()->user()->role ?? 'admin' }}</span>
            </div>
            <button type="button" class="logout-btn" title="Logout" id="adminLogoutBtn">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </div>
    </div>
</aside>

{{-- ── Main ────────────────────────────────────────────────────── --}}
<div class="main-wrap">
    <header class="topbar">
        <div style="display:flex; align-items:center; gap:0.85rem;">
            <button class="hamburger" onclick="Sidebar.toggle()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="topbar-left">
                <h2>@yield('page-title', 'Dashboard')</h2>
                <p>@yield('page-subtitle', '')</p>
            </div>
        </div>
        <div class="topbar-right">
            <span class="topbar-date" id="topbarDate"></span>
        </div>
    </header>

    <main class="page-content">
        @if (session('success'))
            <script>document.addEventListener('DOMContentLoaded', () => Toast.show(@json(session('success')), 'success'));</script>
        @endif
        @if (session('error'))
            <script>document.addEventListener('DOMContentLoaded', () => Toast.show(@json(session('error')), 'error'));</script>
        @endif

        @yield('content')
    </main>
</div>

{{-- ── Toast stack ─────────────────────────────────────────────── --}}
<div id="toastStack"></div>

{{-- ── Global smart modal ──────────────────────────────────────── --}}
<div class="modal-overlay" id="globalModal" onclick="Modal._backdropClick(event)">
    <div class="modal" id="modalBox">
        <div class="modal-header">
            <h3 id="modalTitle">Modal Title</h3>
            <button type="button" class="modal-close" onclick="Modal.close()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer" id="modalFooter"></div>
    </div>
</div>

<script>
/* ══════════════════════════════════════════════════════════════════
   MASTER LAYOUT JS TOOLKIT
   Every child page can rely on these globals — no need to reimplement
   modals, toasts, loaders, or search per page.

     PageLoader.start() / .done()             — top brand-gradient bar
     Toast.show(message, type, duration)      — type: success|error|warning|info
     Modal.open(title, bodyHtml, footerHtml)  — smart modal
     Modal.close()
     Modal.setSubmitting(buttonEl, isLoading, label) — spinner in a modal button
     Modal.confirm({ title, message, confirmLabel, danger, onConfirm }) — confirm dialog
     Loader.skeletonRows(colCount, rowCount)  — returns <tr> HTML for table loading state
     Loader.emptyRow(colCount, message)       — returns <tr> HTML for "no results"
     LiveSearch.attach(inputEl, tbodySelector, { emptyColCount }) — instant client-side row filter
     StatusChip.init(rootEl)                  — wires up any .status-chip inside rootEl (call after re-rendering rows)
     ToggleSwitch.init(rootEl)                — wires up any .toggle-switch inside rootEl, for binary states

   CSRF token is read once and reused by any fetch() child pages write themselves:
     const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
   ══════════════════════════════════════════════════════════════════ */

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── Sidebar (mobile off-canvas) ─────────────────────────────────────
const Sidebar = {
    toggle() {
        document.getElementById('adminSidebar').classList.toggle('open');
        document.getElementById('overlayBg').classList.toggle('open');
    },
    close() {
        document.getElementById('adminSidebar').classList.remove('open');
        document.getElementById('overlayBg').classList.remove('open');
    }
};

// ── Top page loader ──────────────────────────────────────────────────
const PageLoader = (() => {
    let active = 0;
    const el = document.getElementById('pageLoader');
    return {
        start() {
            active++;
            el.classList.add('active');
            el.style.width = '0%';
            requestAnimationFrame(() => { el.style.width = '75%'; });
        },
        done() {
            active = Math.max(0, active - 1);
            if (active === 0) {
                el.style.width = '100%';
                setTimeout(() => { el.classList.remove('active'); el.style.width = '0%'; }, 250);
            }
        }
    };
})();

// ── Toasts ────────────────────────────────────────────────────────────
const Toast = (() => {
    const stack = document.getElementById('toastStack');
    const icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
        error:   '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.93 19h14.14c1.4 0 2.27-1.52 1.58-2.75L13.58 4.75a1.8 1.8 0 00-3.16 0L3.35 16.25C2.66 17.48 3.53 19 4.93 19z"/>',
        info:    '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    };
    function show(message, type = 'info', duration = 4000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <svg class="toast-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">${icons[type] || icons.info}</svg>
            <div class="toast-body">${message}</div>
            <button class="toast-close" aria-label="Dismiss">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="toast-progress" style="animation-duration:${duration}ms; color:inherit"></div>`;
        toast.querySelector('.toast-close').addEventListener('click', () => remove(toast));
        stack.appendChild(toast);
        const timer = setTimeout(() => remove(toast), duration);
        toast.addEventListener('mouseenter', () => clearTimeout(timer));
    }
    function remove(toast) {
        toast.classList.add('leaving');
        setTimeout(() => toast.remove(), 200);
    }
    return { show };
})();

// ── Modal ─────────────────────────────────────────────────────────────
const Modal = (() => {
    const overlay = document.getElementById('globalModal');
    let lastFocused = null;

    function open(title, bodyHtml, footerHtml = '') {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalBody').innerHTML = bodyHtml;
        document.getElementById('modalFooter').innerHTML = footerHtml;
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        lastFocused = document.activeElement;
        StatusChip.init(document.getElementById('modalBody'));
        ToggleSwitch.init(document.getElementById('modalBody'));
    }
    function close() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        if (lastFocused) lastFocused.focus();
    }
    function _backdropClick(e) { if (e.target === overlay) close(); }

    function setSubmitting(buttonEl, isLoading, label) {
        if (!buttonEl) return;
        if (isLoading) {
            buttonEl.dataset.originalLabel = buttonEl.innerHTML;
            buttonEl.innerHTML = `<span class="btn-spinner"></span> ${label || 'Saving...'}`;
            buttonEl.disabled = true;
        } else {
            buttonEl.innerHTML = buttonEl.dataset.originalLabel || label || 'Save';
            buttonEl.disabled = false;
        }
    }

    function confirm({ title = 'Are you sure?', message = '', confirmLabel = 'Confirm', danger = true, onConfirm }) {
        const body = `<p style="color:var(--text-muted); font-size:0.9rem; line-height:1.6;">${message}</p>`;
        const footer = `
            <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancel</button>
            <button type="button" class="btn ${danger ? 'btn-danger' : 'btn-primary'}" id="modalConfirmBtn">${confirmLabel}</button>`;
        open(title, body, footer);
        document.getElementById('modalConfirmBtn').addEventListener('click', async function () {
            setSubmitting(this, true, 'Please wait...');
            try {
                await onConfirm();
                close();
            } catch (err) {
                setSubmitting(this, false);
                Toast.show(err.message || 'Something went wrong.', 'error');
            }
        });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

    return { open, close, _backdropClick, setSubmitting, confirm };
})();

// ── Skeleton / empty-state table helpers ───────────────────────────────
const Loader = {
    skeletonRows(colCount, rowCount = 4) {
        let rows = '';
        for (let r = 0; r < rowCount; r++) {
            let cells = '';
            for (let c = 0; c < colCount; c++) {
                const w = 55 + Math.round(Math.random() * 35);
                cells += `<td><div class="skeleton-bar" style="width:${w}%"></div></td>`;
            }
            rows += `<tr>${cells}</tr>`;
        }
        return rows;
    },
    emptyRow(colCount, message = 'No records found.') {
        return `<tr class="table-empty-row"><td colspan="${colCount}">${message}</td></tr>`;
    }
};

// ── Live search (instant client-side filter) ────────────────────────────
const LiveSearch = {
    attach(inputEl, tbodySelector, opts = {}) {
        if (!inputEl) return;
        let debounceTimer;
        inputEl.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => this._filter(inputEl.value, tbodySelector, opts), 120);
        });
    },
    _filter(query, tbodySelector, opts) {
        const tbody = document.querySelector(tbodySelector);
        if (!tbody) return;
        const q = query.trim().toLowerCase();
        const rows = tbody.querySelectorAll('tr:not(.table-empty-row):not(.skeleton-row)');
        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const match = q === '' || text.includes(q);
            row.classList.toggle('row-hidden', !match);
            if (match) visibleCount++;
        });
        let noResultsRow = tbody.querySelector('.no-results-row');
        if (visibleCount === 0 && q !== '') {
            if (!noResultsRow) {
                const colCount = opts.emptyColCount || (rows[0] ? rows[0].children.length : 5);
                tbody.insertAdjacentHTML('beforeend', `<tr class="table-empty-row no-results-row"><td colspan="${colCount}">No matches for "${query}".</td></tr>`);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }
};

// ── Smart status chip (click → popover → AJAX PATCH → toast) ───────────
// Usage: <span class="status-chip" data-current="clean"
//          data-endpoint="/admin/rooms/12/status" data-payload-key="status"
//          data-options='[{"value":"clean","label":"Clean","color":"#2F6F4F"},{"value":"dirty","label":"Dirty","color":"#97711F"}]'>Clean</span>
// The popover is rendered into a single shared element anchored to <body> with
// position:fixed, positioned via getBoundingClientRect — this keeps it from being
// clipped by any ancestor's overflow:hidden (e.g. rounded-corner table containers).
const StatusChip = (() => {
    let sharedPopover = null;
    let activeChip = null;

    function ensurePopover() {
        if (sharedPopover) return sharedPopover;
        sharedPopover = document.createElement('div');
        sharedPopover.className = 'status-popover';
        document.body.appendChild(sharedPopover);
        return sharedPopover;
    }

    function options(chip) { return JSON.parse(chip.dataset.options); }
    function colorFor(chip, value) {
        const opt = options(chip).find(o => o.value === value);
        return opt ? (opt.color || '#756F63') : '#756F63';
    }
    function labelFor(chip, value) {
        const opt = options(chip).find(o => o.value === value);
        return opt ? opt.label : value;
    }

    function render(chip) {
        const current = chip.dataset.current;
        chip.style.color = colorFor(chip, current);
        chip.style.background = colorFor(chip, current) + '1a';
        chip.innerHTML = `${labelFor(chip, current)}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>`;
    }

    function closePopover() {
        if (sharedPopover) sharedPopover.classList.remove('open');
        activeChip = null;
    }

    function togglePopover(chip) {
        if (activeChip === chip) { closePopover(); return; }
        activeChip = chip;

        const popover = ensurePopover();
        popover.innerHTML = options(chip).map(o => `
            <button type="button" data-value="${o.value}">
                <span class="dot" style="background:${o.color || '#756F63'}"></span> ${o.label}
            </button>`).join('');

        popover.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                closePopover();
                update(chip, btn.dataset.value);
            });
        });

        const rect = chip.getBoundingClientRect();
        popover.style.position = 'fixed';
        popover.style.top = `${rect.bottom + 6}px`;
        popover.style.left = `${rect.left}px`;
        popover.classList.add('open');

        // Flip to the right edge if it would overflow the viewport.
        requestAnimationFrame(() => {
            const pRect = popover.getBoundingClientRect();
            if (pRect.right > window.innerWidth - 8) {
                popover.style.left = `${Math.max(8, window.innerWidth - pRect.width - 8)}px`;
            }
        });
    }

    async function update(chip, newValue) {
        const previous = chip.dataset.current;
        if (newValue === previous) return;
        chip.classList.add('is-updating');
        try {
            const res = await fetch(chip.dataset.endpoint, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ [chip.dataset.payloadKey || 'status']: newValue })
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Update failed.');
            chip.dataset.current = newValue;
            render(chip);
            Toast.show(json.message || 'Updated successfully.', 'success');
        } catch (err) {
            Toast.show(err.message, 'error');
        } finally {
            chip.classList.remove('is-updating');
        }
    }

    // Registered once, ever — not per init() call — so closing-on-outside-click never stops working.
    document.addEventListener('click', (e) => {
        if (sharedPopover && !sharedPopover.contains(e.target) && !e.target.closest('.status-chip')) {
            closePopover();
        }
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePopover(); });

    return {
        init(root = document) {
            root.querySelectorAll('.status-chip[data-options]').forEach(chip => {
                if (chip.dataset.bound) return;
                chip.dataset.bound = '1';
                render(chip);
                chip.addEventListener('click', (e) => {
                    e.stopPropagation();
                    togglePopover(chip);
                });
            });
        },
        // Exposed so a page can override update behavior for endpoints with nonstandard semantics
        // (e.g. a toggle endpoint that flips server-side rather than accepting a target value).
        _render: render,
        _update: update,
    };
})();

// ── Smart toggle switch (binary states: active/inactive, enabled/disabled, etc.) ─
// Usage: <button type="button" class="toggle-switch" role="switch"
//          data-endpoint="/admin/room-types/12/toggle" data-state-key="is_active"
//          aria-checked="true"><span class="toggle-knob"></span></button>
// Optionally pair with <span class="toggle-label" data-on="Active" data-off="Inactive"></span>
// as the very next sibling to get its text kept in sync automatically.
const ToggleSwitch = {
    init(root = document) {
        root.querySelectorAll('.toggle-switch[data-endpoint]').forEach(el => {
            if (el.dataset.bound) return;
            el.dataset.bound = '1';
            el.addEventListener('click', () => this._flip(el));
        });
    },
    async _flip(el) {
        if (el.classList.contains('is-updating')) return;
        el.classList.add('is-updating');
        try {
            const res = await fetch(el.dataset.endpoint, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Update failed.');
            const stateKey = el.dataset.stateKey || 'is_active';
            const newState = !!json[stateKey];
            el.classList.toggle('is-on', newState);
            el.setAttribute('aria-checked', newState ? 'true' : 'false');
            const label = el.nextElementSibling;
            if (label && label.classList.contains('toggle-label')) {
                label.textContent = newState ? (label.dataset.on || 'Active') : (label.dataset.off || 'Inactive');
            }
            Toast.show(json.message || 'Updated successfully.', 'success');
        } catch (err) {
            Toast.show(err.message, 'error');
        } finally {
            el.classList.remove('is-updating');
        }
    }
};

// ── Live clock/date in topbar ────────────────────────────────────────
(function updateDate() {
    const el = document.getElementById('topbarDate');
    const now = new Date();
    el.textContent = now.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
})();

// ── Logout ────────────────────────────────────────────────────────────
document.getElementById('adminLogoutBtn').addEventListener('click', function () {
    PageLoader.start();
    fetch('/logout', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
        .then(res => res.json())
        .then(json => { window.location.href = json.redirect || '/login'; })
        .catch(() => { window.location.href = '/login'; })
        .finally(() => PageLoader.done());
});

// ── Wrap fetch globally so PageLoader reacts to every AJAX call child pages make ──
(function () {
    const originalFetch = window.fetch;
    window.fetch = function (...args) {
        PageLoader.start();
        return originalFetch.apply(this, args).finally(() => PageLoader.done());
    };
})();

// ── Highlight active nav link (fallback for non route()-based hrefs) ───
document.querySelectorAll('.nav-item').forEach(link => {
    if (link.getAttribute('href') === window.location.pathname) link.classList.add('active');
});

// Initialize any status chips / toggle switches already present on first load
StatusChip.init(document);
ToggleSwitch.init(document);
</script>

@stack('scripts')
</body>
</html>