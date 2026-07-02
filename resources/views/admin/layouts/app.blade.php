<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') — Amilcar</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:        #1a4fa0;
            --primary-dark:   #0f3170;
            --primary-light:  #2e6fd8;
            --accent:         #4a9de0;
            --accent-light:   #7bbfee;
            --sidebar-bg:     #0d2654;
            --sidebar-hover:  rgba(255,255,255,0.07);
            --sidebar-active: rgba(74,157,224,0.18);
            --sidebar-border: rgba(255,255,255,0.06);
            --sidebar-width:  260px;
            --topbar-h:       64px;
            --bg:             #f0f4fa;
            --card:           #ffffff;
            --text:           #1e2d42;
            --text-muted:     #6b7f99;
            --border:         #d8e4f0;
            --success:        #1aaa6e;
            --warning:        #e8a020;
            --danger:         #d63031;
            --shadow-sm:      0 2px 12px rgba(26,79,160,0.08);
            --shadow:         0 8px 30px rgba(26,79,160,0.12);
            --radius:         14px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        /* ─── SIDEBAR ─── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow: hidden;
        }

        /* Subtle gradient overlay */
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(26,79,160,0.3) 0%, transparent 40%);
            pointer-events: none;
        }

        /* Logo area */
        .sidebar-logo {
            padding: 22px 20px 18px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }

        .sidebar-logo img {
            width: 46px;
            height: 46px;
            object-fit: contain;
            flex-shrink: 0;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3));
        }

        .sidebar-logo-text { line-height: 1.3; }

        .sidebar-logo-text strong {
            display: block;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .sidebar-logo-text span {
            font-size: 10px;
            color: rgba(255,255,255,0.5);
            font-weight: 400;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* Nav sections */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }

        .nav-section-label {
            padding: 14px 20px 6px;
            font-size: 9.5px;
            font-weight: 600;
            color: rgba(255,255,255,0.3);
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .nav-item {
            margin: 2px 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: rgba(255,255,255,0.9);
            transform: translateX(2px);
        }

        .nav-link.active {
            background: var(--sidebar-active);
            color: var(--accent-light);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 22px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--accent);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            line-height: 1.5;
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--sidebar-border);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            transition: background 0.2s;
        }

        .sidebar-user:hover { background: rgba(255,255,255,0.09); }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
        }

        .sidebar-user i { color: rgba(255,255,255,0.4); font-size: 13px; }

        /* ─── MAIN CONTENT ─── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── TOPBAR ─── */
        .topbar {
            height: var(--topbar-h);
            background: var(--card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: var(--shadow-sm);
        }

        .topbar-hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text);
            cursor: pointer;
            padding: 6px;
        }

        .topbar-breadcrumb {
            flex: 1;
        }

        .topbar-breadcrumb h1 {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
        }

        .topbar-breadcrumb span {
            font-size: 12px;
            color: var(--text-muted);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--bg);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 15px;
            transition: all 0.2s;
            position: relative;
            text-decoration: none;
        }

        .topbar-btn:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .topbar-btn .badge-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: #e74c3c;
            border-radius: 50%;
            border: 2px solid var(--card);
        }

        .topbar-date {
            font-size: 12px;
            color: var(--text-muted);
            padding: 0 10px;
            border-left: 1px solid var(--border);
        }

        /* ─── PAGE CONTENT ─── */
        .page-content {
            flex: 1;
            padding: 28px;
        }

        /* ─── CARDS ─── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .card-header-custom {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header-custom h5 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 8px 0 30px rgba(0,0,0,0.3);
            }
            .main-wrapper { margin-left: 0; }
            .topbar-hamburger { display: flex; }
        }

        /* ─── UTILITIES ─── */
        .text-primary-amilcar { color: var(--primary); }
        .bg-primary-amilcar   { background: var(--primary); }
        .btn-primary-amilcar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary-amilcar:hover {
            box-shadow: 0 6px 20px rgba(26,79,160,0.35);
            transform: translateY(-1px);
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .sidebar-overlay.show { display: block; }

        @yield('extra-css')
    </style>

    @yield('head')
</head>
<body>

    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Amilcar">
            <div class="sidebar-logo-text">
                <strong>Groupe Scolaire</strong>
                <span>Amilcar — Admin</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-section-label">Principal</div>

            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    Tableau de bord
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('messagerie.index') }}" class="nav-link {{ request()->routeIs('messagerie.*') ? 'active' : '' }}" style="position:relative;">
                    <i class="fa-solid fa-comments"></i>
                    Messagerie
                    @php $nbMsg = \App\Models\Message::nonLusPour(auth()->id()); @endphp
                    @if($nbMsg > 0)<span style="margin-left:auto; background:#ef4444; color:#fff; font-size:11px; font-weight:700; min-width:20px; height:20px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; padding:0 6px;">{{ $nbMsg }}</span>@endif
                </a>
            </div>

            <div class="nav-section-label">Gestion</div>

            <div class="nav-item">
                <a href="{{ route('admin.eleves.index') }}" class="nav-link {{ request()->routeIs('admin.eleves.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    Élèves
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.enseignants.index') }}" class="nav-link {{ request()->routeIs('admin.enseignants.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    Enseignants
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.parents.index') }}" class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-people-roof"></i>
                    Parents
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-door-open"></i>
                    Classes
                </a>
            </div>

            <div class="nav-section-label">Pédagogie</div>

            <div class="nav-item">
                <a href="{{ route('admin.emplois.index') }}" class="nav-link {{ request()->routeIs('admin.emplois.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i>
                    Emplois du temps
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.notes.index') }}" class="nav-link {{ request()->routeIs('admin.notes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-star-half-stroke"></i>
                    Notes
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('absences.index') }}" class="nav-link {{ request()->routeIs('absences.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-xmark"></i>
                    Absences
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('cahier.index') }}" class="nav-link {{ request()->routeIs('cahier.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i>
                    Cahier de texte
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.examens.index') }}" class="nav-link {{ request()->routeIs('admin.examens.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Examens IA
                    <span class="nav-badge">IA</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.bibliotheque.index') }}" class="nav-link {{ request()->routeIs('admin.bibliotheque.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i>
                    Bibliothèque
                </a>
            </div>

            <div class="nav-section-label">Documents</div>

            <div class="nav-item">
                <a href="{{ route('admin.attestations.index') }}" class="nav-link {{ request()->routeIs('admin.attestations.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-certificate"></i>
                    Attestations
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.bulletins.index') }}" class="nav-link {{ request()->routeIs('admin.bulletins.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-lines"></i>
                    Bulletins
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.annonces.index') }}" class="nav-link {{ request()->routeIs('admin.annonces.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i>
                    Annonces
                </a>
            </div>

            <div class="nav-section-label">Système</div>

            <div class="nav-item">
                <a href="{{ route('admin.parametres') }}" class="nav-link {{ request()->routeIs('admin.parametres') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i>
                    Paramètres
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.journal') }}" class="nav-link {{ request()->routeIs('admin.journal') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Journal des actions
                </a>
            </div>

        </nav>

        <!-- User footer -->
        <div class="sidebar-footer">
            <div class="sidebar-user" onclick="window.location='{{ route('admin.parametres') }}'">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->prenom ?? 'A', 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom ?? 'D', 0, 1)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->prenom ?? 'Administrateur' }} {{ auth()->user()->nom ?? '' }}</div>
                    <div class="user-role">Directeur</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">@csrf</form>
                <i class="fa-solid fa-arrow-right-from-bracket" title="Déconnexion"
                   onclick="event.stopPropagation(); document.getElementById('logoutForm').submit();"
                   style="cursor:pointer; padding:4px;"></i>
            </div>
        </div>

    </aside>

    <!-- ═══ MAIN ═══ -->
    <div class="main-wrapper">

        <!-- Topbar -->
        <header class="topbar">
            <button class="topbar-hamburger" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="topbar-breadcrumb">
                <h1>@yield('page-title', 'Tableau de bord')</h1>
                <span>@yield('page-subtitle', 'Groupe Scolaire Amilcar')</span>
            </div>

            <div class="topbar-actions">
                <div class="topbar-date" id="topbarDate"></div>

                <a href="{{ route('admin.annonces.index') }}" class="topbar-btn" title="Annonces">
                    <i class="fa-regular fa-bell"></i>
                    <span class="badge-dot"></span>
                </a>

                <a href="{{ route('admin.parametres') }}" class="topbar-btn" title="Paramètres">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>
        </header>

        <!-- Page content -->
        <main class="page-content">
            @yield('content')
        </main>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            const s = document.getElementById('sidebar');
            const o = document.getElementById('sidebarOverlay');
            s.classList.toggle('open');
            o.classList.toggle('show');
        }

        // Live date in topbar
        function updateDate() {
            const now = new Date();
            const opts = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
            document.getElementById('topbarDate').textContent =
                now.toLocaleDateString('fr-FR', opts);
        }
        updateDate();
        setInterval(updateDate, 60000);
    </script>

    @yield('scripts')
</body>
</html>
