<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des élèves — Groupe Scolaire Amilcar</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1e2d42; background: #fff; }

        /* Header */
        .header {
            display: flex; align-items: center; gap: 16px;
            padding: 18px 24px 14px;
            border-bottom: 3px solid #1a4fa0;
            margin-bottom: 16px;
        }
        .header img { width: 60px; height: 60px; object-fit: contain; }
        .header-text h1 { font-size: 17px; color: #1a4fa0; font-weight: 700; }
        .header-text p  { font-size: 11px; color: #6b7f99; margin-top: 2px; }
        .header-meta {
            margin-left: auto; text-align: right; font-size: 10px; color: #6b7f99;
        }

        /* Title */
        .doc-title {
            text-align: center; font-size: 14px; font-weight: 700;
            color: #1a4fa0; margin-bottom: 12px;
            text-transform: uppercase; letter-spacing: 1px;
        }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin: 0 12px; width: calc(100% - 24px); }
        thead tr { background: #1a4fa0; color: #fff; }
        thead th { padding: 8px 10px; font-size: 10px; text-align: left; font-weight: 700; }
        tbody tr:nth-child(even) { background: #f7f9fd; }
        tbody tr:hover { background: #eef3ff; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e8eef8; font-size: 10.5px; }

        /* Footer */
        .footer {
            margin-top: 20px; padding: 12px 24px;
            border-top: 1px solid #d8e4f0;
            display: flex; justify-content: space-between;
            font-size: 10px; color: #6b7f99;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- Print button -->
    <div class="no-print" style="padding:12px 24px; background:#1a4fa0; display:flex; align-items:center; gap:12px;">
        <button onclick="window.print()"
                style="background:#fff; color:#1a4fa0; border:none; padding:8px 18px;
                       border-radius:6px; font-weight:700; cursor:pointer; font-size:13px;">
            🖨️ Imprimer / Télécharger PDF
        </button>
        <a href="{{ route('admin.eleves.index') }}"
           style="color:rgba(255,255,255,.8); font-size:12px; text-decoration:none;">
            ← Retour à la liste
        </a>
        <span style="color:rgba(255,255,255,.7); font-size:12px; margin-left:auto;">
            {{ $eleves->count() }} élèves — Généré le {{ now()->format('d/m/Y à H:i') }}
        </span>
    </div>

    <!-- Header -->
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Amilcar">
        <div class="header-text">
            <h1>Groupe Scolaire Amilcar</h1>
            <p>Établissement d'enseignement privé — La Marsa, Tunis</p>
        </div>
        <div class="header-meta">
            <div>Année scolaire : <strong>{{ date('Y').'-'.(date('Y')+1) }}</strong></div>
            <div>Imprimé le : {{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="doc-title">Liste des élèves inscrits</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Matricule</th>
                <th>Nom & Prénom</th>
                <th>Date naissance</th>
                <th>Sexe</th>
                <th>Classe</th>
                <th>Niveau</th>
                <th>Téléphone</th>
            </tr>
        </thead>
        <tbody>
            @foreach($eleves as $i => $eleve)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $eleve->matricule }}</strong></td>
                <td>{{ $eleve->nom }} {{ $eleve->prenom }}</td>
                <td>{{ $eleve->date_naissance?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $eleve->sexe == 'M' ? 'Masc.' : ($eleve->sexe == 'F' ? 'Fém.' : '—') }}</td>
                <td>{{ $eleve->classe?->nom ?? '—' }}</td>
                <td>{{ $eleve->classe?->niveau ?? '—' }}</td>
                <td>{{ $eleve->telephone ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <span>Groupe Scolaire Amilcar — Confidentiel</span>
        <span>Total : {{ $eleves->count() }} élèves</span>
        <span>{{ now()->format('d/m/Y H:i') }}</span>
    </div>

</body>
</html>
