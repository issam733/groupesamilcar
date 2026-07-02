@extends('enseignant.layouts.app')

@section('title', $examen->titre)
@section('page-title', 'Examen généré')
@section('page-subtitle', $examen->titre)

@section('extra-css')
<style>
    .top-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
    .examen-header-card { background:linear-gradient(135deg,#6d28d9,#1a4fa0); border-radius:16px; padding:24px 28px; color:#fff; margin-bottom:20px; }
    .examen-header-card h2 { font-size:20px; font-weight:800; margin-bottom:8px; }
    .examen-header-meta { display:flex; gap:16px; flex-wrap:wrap; font-size:12.5px; opacity:.9; }
    .examen-header-meta span { display:flex; align-items:center; gap:6px; }
    .question-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 22px; margin-bottom:14px; box-shadow:var(--shadow-sm); }
    .question-num { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background:#f3eeff; color:var(--primary); font-size:12px; font-weight:700; margin-right:10px; }
    .question-text { font-size:14px; font-weight:600; display:inline; }
    .question-points { float:right; font-size:11px; font-weight:700; color:var(--text-muted); background:var(--bg); padding:3px 10px; border-radius:20px; }
    .choix-list { margin-top:14px; display:grid; gap:8px; }
    .choix-item { display:flex; align-items:center; gap:10px; padding:9px 14px; border-radius:9px; background:var(--bg); border:1.5px solid transparent; font-size:13px; }
    .choix-item.correct { background:#ecfdf5; border-color:#a7f3d0; }
    .choix-letter { width:22px; height:22px; border-radius:50%; background:#fff; border:1.5px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:var(--text-muted); flex-shrink:0; }
    .choix-item.correct .choix-letter { background:var(--success); color:#fff; border-color:var(--success); }
    .choix-item.correct .correct-icon { color:var(--success); margin-left:auto; }
    .explication-box { margin-top:12px; padding:10px 14px; background:#fffbeb; border-radius:8px; font-size:12px; color:#92400e; display:flex; gap:8px; }
    .reponse-attendue-box { margin-top:14px; padding:14px 16px; background:#f3eeff; border-radius:10px; font-size:13px; line-height:1.5; }
    .reponse-attendue-label { font-size:10px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
    .section-divider-title { font-size:13px; font-weight:700; margin:28px 0 16px; display:flex; align-items:center; gap:10px; }
    .section-divider-title::after { content:''; flex:1; height:1px; background:var(--border); }
    .corrige-toggle { display:flex; align-items:center; gap:10px; background:var(--card); border:1px solid var(--border); border-radius:12px; padding:14px 18px; margin-bottom:20px; }
    .toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; transition:.3s; cursor:pointer; }
    .toggle-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
    input:checked + .toggle-slider { background:var(--success); }
    input:checked + .toggle-slider::before { transform:translateX(20px); }
    .send-banner { border-radius:14px; padding:18px 22px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; }
    .send-banner.todo { background:#fffbeb; border:1px solid #fde68a; }
    .send-banner.done { background:#ecfdf5; border:1px solid #a7f3d0; }
    .send-banner .txt { font-size:13.5px; font-weight:600; }
    .send-banner .sub { font-size:12px; color:var(--text-muted); font-weight:400; margin-top:2px; }
</style>
@endsection

@section('content')

<div class="top-actions">
    <a href="{{ route('enseignant.examens.index') }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Mes examens</a>
    <div style="display:flex; gap:10px;">
        @if($examen->statut === 'envoye')
        <a href="{{ route('enseignant.examens.copies', $examen) }}" class="btn-am primary"><i class="fa-solid fa-file-lines"></i> Copies reçues</a>
        @endif
        <a href="{{ route('enseignant.examens.pdf', $examen) }}?corrige=0" target="_blank" class="btn-am secondary"><i class="fa-solid fa-print"></i> Sujet</a>
        <a href="{{ route('enseignant.examens.pdf', $examen) }}?corrige=1" target="_blank" class="btn-am secondary"><i class="fa-solid fa-key"></i> Avec corrigé</a>
    </div>
</div>

<!-- Bandeau d'envoi aux élèves -->
@if($examen->statut === 'envoye')
    <div class="send-banner done">
        <div>
            <div class="txt"><i class="fa-solid fa-circle-check" style="color:var(--success);"></i> Envoyé aux élèves de {{ $examen->classe->nom ?? 'la classe' }}</div>
            <div class="sub">Les élèves de cette classe peuvent consulter l'examen depuis leur espace.</div>
        </div>
        <form method="POST" action="{{ route('enseignant.examens.retirer', $examen) }}">
            @csrf
            <button class="btn-am secondary sm"><i class="fa-solid fa-rotate-left"></i> Retirer de l'espace élèves</button>
        </form>
    </div>
@else
    <div class="send-banner todo">
        <div>
            <div class="txt"><i class="fa-solid fa-paper-plane" style="color:var(--warning);"></i> Pas encore envoyé</div>
            <div class="sub">
                @if($examen->classe_id)
                    Une fois vérifié, envoyez cet examen aux élèves de {{ $examen->classe->nom }}.
                @else
                    Aucune classe n'est associée : régénérez l'examen en choisissant une classe pour pouvoir l'envoyer.
                @endif
            </div>
        </div>
        @if($examen->classe_id)
        <form method="POST" action="{{ route('enseignant.examens.envoyer', $examen) }}" onsubmit="return confirm('Envoyer cet examen aux élèves de {{ $examen->classe->nom }} ?');">
            @csrf
            <button class="btn-am success"><i class="fa-solid fa-paper-plane"></i> Envoyer aux élèves</button>
        </form>
        @endif
    </div>
@endif

<div class="examen-header-card">
    <h2>{{ $contenu['titre'] ?? $examen->titre }}</h2>
    <div class="examen-header-meta">
        <span><i class="fa-solid fa-door-open"></i> {{ $examen->classe->nom ?? 'Classe non spécifiée' }}</span>
        <span><i class="fa-solid fa-book"></i> {{ $contenu['matiere'] ?? $examen->matiere->nom ?? '—' }}</span>
        <span><i class="fa-regular fa-clock"></i> {{ $contenu['duree_minutes'] ?? 60 }} min</span>
        <span><i class="fa-solid fa-scale-balanced"></i> /{{ $contenu['bareme_total'] ?? 20 }}</span>
        <span><i class="fa-solid fa-gauge"></i> {{ ucfirst($examen->difficulte) }}</span>
    </div>
</div>

<div class="corrige-toggle">
    <label class="toggle-switch">
        <input type="checkbox" id="toggleCorrige" onchange="toggleCorrige(this)">
        <span class="toggle-slider"></span>
    </label>
    <div>
        <div style="font-size:13px; font-weight:600;">Afficher le corrigé</div>
        <div style="font-size:11px; color:var(--text-muted);">Bonnes réponses et explications (visible uniquement par vous)</div>
    </div>
</div>

@if(!empty($contenu['qcm']))
<div class="section-divider-title">Questions à choix multiples ({{ count($contenu['qcm']) }})</div>
@foreach($contenu['qcm'] as $q)
<div class="question-card">
    <span class="question-points">{{ $q['points'] ?? 1 }} pt{{ ($q['points'] ?? 1) > 1 ? 's' : '' }}</span>
    <span class="question-num">{{ $q['numero'] ?? $loop->iteration }}</span>
    <span class="question-text">{{ $q['question'] ?? '' }}</span>
    <div class="choix-list">
        @foreach($q['choix'] ?? [] as $idx => $choix)
        <div class="choix-item correct-answer" data-correct="{{ $idx == ($q['bonne_reponse'] ?? -1) ? '1' : '0' }}">
            <div class="choix-letter">{{ chr(65 + $idx) }}</div>
            {{ $choix }}
            <i class="fa-solid fa-circle-check correct-icon" style="display:none;"></i>
        </div>
        @endforeach
    </div>
    @if(!empty($q['explication']))
    <div class="explication-box explication-corrige" style="display:none;">
        <i class="fa-solid fa-lightbulb"></i><div><strong>Explication :</strong> {{ $q['explication'] }}</div>
    </div>
    @endif
</div>
@endforeach
@endif

@if(!empty($contenu['questions_ouvertes']))
<div class="section-divider-title">Questions ouvertes ({{ count($contenu['questions_ouvertes']) }})</div>
@foreach($contenu['questions_ouvertes'] as $q)
<div class="question-card">
    <span class="question-points">{{ $q['points'] ?? 2 }} pts</span>
    <span class="question-num">{{ $q['numero'] ?? $loop->iteration }}</span>
    <span class="question-text">{{ $q['question'] ?? '' }}</span>
    @if(!empty($q['reponse_attendue']))
    <div class="reponse-attendue-box reponse-corrige" style="display:none;">
        <div class="reponse-attendue-label">Réponse attendue / corrigé</div>
        {{ $q['reponse_attendue'] }}
    </div>
    @endif
</div>
@endforeach
@endif

@endsection

@section('scripts')
<script>
function toggleCorrige(cb) {
    const show = cb.checked;
    document.querySelectorAll('.correct-answer').forEach(el => {
        const ok = el.dataset.correct === '1';
        el.classList.toggle('correct', show && ok);
        const icon = el.querySelector('.correct-icon');
        if (icon) icon.style.display = (show && ok) ? 'block' : 'none';
    });
    document.querySelectorAll('.explication-corrige, .reponse-corrige').forEach(el => el.style.display = show ? 'block' : 'none');
}
</script>
@endsection
