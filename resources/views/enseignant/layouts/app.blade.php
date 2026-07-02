<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Enseignant') — Amilcar</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #6d28d9; --primary-dark: #4c1d95; --primary-light: #8b5cf6;
            --accent: #7c5cbf;
            --sidebar-bg: #2e1065;
            --sidebar-hover: rgba(255,255,255,0.07); --sidebar-active: rgba(139,92,246,0.22);
            --sidebar-width: 250px; --topbar-h: 64px;
            --bg: #f6f4fc; --card: #ffffff; --text: #1e2238; --text-muted: #6b7099;
            --border: #e6e0f5; --success: #1aaa6e; --warning: #e8a020; --danger: #d63031;
            --shadow-sm: 0 2px 12px rgba(76,29,149,0.06); --shadow: 0 8px 30px rgba(76,29,149,0.12);
            --radius: 14px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); }

        .sidebar { position:fixed; top:0; left:0; width:var(--sidebar-width); height:100vh; background:var(--sidebar-bg); display:flex; flex-direction:column; z-index:1000; transition:transform .3s ease; }
        .sidebar-logo { padding:22px 20px 18px; border-bottom:1px solid rgba(255,255,255,.08); display:flex; align-items:center; gap:12px; }
        .sidebar-logo img { width:42px; height:42px; object-fit:contain; }
        .sidebar-logo-text strong { display:block; color:#fff; font-size:13px; font-weight:700; }
        .sidebar-logo-text span { font-size:10px; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.5px; }

        .sidebar-nav { flex:1; overflow-y:auto; padding:16px 0; }
        .nav-section { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:rgba(255,255,255,.35); padding:14px 24px 6px; font-weight:700; }
        .nav-item { margin:2px 10px; }
        .nav-link { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; color:rgba(255,255,255,.65); text-decoration:none; font-size:13.5px; font-weight:500; transition:all .2s; }
        .nav-link:hover { background:var(--sidebar-hover); color:rgba(255,255,255,.9); }
        .nav-link.active { background:var(--sidebar-active); color:#c4b5fd; }
        .nav-link i { width:20px; text-align:center; font-size:15px; }

        .sidebar-footer { padding:16px 20px; border-top:1px solid rgba(255,255,255,.08); }
        .sidebar-user { display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:10px; background:rgba(255,255,255,.05); }
        .user-avatar { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,var(--primary-dark),var(--primary-light)); display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:#fff; flex-shrink:0; }
        .user-name { font-size:13px; font-weight:600; color:rgba(255,255,255,.9); }
        .user-role { font-size:11px; color:rgba(255,255,255,.4); }

        .main-wrapper { margin-left:var(--sidebar-width); min-height:100vh; display:flex; flex-direction:column; }
        .topbar { height:var(--topbar-h); background:var(--card); border-bottom:1px solid var(--border); display:flex; align-items:center; padding:0 28px; gap:16px; position:sticky; top:0; z-index:900; box-shadow:var(--shadow-sm); }
        .topbar-hamburger { display:none; background:none; border:none; font-size:20px; color:var(--text); cursor:pointer; }
        .topbar-breadcrumb h1 { font-size:17px; font-weight:700; color:var(--text); }
        .topbar-breadcrumb span { font-size:12px; color:var(--text-muted); }
        .page-content { flex:1; padding:28px; }

        /* ─── Helpers réutilisables par toutes les vues enseignant ─── */
        .card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); }
        .page-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); padding:22px 24px; margin-bottom:20px; }
        .page-card h3 { font-size:15px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:9px; }

        .btn-am { display:inline-flex; align-items:center; gap:8px; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
        .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
        .btn-am.primary:hover { box-shadow:0 6px 18px rgba(109,40,217,.35); }
        .btn-am.success { background:var(--success); color:#fff; }
        .btn-am.danger { background:var(--danger); color:#fff; }
        .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
        .btn-am.sm { padding:6px 12px; font-size:12px; }

        .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:24px; }
        .stat-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:20px; box-shadow:var(--shadow-sm); display:flex; align-items:center; gap:16px; }
        .stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff; flex-shrink:0; }
        .stat-val { font-size:24px; font-weight:800; color:var(--text); line-height:1; }
        .stat-lbl { font-size:12px; color:var(--text-muted); margin-top:5px; }

        .data-table { width:100%; border-collapse:collapse; }
        .data-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); padding:10px 14px; border-bottom:2px solid var(--border); font-weight:700; }
        .data-table td { padding:12px 14px; border-bottom:1px solid var(--border); font-size:13.5px; }
        .data-table tr:last-child td { border-bottom:none; }
        .data-table tr:hover td { background:#faf8ff; }

        .badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
        .badge.violet { background:#f3eeff; color:var(--primary); }
        .badge.green  { background:#ecfdf5; color:var(--success); }
        .badge.gray   { background:var(--bg); color:var(--text-muted); }
        .badge.amber  { background:#fffbeb; color:var(--warning); }

        .empty-state { text-align:center; padding:48px 20px; color:var(--text-muted); }
        .empty-state i { font-size:42px; opacity:.4; margin-bottom:14px; display:block; }

        .alert-flash { padding:13px 18px; border-radius:10px; font-size:13.5px; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
        .alert-flash.ok  { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .alert-flash.err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }

        @media (max-width:991px) {
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); box-shadow:8px 0 30px rgba(0,0,0,.3); }
            .main-wrapper { margin-left:0; }
            .topbar-hamburger { display:flex; }
        }
        .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:999; }
        .sidebar-overlay.show { display:block; }

        @yield('extra-css')
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Amilcar" onerror="this.style.display='none'">
            <div class="sidebar-logo-text">
                <strong>Groupe Scolaire</strong>
                <span>Amilcar — Enseignant</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('enseignant.dashboard') }}" class="nav-link {{ request()->routeIs('enseignant.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i> Accueil
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('messagerie.index') }}" class="nav-link {{ request()->routeIs('messagerie.*') ? 'active' : '' }}" style="position:relative;">
                    <i class="fa-solid fa-comments"></i> Messagerie
                    @php $nbMsg = \App\Models\Message::nonLusPour(auth()->id()); @endphp
                    @if($nbMsg > 0)<span style="margin-left:auto; background:#ef4444; color:#fff; font-size:11px; font-weight:700; min-width:20px; height:20px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; padding:0 6px;">{{ $nbMsg }}</span>@endif
                </a>
            </div>

            <div class="nav-section">Enseignement</div>
            <div class="nav-item">
                <a href="{{ route('enseignant.classes') }}" class="nav-link {{ request()->routeIs('enseignant.classes') ? 'active' : '' }}">
                    <i class="fa-solid fa-chalkboard-user"></i> Mes classes & matières
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('enseignant.notes.index') }}" class="nav-link {{ request()->routeIs('enseignant.notes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-pen-to-square"></i> Saisie des notes
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('enseignant.emploi') }}" class="nav-link {{ request()->routeIs('enseignant.emploi') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i> Mon emploi du temps
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('cahier.index') }}" class="nav-link {{ request()->routeIs('cahier.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i> Cahier de texte
                </a>
            </div>

            <div class="nav-section">Outils IA</div>
            <div class="nav-item">
                <a href="{{ route('enseignant.examens.index') }}" class="nav-link {{ request()->routeIs('enseignant.examens.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Examens IA
                </a>
            </div>

            <div class="nav-section">Vie scolaire</div>
            <div class="nav-item">
                <a href="{{ route('absences.index') }}" class="nav-link {{ request()->routeIs('absences.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-xmark"></i> Absences
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('enseignant.annonces') }}" class="nav-link {{ request()->routeIs('enseignant.annonces') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i> Annonces
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->prenom ?? 'E',0,1)) }}{{ strtoupper(substr(auth()->user()->nom ?? '',0,1)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->prenom ?? 'Enseignant' }} {{ auth()->user()->nom ?? '' }}</div>
                    <div class="user-role">Enseignant</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">@csrf</form>
                <i class="fa-solid fa-arrow-right-from-bracket" style="cursor:pointer;margin-left:auto;color:rgba(255,255,255,.4);" onclick="document.getElementById('logoutForm').submit()"></i>
            </div>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <button class="topbar-hamburger" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <div class="topbar-breadcrumb">
                <h1>@yield('page-title', 'Espace Enseignant')</h1>
                <span>@yield('page-subtitle', '')</span>
            </div>
        </header>
        <main class="page-content">
            @if(session('success'))
                <div class="alert-flash ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert-flash err"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
    @yield('scripts')
</body>
</html>
