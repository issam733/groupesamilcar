<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Groupe Scolaire Amilcar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a4fa0;
            --primary-dark: #0f3170;
            --primary-light: #2e6fd8;
            --accent: #4a9de0;
            --accent-light: #7bbfee;
            --bg: #f0f4fa;
            --white: #ffffff;
            --text: #1e2d42;
            --text-muted: #6b7f99;
            --border: #d1ddf0;
            --shadow: 0 20px 60px rgba(26,79,160,0.15);
            --shadow-sm: 0 4px 20px rgba(26,79,160,0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-y: auto;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: -30%;
            left: -20%;
            width: 70%;
            height: 90%;
            background: radial-gradient(ellipse, rgba(26,79,160,0.08) 0%, transparent 70%);
            animation: float1 8s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -20%;
            right: -10%;
            width: 50%;
            height: 70%;
            background: radial-gradient(ellipse, rgba(74,157,224,0.07) 0%, transparent 70%);
            animation: float2 10s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -20px) scale(1.05); }
        }
        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-15px, 15px) scale(1.03); }
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            padding: 20px;
        }

        .login-card {
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .login-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
            padding: 40px 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 60% 40%, rgba(255,255,255,0.08) 0%, transparent 60%);
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 16px;
        }

        .logo-bg {
            width: 110px;
            height: 110px;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .logo-bg:hover { transform: scale(1.05); }

        .logo-bg img {
            width: 106px;
            height: 106px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.2));
        }

        .school-name {
            color: var(--white);
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .school-sub {
            color: rgba(255,255,255,0.75);
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 400;
        }

        /* Body */
        .login-body {
            padding: 36px 40px 40px;
        }

        .login-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Role selector */
        .role-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 24px;
        }

        .role-btn {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .role-btn:hover {
            border-color: var(--accent);
            background: #f0f7ff;
            transform: translateY(-1px);
        }

        .role-btn.active {
            border-color: var(--primary);
            background: #eef4ff;
            box-shadow: 0 0 0 3px rgba(26,79,160,0.1);
        }

        .role-btn i {
            display: block;
            font-size: 20px;
            margin-bottom: 5px;
            color: var(--text-muted);
        }

        .role-btn.active i { color: var(--primary); }

        .role-btn span {
            font-size: 10px;
            font-weight: 500;
            color: var(--text-muted);
            display: block;
        }

        .role-btn.active span { color: var(--primary); font-weight: 600; }

        /* Form */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 7px;
            letter-spacing: 0.3px;
            display: block;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: #fafbff;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(26,79,160,0.1);
        }

        .form-input:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: var(--primary);
        }

        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            font-size: 14px;
            transition: color 0.2s;
        }

        .toggle-pass:hover { color: var(--primary); }

        /* Options row */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-check {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-check input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember-check span {
            font-size: 12px;
            color: var(--text-muted);
        }

        .forgot-link {
            font-size: 12px;
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover { text-decoration: underline; }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
            border-radius: 10px;
            color: var(--white);
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.4s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26,79,160,0.4);
        }

        .btn-login:hover::before { left: 100%; }
        .btn-login:active { transform: translateY(0); }

        /* Alert error */
        .alert-error {
            background: #fff0f0;
            border: 1px solid #ffd0d0;
            border-left: 4px solid #e74c3c;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 13px;
            color: #c0392b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Footer */
        .login-footer {
            border-top: 1px solid var(--border);
            padding: 16px 40px;
            text-align: center;
            background: #fafbff;
        }

        .login-footer p {
            font-size: 11px;
            color: var(--text-muted);
        }

        .login-footer strong { color: var(--primary); }

        /* Decorative dots */
        .deco-dots {
            position: fixed;
            top: 15%;
            right: 5%;
            opacity: 0.15;
            pointer-events: none;
        }

        .deco-dots svg { width: 120px; }
    </style>
</head>
<body>

    <!-- Decorative element -->
    <div class="deco-dots">
        <svg viewBox="0 0 120 120" fill="none">
            <circle cx="10" cy="10" r="4" fill="#1a4fa0"/>
            <circle cx="30" cy="10" r="4" fill="#1a4fa0"/>
            <circle cx="50" cy="10" r="4" fill="#1a4fa0"/>
            <circle cx="70" cy="10" r="4" fill="#1a4fa0"/>
            <circle cx="10" cy="30" r="4" fill="#1a4fa0"/>
            <circle cx="30" cy="30" r="4" fill="#1a4fa0"/>
            <circle cx="50" cy="30" r="4" fill="#1a4fa0"/>
            <circle cx="70" cy="30" r="4" fill="#1a4fa0"/>
            <circle cx="10" cy="50" r="4" fill="#1a4fa0"/>
            <circle cx="30" cy="50" r="4" fill="#1a4fa0"/>
            <circle cx="50" cy="50" r="4" fill="#1a4fa0"/>
            <circle cx="70" cy="50" r="4" fill="#1a4fa0"/>
            <circle cx="10" cy="70" r="4" fill="#1a4fa0"/>
            <circle cx="30" cy="70" r="4" fill="#1a4fa0"/>
            <circle cx="50" cy="70" r="4" fill="#1a4fa0"/>
            <circle cx="70" cy="70" r="4" fill="#1a4fa0"/>
        </svg>
    </div>

    <div class="login-wrapper">
        <div class="login-card">

            <!-- Header -->
            <div class="login-header">
                <div class="logo-container">
                    <div class="logo-bg">
                        <img src="{{ asset('images/logo.png') }}" alt="Amilcar Logo">
                    </div>
                </div>
                <div class="school-name">Groupe Scolaire Amilcar</div>
                <div class="school-sub">Établissement d'enseignement privé</div>
            </div>

            <!-- Body -->
            <div class="login-body">

                <div class="login-title">Bon retour 👋</div>
                <div class="login-subtitle">Connectez-vous à votre espace</div>

                <!-- Role selector -->
                <div class="role-selector" id="roleSelector">
                    <div class="role-btn active" data-role="admin" onclick="selectRole(this)">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Admin</span>
                    </div>
                    <div class="role-btn" data-role="enseignant" onclick="selectRole(this)">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span>Enseignant</span>
                    </div>
                    <div class="role-btn" data-role="parent" onclick="selectRole(this)">
                        <i class="fa-solid fa-people-roof"></i>
                        <span>Parent</span>
                    </div>
                    <div class="role-btn" data-role="eleve" onclick="selectRole(this)">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Élève</span>
                    </div>
                </div>

                <!-- Error message -->
                @if($errors->any())
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ $errors->first() }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ session('error') }}
                </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <input type="hidden" name="role" id="roleInput" value="admin">

                    <div class="form-group">
                        <label class="form-label">Adresse email</label>
                        <div class="input-wrapper">
                            <input
                                type="email"
                                name="email"
                                class="form-input"
                                placeholder="exemple@amilcar.tn"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                            >
                            <i class="fa-regular fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-wrapper">
                            <input
                                type="password"
                                name="password"
                                id="passwordField"
                                class="form-input"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <i class="fa-solid fa-lock input-icon"></i>
                            <i class="fa-regular fa-eye toggle-pass" id="togglePass" onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-check">
                            <input type="checkbox" name="remember">
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right:8px;"></i>
                        Se connecter
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                <p>© 2026 <strong>Groupe Scolaire Amilcar</strong> · La Marsa, Tunis</p>
            </div>

        </div>
    </div>

    <script>
        function selectRole(el) {
            document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('roleInput').value = el.dataset.role;
        }

        function togglePassword() {
            const field = document.getElementById('passwordField');
            const icon  = document.getElementById('togglePass');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
