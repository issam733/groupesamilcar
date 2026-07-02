<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche élève — {{ $eleve->prenom }} {{ $eleve->nom }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e2d42; background: #fff; padding: 24px; }

        .header {
            display: flex; align-items: center; gap: 16px;
            padding-bottom: 16px; border-bottom: 3px solid #1a4fa0; margin-bottom: 20px;
        }
        .header img { width: 70px; height: 70px; object-fit: contain; }
        .header-text h1 { font-size: 18px; color: #1a4fa0; font-weight: 700; }
        .header-text p  { font-size: 11px; color: #6b7f99; }

        .doc-title {
            text-align: center; font-size: 16px; font-weight: 700;
            color: #1a4fa0; margin-bottom: 20px; text-transform: uppercase;
            letter-spacing: 1.5px; border-bottom: 1px solid #d8e4f0; padding-bottom: 10px;
        }

        .fiche-grid { display: flex; gap: 24px; margin-bottom: 20px; }

        /* Photo */
        .fiche-photo {
            width: 120px; height: 140px;
            border: 2px solid #1a4fa0; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; font-weight: 800; color: #fff;
            background: linear-gradient(135deg, #1a4fa0, #4a9de0);
            flex-shrink: 0; overflow: hidden;
        }
        .fiche-photo img { width: 100%; height: 100%; object-fit: cover; }

        /* Infos table */
        .info-table { flex: 1; border-collapse: collapse; }
        .info-table td { padding: 7px 10px; border-bottom: 1px solid #f0f4fa; font-size: 12px; }
        .info-table td:first-child {
            font-weight: 700; color: #1a4fa0; width: 130px;
            background: #f7f9fd; border-right: 2px solid #1a4fa0;
        }

        /* Matricule */
        .matricule-box {
            border: 2px solid #1a4fa0; border-radius: 8px;
            padding: 10px 16px; display: flex; align-items: center; gap: 12px;
            margin-bottom: 20px; background: #f0f4ff;
        }
        .matricule-box .label { font-size: 11px; color: #6b7f99; font-weight: 700; }
        .matricule-box .value { font-size: 20px; font-weight: 800; color: #1a4fa0; letter-spacing: 2px; }

        /* Signature zone */
        .sign-zone {
            display: flex; justify-content: space-between;
            margin-top: 40px; padding-top: 16px;
            border-top: 1px solid #d8e4f0;
        }
        .sign-box { text-align: center; width: 180px; }
        .sign-box .sign-line {
            border-top: 1px solid #1e2d42; margin: 28px auto 6px; width: 130px;
        }
        .sign-box p { font-size: 11px; color: #6b7f99; }

        .footer {
            margin-top: 24px; padding-top: 10px;
            border-top: 1px dashed #d8e4f0;
            font-size: 10px; color: #9ca3af; text-align: center;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 16px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom:16px;">
        <button onclick="window.print()"
                style="background:#1a4fa0; color:#fff; border:none; padding:8px 18px;
                       border-radius:6px; font-weight:700; cursor:pointer; font-size:13px; margin-right:10px;">
            🖨️ Imprimer
        </button>
        <a href="{{ route('admin.eleves.show', $eleve) }}"
           style="color:#1a4fa0; font-size:13px; text-decoration:none;">
            ← Retour à la fiche
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Amilcar">
        <div class="header-text">
            <h1>Groupe Scolaire Amilcar</h1>
            <p>Établissement d'enseignement privé — La Marsa, Tunis</p>
        </div>
    </div>

    <div class="doc-title">Fiche Élève</div>

    <!-- Matricule -->
    <div class="matricule-box">
        <div>
            <div class="label">Matricule</div>
            <div class="value">{{ $eleve->matricule }}</div>
        </div>
        <div style="margin-left:auto; text-align:right; font-size:11px; color:#6b7f99;">
            Année scolaire : <strong>{{ $eleve->annee_scolaire }}</strong><br>
            Inscrit le : {{ $eleve->created_at->format('d/m/Y') }}
        </div>
    </div>

    <!-- Fiche principale -->
    <div class="fiche-grid">
        <!-- Photo -->
        <div class="fiche-photo">
            @if($eleve->photo)
                <img src="{{ asset('storage/'.$eleve->photo) }}" alt="">
            @else
                {{ strtoupper(substr($eleve->prenom,0,1).substr($eleve->nom,0,1)) }}
            @endif
        </div>

        <!-- Infos -->
        <table class="info-table">
            <tr><td>Nom</td><td><strong>{{ $eleve->nom }}</strong></td></tr>
            <tr><td>Prénom</td><td><strong>{{ $eleve->prenom }}</strong></td></tr>
            <tr><td>Date de naissance</td><td>{{ $eleve->date_naissance?->format('d/m/Y') ?? '—' }}</td></tr>
            <tr><td>Sexe</td><td>{{ $eleve->sexe == 'M' ? 'Masculin' : ($eleve->sexe == 'F' ? 'Féminin' : '—') }}</td></tr>
            <tr><td>Classe</td><td>{{ $eleve->classe?->nom ?? '—' }}</td></tr>
            <tr><td>Niveau</td><td>{{ $eleve->classe?->niveau ?? '—' }}</td></tr>
            <tr><td>Parent responsable</td>
                <td>{{ $eleve->parent ? $eleve->parent->prenom.' '.$eleve->parent->nom : '—' }}</td></tr>
            <tr><td>Téléphone parent</td><td>{{ $eleve->parent?->telephone ?? '—' }}</td></tr>
            <tr><td>Adresse</td><td>{{ $eleve->adresse ?? '—' }}</td></tr>
            <tr><td>Email élève</td><td>{{ $eleve->email ?? '—' }}</td></tr>
        </table>
    </div>

    <!-- Signatures -->
    <div class="sign-zone">
        <div class="sign-box">
            <div class="sign-line"></div>
            <p>Signature du parent</p>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <p>Cachet de l'établissement</p>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <p>Le Directeur</p>
        </div>
    </div>

    <div class="footer">
        Groupe Scolaire Amilcar · La Marsa, Tunis · Document généré le {{ now()->format('d/m/Y à H:i') }}
    </div>

</body>
</html>
