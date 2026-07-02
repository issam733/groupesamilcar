<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de document — Groupe Scolaire Amilcar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Inter',sans-serif;
            background: linear-gradient(135deg, #0f3170 0%, #1a4fa0 50%, #2e6fd8 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .card {
            background: #fff; border-radius: 20px; max-width: 480px; width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,.25); overflow: hidden;
        }
        .card-header { padding: 32px 28px 24px; text-align: center; }
        .card-header img { width: 64px; height: 64px; object-fit: contain; margin-bottom: 12px; }
        .school-name { font-size: 15px; font-weight: 700; color: #1a4fa0; }
        .school-sub { font-size: 11px; color: #6b7f99; margin-top: 2px; }

        .status-icon {
            width: 80px; height: 80px; border-radius: 50%;
            display:flex; align-items:center; justify-content:center;
            margin: 16px auto; font-size: 36px;
        }
        .status-icon.valid   { background:#ecfdf5; color:#0d9488; }
        .status-icon.invalid { background:#fef2f2; color:#dc2626; }

        .status-title { text-align:center; font-size:18px; font-weight:800; margin-bottom:6px; }
        .status-title.valid   { color:#0d9488; }
        .status-title.invalid { color:#dc2626; }
        .status-sub { text-align:center; font-size:13px; color:#6b7f99; margin-bottom:24px; padding:0 20px; }

        .details-box { background:#f7f9fd; border-radius:14px; padding:20px 22px; margin:0 28px 28px; }
        .detail-row { display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid #e8eef7; font-size:13px; }
        .detail-row:last-child { border-bottom:none; }
        .detail-label { color:#6b7f99; }
        .detail-value { font-weight:700; color:#1e2d42; }

        .code-box { background:#1a4fa0; color:#fff; text-align:center; padding:16px; margin: 0 28px 28px; border-radius:12px; font-family:monospace; font-size:14px; font-weight:700; letter-spacing:1px; }

        .footer-note { text-align:center; padding:0 28px 28px; font-size:11px; color:#9ca8ba; }
    </style>
</head>
<body>

    <div class="card">
        <div class="card-header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Amilcar">
            <div class="school-name">Groupe Scolaire Amilcar</div>
            <div class="school-sub">Plateforme de vérification de documents officiels</div>
        </div>

        @if($attestation)
            <div class="status-icon valid">✓</div>
            <div class="status-title valid">Document authentique</div>
            <div class="status-sub">Cette attestation a été émise par le Groupe Scolaire Amilcar et son contenu n'a pas été altéré.</div>

            <div class="details-box">
                <div class="detail-row">
                    <span class="detail-label">Type de document</span>
                    <span class="detail-value">{{ ucfirst($attestation->type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Élève</span>
                    <span class="detail-value">{{ $attestation->eleve->prenom }} {{ $attestation->eleve->nom }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Classe</span>
                    <span class="detail-value">{{ $attestation->eleve->classe->nom ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Année scolaire</span>
                    <span class="detail-value">{{ $attestation->annee_scolaire }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date d'émission</span>
                    <span class="detail-value">{{ $attestation->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <div class="code-box">{{ $attestation->numero_unique }}</div>

        @else
            <div class="status-icon invalid">✕</div>
            <div class="status-title invalid">Document introuvable</div>
            <div class="status-sub">Aucune attestation ne correspond à ce code. Le document pourrait être falsifié, ou le lien est incorrect.</div>

            <div class="code-box" style="background:#dc2626;">{{ $code }}</div>
        @endif

        <div class="footer-note">
            © {{ date('Y') }} Groupe Scolaire Amilcar — La Marsa, Tunisie<br>
            Pour toute question, contactez l'administration de l'établissement.
        </div>
    </div>

</body>
</html>
