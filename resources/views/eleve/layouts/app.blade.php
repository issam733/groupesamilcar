<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Élève') — Amilcar</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1a4fa0; --primary-dark: #0f3170; --primary-light: #2e6fd8;
            --accent: #4a9de0; --student-accent: #0d9488; --student-accent-light: #14b8a6;
            --sidebar-bg: #052e2b;
            --sidebar-hover: rgba(255,255,255,0.07); --sidebar-active: rgba(20,184,166,0.18);
            --sidebar-width: 250px; --topbar-h: 64px;
            --bg: #f1f9f8; --card: #ffffff; --text: #1e2d42; --text-muted: #6b7f99;
            --border: #d9eeec; --success: #1aaa6e; --warning: #e8a020; --danger: #d63031;
            --shadow-sm: 0 2px 12px rgba(0,0,0,0.06); --shadow: 0 8px 30px rgba(0,0,0,0.1);
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
        .nav-item { margin:2px 10px; }
        .nav-link { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; color:rgba(255,255,255,.65); text-decoration:none; font-size:13.5px; font-weight:500; transition:all .2s; }
        .nav-link:hover { background:var(--sidebar-hover); color:rgba(255,255,255,.9); }
        .nav-link.active { background:var(--sidebar-active); color:#2dd4bf; }
        .nav-link i { width:20px; text-align:center; font-size:15px; }

        .sidebar-footer { padding:16px 20px; border-top:1px solid rgba(255,255,255,.08); }
        .sidebar-user { display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:10px; background:rgba(255,255,255,.05); cursor:pointer; }
        .user-avatar { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#0d9488,#14b8a6); display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:#fff; flex-shrink:0; }
        .user-name { font-size:13px; font-weight:600; color:rgba(255,255,255,.9); }
        .user-role { font-size:11px; color:rgba(255,255,255,.4); }

        .main-wrapper { margin-left:var(--sidebar-width); min-height:100vh; display:flex; flex-direction:column; }
        .topbar { height:var(--topbar-h); background:var(--card); border-bottom:1px solid var(--border); display:flex; align-items:center; padding:0 28px; gap:16px; position:sticky; top:0; z-index:900; box-shadow:var(--shadow-sm); }
        .topbar-hamburger { display:none; background:none; border:none; font-size:20px; color:var(--text); cursor:pointer; }
        .topbar-breadcrumb h1 { font-size:17px; font-weight:700; color:var(--text); }
        .topbar-breadcrumb span { font-size:12px; color:var(--text-muted); }
        .page-content { flex:1; padding:28px; }

        .card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); }

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
            <img src="{{ asset('images/logo.png') }}" alt="Amilcar">
            <div class="sidebar-logo-text">
                <strong>Groupe Scolaire</strong>
                <span>Amilcar — Élève</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('eleve.dashboard') }}" class="nav-link {{ request()->routeIs('eleve.dashboard') ? 'active' : '' }}">
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
            <div class="nav-item">
                <a href="{{ route('eleve.cours') }}" class="nav-link {{ request()->routeIs('eleve.cours') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i> Mes cours
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('cahier.index') }}" class="nav-link {{ request()->routeIs('cahier.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i> Cahier de texte
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('eleve.resultats') }}" class="nav-link {{ request()->routeIs('eleve.resultats') ? 'active' : '' }}">
                    <i class="fa-solid fa-star-half-stroke"></i> Mes résultats
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('eleve.bibliotheque') }}" class="nav-link {{ request()->routeIs('eleve.bibliotheque') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i> Bibliothèque
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('eleve.examens') }}" class="nav-link {{ request()->routeIs('eleve.examens') || request()->routeIs('eleve.examens.show') ? 'active' : '' }}"><i class="fa-solid fa-wand-magic-sparkles"></i> Mes examens</a>
            </div>
            <div class="nav-item">
                <a href="{{ route('eleve.profil') }}" class="nav-link {{ request()->routeIs('eleve.profil') ? 'active' : '' }}">
                    <i class="fa-solid fa-user"></i> Mon profil
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->prenom ?? 'E',0,1)) }}{{ strtoupper(substr(auth()->user()->nom ?? '',0,1)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->prenom ?? 'Élève' }} {{ auth()->user()->nom ?? '' }}</div>
                    <div class="user-role">Élève</div>
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
                <h1>@yield('page-title', 'Espace Élève')</h1>
                <span>@yield('page-subtitle', '')</span>
            </div>
        </header>
        <main class="page-content">@yield('content')</main>
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
