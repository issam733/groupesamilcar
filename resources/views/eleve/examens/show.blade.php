@extends('eleve.layouts.app')

@section('title', $examen->titre)
@section('page-title', 'Examen')
@section('page-subtitle', $examen->titre)

@section('extra-css')
<style>
    .examen-header-card { background:linear-gradient(135deg,#0d9488,#14b8a6); border-radius:16px; padding:24px 28px; color:#fff; margin-bottom:20px; }
    .examen-header-card h2 { font-size:20px; font-weight:800; margin-bottom:8px; }
    .examen-header-meta { display:flex; gap:16px; flex-wrap:wrap; font-size:12.5px; opacity:.95; }
    .examen-header-meta span { display:flex; align-items:center; gap:6px; }
    .question-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 22px; margin-bottom:14px; box-shadow:var(--shadow-sm); }
    .question-num { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background:#ccfbf1; color:#0f766e; font-size:12px; font-weight:700; margin-right:10px; }
    .question-text { font-size:14px; font-weight:600; display:inline; }
    .question-points { float:right; font-size:11px; font-weight:700; color:var(--text-muted); background:var(--bg); padding:3px 10px; border-radius:20px; }
    .choix-list { margin-top:14px; display:grid; gap:8px; }
    .choix-item { display:flex; align-items:center; gap:11px; padding:11px 14px; border-radius:9px; background:var(--bg); font-size:13px; cursor:pointer; border:1.5px solid transparent; transition:all .15s; }
    .choix-item:hover { border-color:#5eead4; }
    .choix-item input { accent-color:#0d9488; width:17px; height:17px; cursor:pointer; }
    .choix-item.correct { background:#ecfdf5; border-color:#a7f3d0; }
    .choix-item.wrong   { background:#fef2f2; border-color:#fecaca; }
    .choix-letter { width:22px; height:22px; border-radius:50%; background:#fff; border:1.5px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:var(--text-muted); flex-shrink:0; }
    .choix-item.correct .choix-letter { background:#0d9488; color:#fff; border-color:#0d9488; }
    .choix-item.wrong .choix-letter { background:#dc2626; color:#fff; border-color:#dc2626; }
    .tag-result { margin-left:auto; font-size:11px; font-weight:700; }
    .tag-result.ok  { color:#0f766e; }
    .tag-result.no  { color:#dc2626; }
    .ouverte-textarea { margin-top:12px; width:100%; min-height:90px; border:1.5px solid var(--border); border-radius:10px; padding:11px 14px; font-family:'Inter',sans-serif; font-size:13px; resize:vertical; outline:none; }
    .ouverte-textarea:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(13,148,136,.12); }
    .ouverte-reponse { margin-top:12px; padding:12px 14px; border-radius:10px; background:var(--bg); font-size:13px; line-height:1.5; white-space:pre-wrap; }
    .ouverte-attendue { margin-top:10px; padding:12px 14px; border-radius:10px; background:#f0fdfa; border:1px solid #99f6e4; font-size:12.5px; line-height:1.5; }
    .ouverte-attendue .lbl { font-size:10px; font-weight:700; color:#0f766e; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:5px; }
    .section-divider-title { font-size:13px; font-weight:700; margin:28px 0 16px; display:flex; align-items:center; gap:10px; }
    .section-divider-title::after { content:''; flex:1; height:1px; background:var(--border); }
    .btn-back { display:inline-flex; align-items:center; gap:8px; padding:9px 18px; border-radius:9px; background:var(--bg); border:1.5px solid var(--border); color:var(--text); text-decoration:none; font-size:13px; font-weight:600; margin-bottom:18px; }
    .submit-bar { position:sticky; bottom:0; background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px 22px; margin-top:20px; display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap; box-shadow:0 -4px 20px rgba(0,0,0,.06); }
    .btn-submit { display:inline-flex; align-items:center; gap:9px; padding:12px 24px; border-radius:10px; background:linear-gradient(135deg,#0d9488,#14b8a6); color:#fff; border:none; font-size:14px; font-weight:700; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-submit:hover { box-shadow:0 6px 18px rgba(13,148,136,.35); }
    .score-banner { border-radius:14px; padding:20px 24px; margin-bottom:20px; display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
    .score-banner.done { background:#ecfdf5; border:1px solid #a7f3d0; }
    .score-banner.pending { background:#fffbeb; border:1px solid #fde68a; }
    .score-circle { width:70px; height:70px; border-radius:50%; background:#0d9488; color:#fff; display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0; }
    .score-circle .v { font-size:20px; font-weight:800; line-height:1; }
    .score-circle .m { font-size:10px; opacity:.85; }
    .alert-flash { padding:13px 18px; border-radius:10px; font-size:13.5px; margin-bottom:18px; display:flex; align-items:center; gap:10px; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
</style>
@endsection

@section('content')

<a href="{{ route('eleve.examens') }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour à mes examens</a>

@if(session('success'))
    <div class="alert-flash"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div class="examen-header-card">
    <h2>{{ $contenu['titre'] ?? $examen->titre }}</h2>
    <div class="examen-header-meta">
        <span><i class="fa-solid fa-book"></i> {{ $contenu['matiere'] ?? $examen->matiere->nom ?? '—' }}</span>
        <span><i class="fa-regular fa-clock"></i> {{ $contenu['duree_minutes'] ?? 60 }} min</span>
        <span><i class="fa-solid fa-scale-balanced"></i> Noté sur {{ $contenu['bareme_total'] ?? 20 }}</span>
        <span><i class="fa-solid fa-chalkboard-user"></i> {{ $examen->enseignant?->prenom }} {{ $examen->enseignant?->nom }}</span>
    </div>
</div>

@php
    $qcm      = $contenu['qcm'] ?? [];
    $ouvertes = $contenu['questions_ouvertes'] ?? [];
    $repQcm   = $copie->reponses['qcm'] ?? [];
    $repOuv   = $copie->reponses['ouvertes'] ?? [];
    $texteSupport = $contenu['texte_support'] ?? '';
@endphp

@if(!empty($texteSupport))
<div class="question-card" style="border-left:4px solid #0d9488; white-space:pre-wrap; line-height:1.75; font-size:13.5px; margin-bottom:20px;">
    <strong style="font-size:11px; color:#0f766e; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:10px;">
        <i class="fa-solid fa-book-open"></i> Texte à étudier
    </strong>
    {{ $texteSupport }}
</div>
@endif

{{-- ════════════ COPIE DÉJÀ SOUMISE : résultats ════════════ --}}
@if($copie)

    @if($copie->statut === 'corrige')
        <div class="score-banner done">
            <div class="score-circle">
                <span class="v">{{ rtrim(rtrim(number_format($copie->note_finale, 2, ',', ''), '0'), ',') }}</span>
                <span class="m">/ {{ $contenu['bareme_total'] ?? 20 }}</span>
            </div>
            <div>
                <div style="font-size:15px; font-weight:700;"><i class="fa-solid fa-circle-check" style="color:#0d9488;"></i> Examen corrigé</div>
                <div style="font-size:12.5px; color:var(--text-muted); margin-top:3px;">
                    QCM : {{ rtrim(rtrim(number_format($copie->score_qcm,2,',',''),'0'),',') }} / {{ rtrim(rtrim(number_format($copie->bareme_qcm,2,',',''),'0'),',') }} (correction automatique)
                </div>
            </div>
        </div>
    @else
        <div class="score-banner pending">
            <div class="score-circle" style="background:#e8a020;">
                <span class="v">{{ rtrim(rtrim(number_format($copie->score_qcm,2,',',''),'0'),',') }}</span>
                <span class="m">/ {{ rtrim(rtrim(number_format($copie->bareme_qcm,2,',',''),'0'),',') }}</span>
            </div>
            <div>
                <div style="font-size:15px; font-weight:700;"><i class="fa-solid fa-hourglass-half" style="color:#e8a020;"></i> Copie envoyée</div>
                <div style="font-size:12.5px; color:var(--text-muted); margin-top:3px;">
                    Score du QCM corrigé automatiquement. Les questions ouvertes seront corrigées par votre enseignant.
                </div>
            </div>
        </div>
    @endif

    @if(!empty($qcm))
    <div class="section-divider-title">Questions à choix multiples</div>
    @foreach($qcm as $i => $q)
        @php
            $num = $q['numero'] ?? ($i + 1);
            $choisi = $repQcm[$num] ?? null;
            $bonne  = $q['bonne_reponse'] ?? -1;
        @endphp
        <div class="question-card">
            <span class="question-points">{{ $q['points'] ?? 1 }} pt{{ ($q['points'] ?? 1) > 1 ? 's' : '' }}</span>
            <span class="question-num">{{ $num }}</span>
            <span class="question-text">{{ $q['question'] ?? '' }}</span>
            <div class="choix-list">
                @foreach($q['choix'] ?? [] as $idx => $choix)
                    @php
                        $estBonne   = ((int)$idx === (int)$bonne);
                        $estChoisi  = ($choisi !== null && (int)$idx === (int)$choisi);
                        $cls = $estBonne ? 'correct' : ($estChoisi ? 'wrong' : '');
                    @endphp
                    <div class="choix-item {{ $cls }}">
                        <div class="choix-letter">{{ chr(65 + $idx) }}</div>
                        {{ $choix }}
                        @if($estBonne)<span class="tag-result ok"><i class="fa-solid fa-check"></i> Bonne réponse</span>
                        @elseif($estChoisi)<span class="tag-result no"><i class="fa-solid fa-xmark"></i> Votre réponse</span>@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
    @endif

    @if(!empty($ouvertes))
    <div class="section-divider-title">Questions ouvertes</div>
    @foreach($ouvertes as $i => $q)
        @php $num = $q['numero'] ?? ($i + 1); @endphp
        <div class="question-card">
            <span class="question-points">{{ $q['points'] ?? 2 }} pts</span>
            <span class="question-num">{{ $num }}</span>
            <span class="question-text">{{ $q['question'] ?? '' }}</span>
            <div class="ouverte-reponse">
                <strong style="font-size:11px; color:var(--text-muted); display:block; margin-bottom:5px;">VOTRE RÉPONSE</strong>
                {{ $repOuv[$num] ?? '—' }}
            </div>
            @if(!empty($q['reponse_attendue']) && $copie->statut === 'corrige')
            <div class="ouverte-attendue">
                <span class="lbl">Réponse attendue</span>
                {{ $q['reponse_attendue'] }}
            </div>
            @endif
        </div>
    @endforeach
    @endif

{{-- ════════════ PAS ENCORE SOUMIS : formulaire ════════════ --}}
@else

    <form method="POST" action="{{ route('eleve.examens.soumettre', $examen) }}" id="examForm">
        @csrf

        @if(!empty($qcm))
        <div class="section-divider-title">Questions à choix multiples</div>
        @foreach($qcm as $i => $q)
            @php $num = $q['numero'] ?? ($i + 1); @endphp
            <div class="question-card">
                <span class="question-points">{{ $q['points'] ?? 1 }} pt{{ ($q['points'] ?? 1) > 1 ? 's' : '' }}</span>
                <span class="question-num">{{ $num }}</span>
                <span class="question-text">{{ $q['question'] ?? '' }}</span>
                <div class="choix-list">
                    @foreach($q['choix'] ?? [] as $idx => $choix)
                        <label class="choix-item">
                            <input type="radio" name="qcm[{{ $num }}]" value="{{ $idx }}">
                            <div class="choix-letter">{{ chr(65 + $idx) }}</div>
                            {{ $choix }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
        @endif

        @if(!empty($ouvertes))
        <div class="section-divider-title">Questions ouvertes</div>
        @foreach($ouvertes as $i => $q)
            @php $num = $q['numero'] ?? ($i + 1); @endphp
            <div class="question-card">
                <span class="question-points">{{ $q['points'] ?? 2 }} pts</span>
                <span class="question-num">{{ $num }}</span>
                <span class="question-text">{{ $q['question'] ?? '' }}</span>
                <textarea class="ouverte-textarea" name="ouvertes[{{ $num }}]" placeholder="Votre réponse…"></textarea>
            </div>
        @endforeach
        @endif

        <div class="submit-bar">
            <span style="font-size:12.5px; color:var(--text-muted);">
                <i class="fa-solid fa-circle-info"></i> Une fois envoyées, vos réponses ne pourront plus être modifiées.
            </span>
            <button type="submit" class="btn-submit" onclick="return confirm('Envoyer définitivement vos réponses ?');">
                <i class="fa-solid fa-paper-plane"></i> Envoyer mes réponses
            </button>
        </div>
    </form>

@endif

@endsection
