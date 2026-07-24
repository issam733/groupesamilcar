<!DOCTYPE html>
<html lang="{{ $attestation->langue }}" dir="{{ $attestation->langue === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>Attestation — {{ $attestation->numero_unique }}</title>
    <style>
        @page { size: A4; margin: 0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: {{ $attestation->langue === 'ar' ? "'Noto Naskh Arabic','Tahoma'" : "'Georgia','Times New Roman'" }}, serif;
            color: #1e2d42;
            background: #fff;
        }
        .page {
            width: 210mm; min-height: 297mm; padding: 18mm 20mm;
            margin: 0 auto; position: relative;
            border: 6px solid #1a4fa0;
            outline: 1px solid #4a9de0;
            outline-offset: -10px;
        }

        .header { text-align:center; margin-bottom:28px; }
        .header img { width:75px; height:75px; object-fit:contain; margin-bottom:10px; }
        .school-name { font-size:18px; font-weight:800; color:#1a4fa0; letter-spacing:.5px; }
        .school-sub { font-size:10px; color:#6b7f99; margin-top:4px; font-family:'Helvetica',sans-serif; }

        .doc-title {
            text-align:center; margin:30px 0;
            font-size:24px; font-weight:800; color:#1e2d42;
            letter-spacing:2px; text-transform:uppercase;
            border-top: 2px solid #4a9de0; border-bottom: 2px solid #4a9de0;
            padding: 14px 0;
        }

        .body-text {
            font-size: 14px; line-height: 2.1; color:#1e2d42; text-align: justify;
            margin: 30px 0; padding: 0 10px;
        }
        .body-text strong { color:#1a4fa0; }
        .fill-line { border-bottom: 1.5px solid #1e2d42; padding: 0 6px; font-weight:700; display:inline-block; min-width:160px; text-align:center; }

        .info-table { width:100%; margin: 24px 0; border-collapse:collapse; }
        .info-table td { padding:8px 4px; font-size:13px; }
        .info-table td.label { color:#6b7f99; width:35%; font-family:'Helvetica',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:.5px; }
        .info-table td.value { font-weight:700; border-bottom:1px dotted #c5d2e3; }

        .footer-section {
            display:flex; justify-content:space-between; align-items:flex-end;
            margin-top: 50px; padding-top: 20px;
        }
        .signature-block { text-align:center; }
        .signature-line { width:160px; border-top:1.5px solid #1e2d42; margin:50px auto 8px; }
        .signature-label { font-size:11px; color:#6b7f99; font-family:'Helvetica',sans-serif; }

        .qr-block { text-align:center; }
        .qr-block svg { display:block; margin:0 auto 8px; }
        .qr-code-text { font-size:10px; color:#6b7f99; font-family:'Helvetica',sans-serif; }
        .qr-numero { font-size:11px; font-weight:700; color:#1a4fa0; font-family:'Courier New',monospace; margin-top:4px; }

        .stamp-placeholder {
            position: absolute; bottom: 60mm; right: 30mm;
            width: 90px; height: 90px; border: 2px dashed #c5d2e3; border-radius: 50%;
            display:flex; align-items:center; justify-content:center;
            font-size: 9px; color: #c5d2e3; text-align:center; transform: rotate(-12deg);
            font-family:'Helvetica',sans-serif;
        }

        .print-bar { background:#1a4fa0; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; font-family:'Helvetica',sans-serif; }
        .print-bar button { background:#fff; color:#1a4fa0; border:none; padding:8px 18px; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer; }

        @media print {
            .no-print { display:none !important; }
            body { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
        }
    </style>
</head>
<body>

    <div class="print-bar no-print">
        <span>Aperçu — Attestation {{ $attestation->numero_unique }}</span>
        <button onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="page">

        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <div class="school-name">
                {{ $attestation->langue === 'ar' ? 'مجمع أميلكار التربوي' : 'Groupe Scolaire Amilcar' }}
            </div>
            <div class="school-sub">
                {{ $attestation->langue === 'ar' ? 'مؤسسة تربوية خاصة - المرسى' : 'Établissement d\'enseignement privé — La Marsa, Tunisie' }}
            </div>
        </div>

        @if($attestation->langue === 'ar')
        <div class="doc-title">شهادة {{ ['inscription'=>'ترسيم','presence'=>'حضور','reussite'=>'نجاح'][$attestation->type] ?? '' }}</div>

        <div class="body-text">
            يشهد مدير مؤسسة أميلكار للتعليم الخاص أن التلميذ(ة) :
            <br><br>
            الاسم واللقب : <span class="fill-line">{{ $attestation->eleve->prenom }} {{ $attestation->eleve->nom }}</span>
            <br><br>
            تاريخ الولادة : <span class="fill-line">{{ $attestation->eleve->date_naissance?->format('d/m/Y') ?? '—' }}</span>
            <br><br>
            @if($attestation->type === 'inscription')
                مرسم(ة) بصفة قانونية بمؤسستنا بالقسم <span class="fill-line">{{ $attestation->eleve->classe->nom ?? '—' }}</span>
                للسنة الدراسية <span class="fill-line">{{ $attestation->annee_scolaire }}</span>.
            @elseif($attestation->type === 'presence')
                يتابع (تتابع) بانتظام دراسته (دراستها) بالقسم <span class="fill-line">{{ $attestation->eleve->classe->nom ?? '—' }}</span>
                للسنة الدراسية <span class="fill-line">{{ $attestation->annee_scolaire }}</span>.
            @else
                قد أنهى (أنهت) بنجاح السنة الدراسية <span class="fill-line">{{ $attestation->annee_scolaire }}</span>
                بالقسم <span class="fill-line">{{ $attestation->eleve->classe->nom ?? '—' }}</span>.
            @endif
            <br><br>
            وقد سلمت هذه الشهادة بطلب من المعني(ة) بالأمر لتقديمها لمن يهمه الأمر.
        </div>
        @else
        <div class="doc-title">Attestation de {{ ['inscription'=>'inscription','presence'=>'présence','reussite'=>'réussite'][$attestation->type] ?? '' }}</div>

        <div class="body-text">
            Je soussigné, Directeur du <strong>Groupe Scolaire Amilcar</strong>,
            établissement d'enseignement privé sis à La Marsa, certifie que l'élève :
            <br><br>
            <strong>Nom et prénom :</strong> <span class="fill-line">{{ $attestation->eleve->prenom }} {{ $attestation->eleve->nom }}</span>
            <br><br>
            <strong>Né(e) le :</strong> <span class="fill-line">{{ $attestation->eleve->date_naissance?->format('d/m/Y') ?? '—' }}</span>
            <br><br>
            @if($attestation->type === 'inscription')
                est régulièrement inscrit(e) dans notre établissement, en classe de
                <span class="fill-line">{{ $attestation->eleve->classe->nom ?? '—' }}</span>,
                pour l'année scolaire <span class="fill-line">{{ $attestation->annee_scolaire }}</span>.
            @elseif($attestation->type === 'presence')
                suit assidûment sa scolarité en classe de
                <span class="fill-line">{{ $attestation->eleve->classe->nom ?? '—' }}</span>
                pour l'année scolaire <span class="fill-line">{{ $attestation->annee_scolaire }}</span>.
            @else
                a achevé avec succès l'année scolaire <span class="fill-line">{{ $attestation->annee_scolaire }}</span>
                en classe de <span class="fill-line">{{ $attestation->eleve->classe->nom ?? '—' }}</span>.
            @endif
            <br><br>
            La présente attestation est délivrée à la demande de l'intéressé(e) pour servir et valoir ce que de droit.
        </div>
        @endif

        <table class="info-table">
            <tr>
                <td class="label">{{ $attestation->langue === 'ar' ? 'رقم التسجيل' : 'Matricule' }}</td>
                <td class="value">{{ $attestation->eleve->matricule }}</td>
                <td class="label">{{ $attestation->langue === 'ar' ? 'القسم' : 'Classe' }}</td>
                <td class="value">{{ $attestation->eleve->classe->nom ?? '—' }}</td>
            </tr>
        </table>

        <div class="footer-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">{{ $attestation->langue === 'ar' ? 'توقيع المدير' : 'Signature du Directeur' }}</div>
            </div>

            <div class="qr-block">
                {!! $qrCodeSvg !!}
                <div class="qr-code-text">{{ $attestation->langue === 'ar' ? 'تحقق رقمي' : 'Vérification numérique' }}</div>
                <div class="qr-numero">{{ $attestation->numero_unique }}</div>
            </div>
        </div>

        <div class="stamp-placeholder">{{ $attestation->langue === 'ar' ? 'ختم المؤسسة' : 'Cachet de l\'établissement' }}</div>

    </div>

</body>
</html>
