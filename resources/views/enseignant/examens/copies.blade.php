@extends('enseignant.layouts.app')

@section('title', 'Copies — ' . $examen->titre)
@section('page-title', 'Copies reçues')
@section('page-subtitle', $examen->titre . ' · ' . ($examen->classe->nom ?? ''))

@section('extra-css')
<style>
    .copie-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm); margin-bottom:16px; overflow:hidden; }
    .copie-head { display:flex; align-items:center; gap:14px; padding:16px 20px; border-bottom:1px solid var(--border); flex-wrap:wrap; }
    .copie-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#6d28d9,#8b5cf6); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; }
    .copie-score { margin-left:auto; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .pill-score { background:#f3eeff; color:var(--primary); border-radius:20px; padding:5px 14px; font-size:12.5px; font-weight:700; }
    .pill-score.final { background:#ecfdf5; color:#0f766e; }
    .copie-body { padding:16px 20px; }
    .oq { padding:12px 0; border-bottom:1px dashed var(--border); }
    .oq:last-child { border-bottom:none; }
    .oq-q { font-size:13px; font-weight:600; margin-bottom:6px; }
    .oq-a { font-size:13px; background:var(--bg); border-radius:8px; padding:10px 12px; white-space:pre-wrap; }
    .oq-att { font-size:12px; color:#0f766e; background:#f0fdfa; border:1px solid #99f6e4; border-radius:8px; padding:8px 12px; margin-top:6px; }
    .note-form { display:flex; align-items:center; gap:10px; padding:14px 20px; background:#faf8ff; border-top:1px solid var(--border); flex-wrap:wrap; }
    .note-form input { width:90px; padding:8px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:13.5px; text-align:center; outline:none; }
    .note-form input:focus { border-color:var(--primary); }
</style>
@endsection

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
    <a href="{{ route('enseignant.examens.show', $examen) }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour à l'examen</a>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#6d28d9,#8b5cf6);"><i class="fa-solid fa-file-lines"></i></div>
        <div><div class="stat-val">{{ $copies->count() }}{{ $nbEleves ? ' / ' . $nbEleves : '' }}</div><div class="stat-lbl">Copies reçues</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6);"><i class="fa-solid fa-check-double"></i></div>
        <div><div class="stat-val">{{ $copies->where('statut','corrige')->count() }}</div><div class="stat-lbl">Corrigées</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#e8a020,#f0b955);"><i class="fa-solid fa-hourglass-half"></i></div>
        <div><div class="stat-val">{{ $copies->where('statut','soumis')->count() }}</div><div class="stat-lbl">À corriger</div></div>
    </div>
</div>

@php $ouvertes = $contenu['questions_ouvertes'] ?? []; $bareme = $contenu['bareme_total'] ?? 20; $rc = $examen->rapport_classe; @endphp

{{-- ─── Rapport de synthèse de la CLASSE (IA) ─── --}}
<div class="page-card" style="border-left:4px solid var(--primary);">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; @if($rc)margin-bottom:16px;@endif">
        <h3 style="margin:0;"><i class="fa-solid fa-chart-pie" style="color:var(--primary);"></i> Rapport de classe IA</h3>
        <form method="POST" action="{{ route('enseignant.examens.rapport.classe', $examen) }}" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Analyse…';">
            @csrf
            <button class="btn-am {{ $rc ? 'secondary' : 'primary' }} sm" {{ $copies->isEmpty() ? 'disabled' : '' }}>
                <i class="fa-solid fa-wand-magic-sparkles"></i> {{ $rc ? 'Régénérer la synthèse' : 'Générer la synthèse de classe' }}
            </button>
        </form>
    </div>

    @if($rc)
        @if(!empty($rc['synthese']))
            <p style="font-size:13.5px; line-height:1.6; margin-bottom:14px;">{{ $rc['synthese'] }}</p>
        @endif
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px;">
            @php
                $blocsC = [
                    ['Lacunes récurrentes', $rc['lacunes_recurrentes'] ?? [], '#b45309', '#fffbeb', 'fa-circle-exclamation'],
                    ['Questions problématiques', $rc['questions_problematiques'] ?? [], '#b91c1c', '#fef2f2', 'fa-triangle-exclamation'],
                    ['Recommandations pédagogiques', $rc['recommandations_pedagogiques'] ?? [], '#5b21b6', '#f5f3ff', 'fa-lightbulb'],
                ];
            @endphp
            @foreach($blocsC as [$titre, $items, $col, $bg, $ic])
                @if(!empty($items))
                <div style="background:{{ $bg }}; border-radius:10px; padding:12px 14px;">
                    <div style="font-size:11px; font-weight:700; color:{{ $col }}; text-transform:uppercase; letter-spacing:.4px; margin-bottom:7px;"><i class="fa-solid {{ $ic }}"></i> {{ $titre }}</div>
                    <ul style="margin:0; padding-left:16px; font-size:12.5px; line-height:1.5;">@foreach($items as $it)<li>{{ $it }}</li>@endforeach</ul>
                </div>
                @endif
            @endforeach
        </div>
        @if(!empty($rc['suivi_eleves']))
            <div style="margin-top:12px; background:#eef2ff; border:1px solid #c7d2fe; border-radius:10px; padding:12px 14px;">
                <div style="font-size:11px; font-weight:700; color:#3730a3; text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px;"><i class="fa-solid fa-users-viewfinder"></i> Suivi des élèves en difficulté</div>
                <p style="font-size:12.5px; line-height:1.55; margin:0;">{{ $rc['suivi_eleves'] }}</p>
            </div>
        @endif
    @else
        <p style="font-size:12.5px; color:var(--text-muted); margin:0;">Génère une synthèse des lacunes récurrentes de la classe à partir de toutes les copies reçues, pour ajuster ton enseignement.</p>
    @endif
</div>

@forelse($copies as $copie)
    @php $repOuv = $copie->reponses['ouvertes'] ?? []; @endphp
    <div class="copie-card">
        <div class="copie-head">
            <div class="copie-avatar">{{ strtoupper(substr($copie->eleve->prenom ?? '?',0,1).substr($copie->eleve->nom ?? '',0,1)) }}</div>
            <div>
                <div style="font-weight:700; font-size:14px;">{{ $copie->eleve->prenom ?? '' }} {{ $copie->eleve->nom ?? 'Élève' }}</div>
                <div style="font-size:11.5px; color:var(--text-muted);">{{ $copie->eleve->matricule ?? '' }} · remis {{ $copie->created_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div class="copie-score">
                <span class="pill-score">QCM {{ rtrim(rtrim(number_format($copie->score_qcm,2,',',''),'0'),',') }}/{{ rtrim(rtrim(number_format($copie->bareme_qcm,2,',',''),'0'),',') }}</span>
                @if($copie->statut === 'corrige')
                    <span class="pill-score final"><i class="fa-solid fa-circle-check"></i> Note {{ rtrim(rtrim(number_format($copie->note_finale,2,',',''),'0'),',') }}/{{ $bareme }}</span>
                @else
                    <span class="badge amber"><i class="fa-solid fa-hourglass-half"></i> À corriger</span>
                @endif
            </div>
        </div>

        @if(!empty($ouvertes))
        <div class="copie-body">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:10px;">Réponses aux questions ouvertes</div>
            @foreach($ouvertes as $i => $q)
                @php $num = $q['numero'] ?? ($i + 1); @endphp
                <div class="oq">
                    <div class="oq-q">{{ $num }}. {{ $q['question'] ?? '' }} <span style="color:var(--text-muted); font-weight:400;">({{ $q['points'] ?? 2 }} pts)</span></div>
                    <div class="oq-a">{{ $repOuv[$num] ?? '— (sans réponse)' }}</div>
                    @if(!empty($q['reponse_attendue']))
                    <div class="oq-att"><strong>Attendu :</strong> {{ $q['reponse_attendue'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('enseignant.examens.copies.noter', [$examen, $copie]) }}" class="note-form">
            @csrf
            <span style="font-size:13px; font-weight:600;"><i class="fa-solid fa-pen"></i> Note finale :</span>
            <input type="number" name="note_finale" min="0" max="{{ $bareme }}" step="0.25"
                   value="{{ $copie->note_finale !== null ? rtrim(rtrim(number_format($copie->note_finale,2,'.',''),'0'),'.') : ($copie->score_qcm !== null ? rtrim(rtrim(number_format($copie->score_qcm,2,'.',''),'0'),'.') : '') }}"
                   placeholder="0">
            <span style="font-size:13px; color:var(--text-muted);">/ {{ $bareme }}</span>
            <button class="btn-am primary sm"><i class="fa-solid fa-floppy-disk"></i> Enregistrer la note</button>
            @if(empty($ouvertes))
                <span style="font-size:11.5px; color:var(--text-muted);">(QCM auto : pré-rempli avec le score du QCM)</span>
            @endif
        </form>

        {{-- ─── Rapport pédagogique IA ─── --}}
        @php $r = $copie->rapport; @endphp
        <div style="padding:16px 20px; border-top:1px solid var(--border); background:#fbfaff;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:@if($r)12px @else 0 @endif;">
                <div style="font-size:12.5px; font-weight:700; color:var(--primary);"><i class="fa-solid fa-robot"></i> Rapport IA — lacunes & difficultés</div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <form method="POST" action="{{ route('enseignant.examens.copies.rapport', [$examen, $copie]) }}" style="display:inline;">
                        @csrf
                        <button class="btn-am {{ $r ? 'secondary' : 'primary' }} sm">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> {{ $r ? 'Régénérer' : 'Générer le rapport IA' }}
                        </button>
                    </form>
                    @if($r)
                        <form method="POST" action="{{ route('enseignant.examens.copies.rapport.parent', [$examen, $copie]) }}" style="display:inline;">
                            @csrf
                            <button class="btn-am {{ $copie->rapport_envoye_parent ? 'secondary' : 'success' }} sm">
                                @if($copie->rapport_envoye_parent)
                                    <i class="fa-solid fa-eye-slash"></i> Retirer au parent
                                @else
                                    <i class="fa-solid fa-paper-plane"></i> Transmettre au parent
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if($r)
                @if($copie->rapport_envoye_parent)
                    <div class="badge green" style="margin-bottom:10px;"><i class="fa-solid fa-circle-check"></i> Transmis au parent</div>
                @endif

                @if(!empty($r['appreciation']))
                    <p style="font-size:13px; line-height:1.55; margin-bottom:12px;">{{ $r['appreciation'] }}</p>
                @endif

                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px;">
                    @php
                        $blocs = [
                            ['Points forts', $r['points_forts'] ?? [], '#0f766e', '#ecfdf5', 'fa-thumbs-up'],
                            ['Lacunes', $r['lacunes'] ?? [], '#b45309', '#fffbeb', 'fa-circle-exclamation'],
                            ['Difficultés', $r['difficultes'] ?? [], '#b91c1c', '#fef2f2', 'fa-triangle-exclamation'],
                            ['Recommandations', $r['recommandations'] ?? [], '#5b21b6', '#f5f3ff', 'fa-lightbulb'],
                        ];
                    @endphp
                    @foreach($blocs as [$titre, $items, $col, $bg, $ic])
                        @if(!empty($items))
                        <div style="background:{{ $bg }}; border-radius:10px; padding:12px 14px;">
                            <div style="font-size:11px; font-weight:700; color:{{ $col }}; text-transform:uppercase; letter-spacing:.4px; margin-bottom:7px;"><i class="fa-solid {{ $ic }}"></i> {{ $titre }}</div>
                            <ul style="margin:0; padding-left:16px; font-size:12.5px; line-height:1.5;">
                                @foreach($items as $it)<li>{{ $it }}</li>@endforeach
                            </ul>
                        </div>
                        @endif
                    @endforeach
                </div>

                @if(!empty($r['message_parent']))
                    <div style="margin-top:12px; background:#eef2ff; border:1px solid #c7d2fe; border-radius:10px; padding:12px 14px;">
                        <div style="font-size:11px; font-weight:700; color:#3730a3; text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px;"><i class="fa-solid fa-envelope"></i> Message aux parents</div>
                        <p style="font-size:12.5px; line-height:1.55; margin:0;">{{ $r['message_parent'] }}</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
@empty
    <div class="page-card"><div class="empty-state"><i class="fa-regular fa-file"></i> Aucune copie reçue pour le moment.<br><span style="font-size:12.5px;">Les copies apparaîtront ici dès que les élèves auront soumis leurs réponses.</span></div></div>
@endforelse

@endsection
