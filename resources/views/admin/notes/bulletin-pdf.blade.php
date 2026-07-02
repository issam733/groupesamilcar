<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin — {{ $eleve->prenom }} {{ $eleve->nom }} — Trimestre {{ $trim }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #1e2d42;
            background: #fff;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 14mm 16mm;
            margin: 0 auto;
            position: relative;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            border-bottom: 3px solid #1a4fa0;
            margin-bottom: 18px;
        }
        .header-left { display:flex; align-items:center; gap:14px; }
        .header-left img { width:60px; height:60px; object-fit:contain; }
        .header-school { font-size:16px; font-weight:800; color:#1a4fa0; }
        .header-sub { font-size:10px; color:#6b7f99; margin-top:2px; }
        .header-right { text-align:right; }
        .bulletin-title { font-size:20px; font-weight:800; color:#1a4fa0; }
        .bulletin-trim { font-size:12px; color:#6b7f99; margin-top:2px; }

        /* Élève info */
        .eleve-info-box {
            background: #f7f9fd;
            border: 1px solid #d8e4f0;
            border-radius: 10px;
            padding: 16px 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
        }
        .info-item-label { font-size:9px; color:#6b7f99; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; }
        .info-item-value { font-size:13px; font-weight:700; color:#1e2d42; }

        /* Table notes */
        .notes-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        .notes-table th {
            background: #1a4fa0; color:#fff;
            font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
            padding:10px 12px; text-align:left;
        }
        .notes-table th.center { text-align:center; }
        .notes-table td {
            padding:9px 12px; font-size:12px; color:#1e2d42;
            border-bottom:1px solid #e8eef7;
        }
        .notes-table td.center { text-align:center; }
        .notes-table tr:nth-child(even) td { background:#fafbff; }
        .notes-table tr.total-row td {
            background:#eef3ff; font-weight:800; border-top:2px solid #1a4fa0; border-bottom:none;
        }

        .note-pill { display:inline-block; padding:2px 10px; border-radius:20px; font-weight:700; font-size:12px; }
        .note-pill.excellent { background:#ecfdf5; color:#0d9488; }
        .note-pill.bien      { background:#eef3ff; color:#1a4fa0; }
        .note-pill.passable  { background:#fffbeb; color:#d97706; }
        .note-pill.insuff    { background:#fef2f2; color:#dc2626; }
        .note-pill.none      { background:#f1f5f9; color:#6b7f99; }

        /* Synthèse */
        .synthese-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
            margin-bottom: 24px;
        }
        .synthese-box {
            border: 1px solid #d8e4f0;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }
        .synthese-box.highlight { background:#1a4fa0; border-color:#1a4fa0; }
        .synthese-val { font-size:28px; font-weight:800; color:#1a4fa0; }
        .synthese-box.highlight .synthese-val { color:#fff; }
        .synthese-lbl { font-size:10px; color:#6b7f99; margin-top:4px; text-transform:uppercase; letter-spacing:.5px; }
        .synthese-box.highlight .synthese-lbl { color:rgba(255,255,255,.8); }

        /* Observation */
        .observation-box {
            border: 1px dashed #d8e4f0;
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 24px;
            min-height: 60px;
        }
        .observation-label { font-size:10px; color:#6b7f99; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; font-weight:700; }

        /* Footer signatures */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
        }
        .signature-box { text-align:center; }
        .signature-line { border-top:1px solid #1e2d42; margin-top:50px; padding-top:6px; font-size:10px; color:#6b7f99; }
        .signature-title { font-size:11px; font-weight:700; color:#1e2d42; }

        .footer-mention {
            position: absolute;
            bottom: 10mm;
            left: 16mm;
            right: 16mm;
            text-align: center;
            font-size: 9px;
            color: #9ca8ba;
            border-top: 1px solid #e8eef7;
            padding-top: 10px;
        }

        @media print {
            .no-print { display:none; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }

        .print-bar {
            background: #1a4fa0; color:#fff; padding:12px 20px;
            display:flex; justify-content:space-between; align-items:center;
            font-family: 'Inter', sans-serif;
        }
        .print-bar button {
            background:#fff; color:#1a4fa0; border:none; padding:8px 18px;
            border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;
        }
    </style>
</head>
<body>

    <div class="print-bar no-print">
        <span>Aperçu du bulletin — {{ $eleve->prenom }} {{ $eleve->nom }}</span>
        <button onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="page">

        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
                <div>
                    <div class="header-school">Groupe Scolaire Amilcar</div>
                    <div class="header-sub">Établissement d'enseignement privé — La Marsa, Tunisie</div>
                </div>
            </div>
            <div class="header-right">
                <div class="bulletin-title">BULLETIN DE NOTES</div>
                <div class="bulletin-trim">Trimestre {{ $trim }} — Année {{ $eleve->annee_scolaire }}</div>
            </div>
        </div>

        <!-- Info élève -->
        <div class="eleve-info-box">
            <div>
                <div class="info-item-label">Élève</div>
                <div class="info-item-value">{{ $eleve->prenom }} {{ $eleve->nom }}</div>
            </div>
            <div>
                <div class="info-item-label">Matricule</div>
                <div class="info-item-value">{{ $eleve->matricule }}</div>
            </div>
            <div>
                <div class="info-item-label">Classe</div>
                <div class="info-item-value">{{ $eleve->classe->nom ?? '—' }}</div>
            </div>
            <div>
                <div class="info-item-label">Date de naissance</div>
                <div class="info-item-value">{{ $eleve->date_naissance?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div>
                <div class="info-item-label">Classement</div>
                <div class="info-item-value">
                    {{ $classement ? $classement.'ᵉ' : '—' }} / {{ $totalEleves }}
                </div>
            </div>
            <div>
                <div class="info-item-label">Niveau</div>
                <div class="info-item-value">{{ $eleve->classe->niveau ?? '—' }}</div>
            </div>
        </div>

        <!-- Notes -->
        <table class="notes-table">
            <thead>
                <tr>
                    <th>Matière</th>
                    <th class="center">Coefficient</th>
                    <th class="center">Moyenne /20</th>
                    <th class="center">Points</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $ligne)
                @php
                    $moy = $ligne['moyenne'];
                    $pillClass = match(true) {
                        $moy === null => 'none',
                        $moy >= 16    => 'excellent',
                        $moy >= 12    => 'bien',
                        $moy >= 10    => 'passable',
                        default       => 'insuff',
                    };
                @endphp
                <tr>
                    <td>{{ $ligne['matiere'] }}</td>
                    <td class="center">{{ $ligne['coefficient'] }}</td>
                    <td class="center">
                        <span class="note-pill {{ $pillClass }}">
                            {{ $moy !== null ? number_format($moy, 2) : '—' }}
                        </span>
                    </td>
                    <td class="center">{{ $ligne['points'] !== null ? number_format($ligne['points'], 2) : '—' }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>MOYENNE GÉNÉRALE</td>
                    <td class="center">{{ $lignes->sum('coefficient') }}</td>
                    <td class="center">{{ $moyenneGenerale !== null ? number_format($moyenneGenerale, 2) : '—' }}</td>
                    <td class="center">—</td>
                </tr>
            </tbody>
        </table>

        <!-- Synthèse -->
        <div class="synthese-grid">
            <div class="synthese-box highlight">
                <div class="synthese-val">{{ $moyenneGenerale !== null ? number_format($moyenneGenerale, 2) : '—' }}</div>
                <div class="synthese-lbl">Moyenne générale /20</div>
            </div>
            <div class="synthese-box">
                <div class="synthese-val">{{ $classement ?? '—' }}<span style="font-size:14px;">/{{ $totalEleves }}</span></div>
                <div class="synthese-lbl">Rang dans la classe</div>
            </div>
            <div class="synthese-box">
                <div class="synthese-val" style="font-size:18px;">
                    {{ $moyenneGenerale !== null ? \App\Models\Eleve::mention($moyenneGenerale) : '—' }}
                </div>
                <div class="synthese-lbl">Mention</div>
            </div>
        </div>

        <!-- Observation -->
        <div class="observation-box">
            <div class="observation-label">Observation du conseil de classe</div>
            <div style="font-size:12px; color:#6b7f99;">
                @if($moyenneGenerale === null)
                    En attente de notes pour ce trimestre.
                @elseif($moyenneGenerale >= 16)
                    Excellent trimestre. Continuez sur cette voie, félicitations.
                @elseif($moyenneGenerale >= 12)
                    Bon trimestre dans l'ensemble. Encouragements.
                @elseif($moyenneGenerale >= 10)
                    Résultats moyens. Des efforts supplémentaires sont attendus.
                @else
                    Résultats insuffisants. Un travail plus soutenu est nécessaire.
                @endif
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Le Professeur Principal</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Le Directeur</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Le Parent / Tuteur</div>
            </div>
        </div>

        <div class="footer-mention">
            Document généré le {{ now()->translatedFormat('d F Y') }} — Groupe Scolaire Amilcar — Document à usage interne
        </div>

    </div>

</body>
</html>
