<!DOCTYPE html>
<html lang="{{ $examen->langue === 'ar' ? 'ar' : 'fr' }}" dir="{{ $examen->langue === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $contenu['titre'] ?? $examen->titre }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: {{ $examen->langue === 'ar' ? "'Noto Naskh Arabic', 'Tahoma'" : "'Helvetica Neue', Arial" }}, sans-serif;
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

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            border-bottom: 3px solid #1a4fa0;
            margin-bottom: 18px;
        }
        .header-left { display:flex; align-items:center; gap:14px; }
        .header-left img { width:55px; height:55px; object-fit:contain; }
        .header-school { font-size:15px; font-weight:800; color:#1a4fa0; }
        .header-sub { font-size:9px; color:#6b7f99; margin-top:2px; }
        .header-right { text-align: {{ $examen->langue === 'ar' ? 'left' : 'right' }}; }
        .exam-badge { font-size:10px; font-weight:700; padding:3px 10px; border-radius:20px; background:#fef2f2; color:#dc2626; text-transform:uppercase; }
        .exam-badge.corrige { background:#ecfdf5; color:#0d9488; }

        .exam-title-box {
            text-align: center;
            margin-bottom: 18px;
        }
        .exam-title { font-size:19px; font-weight:800; color:#1e2d42; margin-bottom:6px; }
        .exam-meta-row { display:flex; justify-content:center; gap:18px; font-size:11px; color:#6b7f99; flex-wrap:wrap; }
        .exam-meta-row span { display:flex; align-items:center; gap:5px; }

        .info-fields {
            display: flex;
            justify-content: space-between;
            border: 1px solid #d8e4f0;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 22px;
            font-size: 12px;
        }
        .info-field-line { border-bottom: 1px solid #1e2d42; display:inline-block; min-width:140px; margin-left:6px; }

        .question-block { margin-bottom: 18px; page-break-inside: avoid; }
        .question-header { display:flex; align-items:flex-start; gap:10px; margin-bottom:8px; }
        .question-number {
            width: 24px; height: 24px; border-radius: 6px;
            background: #1a4fa0; color: #fff; font-size: 11px; font-weight: 700;
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
        }
        .question-text { font-size: 12.5px; font-weight: 600; color: #1e2d42; flex: 1; line-height:1.5; }
        .question-pts { font-size: 10px; color: #6b7f99; font-weight: 600; white-space: nowrap; }

        .choix-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-{{ $examen->langue === 'ar' ? 'right' : 'left' }}:34px; margin-top:8px; }
        .choix-cell { display:flex; align-items:center; gap:8px; font-size:11.5px; padding:5px 0; }
        .choix-cell.correct-cell { color:#0d9488; font-weight:700; }
        .choix-circle { width:18px; height:18px; border-radius:50%; border:1.5px solid #d8e4f0; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; color:#6b7f99; flex-shrink:0; }
        .choix-cell.correct-cell .choix-circle { background:#0d9488; border-color:#0d9488; color:#fff; }

        .lignes-reponse { margin-{{ $examen->langue === 'ar' ? 'right' : 'left' }}:34px; margin-top:10px; }
        .ligne-pointillee { border-bottom: 1px dotted #c5d2e3; height: 22px; margin-bottom:4px; }

        .reponse-corrigee {
            margin-{{ $examen->langue === 'ar' ? 'right' : 'left' }}:34px; margin-top:10px;
            background: #ecfdf5; border-radius: 8px; padding: 10px 14px; font-size: 11.5px; color: #065f46;
        }
        .explication-corrigee {
            margin-{{ $examen->langue === 'ar' ? 'right' : 'left' }}:34px; margin-top:6px;
            background: #fffbeb; border-radius: 8px; padding: 8px 14px; font-size: 11px; color: #92400e;
        }

        .section-title {
            font-size: 13px; font-weight: 800; color: #1a4fa0;
            margin: 24px 0 14px; padding-bottom: 6px; border-bottom: 2px solid #eef3ff;
        }

        .footer-mention {
            position: absolute; bottom: 8mm; left: 16mm; right: 16mm;
            text-align: center; font-size: 8.5px; color: #9ca8ba;
            border-top: 1px solid #e8eef7; padding-top: 8px;
        }

        @media print {
            .no-print { display:none !important; }
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
        <span>Aperçu — {{ $avecCorrige ? 'Version avec corrigé' : 'Sujet d\'examen' }}</span>
        <button onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="page">

        <div class="header">
            <div class="header-left">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
                <div>
                    <div class="header-school">Groupe Scolaire Amilcar</div>
                    <div class="header-sub">Établissement d'enseignement privé — La Marsa</div>
                </div>
            </div>
            <div class="header-right">
                <span class="exam-badge {{ $avecCorrige ? 'corrige' : '' }}">
                    {{ $avecCorrige ? '✓ Corrigé' : 'Sujet d\'examen' }}
                </span>
            </div>
        </div>

        <div class="exam-title-box">
            <div class="exam-title">{{ $contenu['titre'] ?? $examen->titre }}</div>
            <div class="exam-meta-row">
                <span>📘 {{ $contenu['matiere'] ?? $examen->matiere->nom ?? '' }}</span>
                <span>🎓 {{ $contenu['niveau'] ?? $examen->niveau }}</span>
                <span>⏱️ {{ $contenu['duree_minutes'] ?? 60 }} min</span>
                <span>📊 /{{ $contenu['bareme_total'] ?? 20 }}</span>
            </div>
        </div>

        @if(!$avecCorrige)
        <div class="info-fields">
            <div>Nom : <span class="info-field-line">&nbsp;</span></div>
            <div>Prénom : <span class="info-field-line">&nbsp;</span></div>
            <div>Classe : <span class="info-field-line">&nbsp;</span></div>
            <div>Date : <span class="info-field-line">&nbsp;</span></div>
        </div>
        @endif

        @if(!empty($contenu['texte_support']))
        <div class="section-title">Texte à étudier</div>
        <div style="border:1px solid #ddd; border-radius:6px; padding:12px 14px; margin-bottom:16px; font-size:11.5px; line-height:1.6; white-space:pre-wrap;">
            {{ $contenu['texte_support'] }}
        </div>
        @endif

        @if(!empty($contenu['qcm']))
        <div class="section-title">PARTIE 1 — Questions à choix multiples</div>

        @foreach($contenu['qcm'] as $q)
        <div class="question-block">
            <div class="question-header">
                <div class="question-number">{{ $q['numero'] ?? $loop->iteration }}</div>
                <div class="question-text">{{ $q['question'] ?? '' }}</div>
                <div class="question-pts">{{ $q['points'] ?? 1 }} pt</div>
            </div>
            <div class="choix-grid">
                @foreach($q['choix'] ?? [] as $idx => $choix)
                <div class="choix-cell {{ $avecCorrige && $idx == ($q['bonne_reponse'] ?? -1) ? 'correct-cell' : '' }}">
                    <div class="choix-circle">{{ chr(65+$idx) }}</div> {{ $choix }}
                </div>
                @endforeach
            </div>
            @if($avecCorrige && !empty($q['explication']))
            <div class="explication-corrigee">💡 {{ $q['explication'] }}</div>
            @endif
        </div>
        @endforeach
        @endif

        @if(!empty($contenu['questions_ouvertes']))
        <div class="section-title">PARTIE 2 — Questions ouvertes</div>

        @foreach($contenu['questions_ouvertes'] as $q)
        <div class="question-block">
            <div class="question-header">
                <div class="question-number">{{ $q['numero'] ?? $loop->iteration }}</div>
                <div class="question-text">{{ $q['question'] ?? '' }}</div>
                <div class="question-pts">{{ $q['points'] ?? 2 }} pts</div>
            </div>

            @if($avecCorrige)
                <div class="reponse-corrigee">✓ {{ $q['reponse_attendue'] ?? '' }}</div>
            @else
                <div class="lignes-reponse">
                    <div class="ligne-pointillee"></div>
                    <div class="ligne-pointillee"></div>
                    <div class="ligne-pointillee"></div>
                </div>
            @endif
        </div>
        @endforeach
        @endif

        <div class="footer-mention">
            {{ $avecCorrige ? 'Document corrigé — usage enseignant uniquement' : 'Bonne chance !' }}
            — Généré le {{ now()->format('d/m/Y') }} — Groupe Scolaire Amilcar
        </div>

    </div>

</body>
</html>
