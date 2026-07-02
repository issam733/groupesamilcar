<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', 'Groupe Scolaire Amilcar')</title>
    <style>
        body { margin:0; padding:0; background:#f0f4fa; font-family:'Helvetica Neue',Arial,sans-serif; }
        .email-wrapper { max-width:560px; margin:0 auto; padding:24px 16px; }
        .email-card { background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(26,79,160,0.08); }
        .email-header { background:linear-gradient(135deg,#0f3170,#1a4fa0,#2e6fd8); padding:32px 28px 24px; text-align:center; }
        .email-header img { width:56px; height:56px; object-fit:contain; margin-bottom:10px; }
        .email-school { color:#ffffff; font-size:16px; font-weight:700; letter-spacing:.3px; }
        .email-sub { color:rgba(255,255,255,0.7); font-size:11px; margin-top:3px; }
        .email-body { padding:32px 28px; }
        .email-greeting { font-size:15px; color:#1e2d42; margin-bottom:16px; }
        .email-footer { padding:20px 28px; text-align:center; border-top:1px solid #e8eef7; background:#fafbff; }
        .email-footer p { font-size:11px; color:#9ca8ba; margin:3px 0; }
        .btn-cta { display:inline-block; padding:12px 28px; background:linear-gradient(135deg,#1a4fa0,#2e6fd8); color:#ffffff !important; text-decoration:none; border-radius:9px; font-size:13px; font-weight:600; margin-top:8px; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-card">
            <div class="email-header">
                <img src="{{ asset('images/logo.png') }}" alt="Amilcar">
                <div class="email-school">Groupe Scolaire Amilcar</div>
                <div class="email-sub">Établissement d'enseignement privé — La Marsa</div>
            </div>
            <div class="email-body">
                @yield('content')
            </div>
            <div class="email-footer">
                <p>© {{ date('Y') }} Groupe Scolaire Amilcar — Tous droits réservés</p>
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
            </div>
        </div>
    </div>
</body>
</html>
