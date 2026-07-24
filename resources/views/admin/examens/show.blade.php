@extends('admin.layouts.app')

@section('title', $examen->titre)
@section('page-title', 'Examen généré')
@section('page-subtitle', $examen->titre)

@section('extra-css')
<style>
    .top-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-am.success { background:var(--success); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }

    .examen-header-card { background:linear-gradient(135deg,#7c5cbf,#1a4fa0); border-radius:16px; padding:24px 28px; color:#fff; margin-bottom:20px; }
    .examen-header-card h2 { font-size:20px; font-weight:800; margin-bottom:8px; }
    .examen-header-meta { display:flex; gap:16px; flex-wrap:wrap; font-size:12.5px; opacity:.9; }
    .examen-header-meta span { display:flex; align-items:center; gap:6px; }

    .question-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 22px; margin-bottom:14px; box-shadow:var(--shadow-sm); }
    .question-num { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background:#eef3ff; color:var(--primary); font-size:12px; font-weight:700; margin-right:10px; }
    .question-text { font-size:14px; font-weight:600; color:var(--text); display:inline; }
    .question-points { float:right; font-size:11px; font-weight:700; color:var(--text-muted); background:var(--bg); padding:3px 10px; border-radius:20px; }

    .choix-list { margin-top:14px; display:grid; gap:8px; }
    .choix-item { display:flex; align-items:center; gap:10px; padding:9px 14px; border-radius:9px; background:var(--bg); border:1.5px solid transparent; font-size:13px; }
    .choix-item.correct { background:#ecfdf5; border-color:#a7f3d0; }
    .choix-letter { width:22px; height:22px; border-radius:50%; background:#fff; border:1.5px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:var(--text-muted); flex-shrink:0; }
    .choix-item.correct .choix-letter { background:var(--success); color:#fff; border-color:var(--success); }
    .choix-item.correct i { color:var(--success); margin-left:auto; }

    .explication-box { margin-top:12px; padding:10px 14px; background:#fffbeb; border-radius:8px; font-size:12px; color:#92400e; display:flex; gap:8px; }

    .reponse-attendue-box { margin-top:14px; padding:14px 16px; background:#eef3ff; border-radius:10px; font-size:13px; color:var(--text); line-height:1.5; }
    .reponse-attendue-label { font-size:10px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }

    .section-divider-title { font-size:13px; font-weight:700; color:var(--text); margin:28px 0 16px; display:flex; align-items:center; gap:10px; }
    .section-divider-title::after { content:''; flex:1; height:1px; background:var(--border); }

    .corrige-toggle { display:flex; align-items:center; gap:10px; background:var(--card); border:1px solid var(--border); border-radius:12px; padding:14px 18px; margin-bottom:20px; }
    .toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; transition:.3s; cursor:pointer; }
    .toggle-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
    input:checked + .toggle-slider { background:var(--success); }
    input:checked + .toggle-slider::before { transform:translateX(20px); }

    /* ═══════ Éditeur de questions (mode brouillon) ═══════ */
    .editor-toolbar { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; background:#eef3ff; border:1px solid #c7d7f7; border-radius:12px; padding:12px 18px; margin-bottom:18px; }
    .editor-toolbar .txt { font-size:12.5px; color:#1a4fa0; font-weight:600; }
    .editor-title-input { width:100%; font-size:19px; font-weight:800; color:#fff; background:rgba(255,255,255,.12); border:1.5px dashed rgba(255,255,255,.4); border-radius:8px; padding:8px 12px; margin-bottom:8px; font-family:inherit; }
    .editor-title-input::placeholder { color:rgba(255,255,255,.7); }
    .texte-support-editor { width:100%; border:1.5px solid var(--border); border-radius:10px; padding:14px 16px; font-size:13.5px; line-height:1.7; font-family:inherit; resize:vertical; min-height:100px; color:var(--text); }
    .texte-support-editor:focus { outline:none; border-color:var(--primary); }

    .q-card { background:var(--card); border:1.5px solid var(--border); border-radius:14px; padding:16px 18px 18px; margin-bottom:14px; cursor:default; }
    .q-card.dragging { opacity:.4; }
    .q-card-head { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
    .drag-handle { cursor:grab; color:var(--text-muted); font-size:14px; padding:4px 6px; border-radius:6px; }
    .drag-handle:hover { background:var(--bg); color:var(--text); }
    .drag-handle:active { cursor:grabbing; }
    .q-badge { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; padding:3px 10px; border-radius:20px; background:#eef3ff; color:#1a4fa0; }
    .q-badge.ouverte { background:#fff7ed; color:#c2410c; }
    .q-points-wrap { margin-left:auto; display:flex; align-items:center; gap:6px; font-size:11.5px; color:var(--text-muted); }
    .q-points { width:52px; padding:5px 7px; border:1.5px solid var(--border); border-radius:7px; font-size:12.5px; text-align:center; font-family:inherit; }
    .q-delete { background:#fff; border:1.5px solid #fecaca; color:var(--danger); width:30px; height:30px; border-radius:8px; cursor:pointer; flex-shrink:0; }
    .q-delete:hover { background:#fef2f2; }
    .q-question { width:100%; border:1.5px solid var(--border); border-radius:9px; padding:10px 12px; font-size:13.5px; font-weight:600; font-family:inherit; resize:vertical; margin-bottom:12px; color:var(--text); }
    .q-question:focus, .q-reponse:focus, .q-explication:focus, .q-choix-text:focus { outline:none; border-color:var(--primary); }
    .q-choix-list { display:grid; gap:8px; margin-bottom:10px; }
    .q-choix-item { display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:9px; background:var(--bg); border:1.5px solid transparent; }
    .q-choix-item:has(.q-correct-radio:checked) { background:#ecfdf5; border-color:#a7f3d0; }
    .q-correct-radio { width:17px; height:17px; accent-color:var(--success); flex-shrink:0; cursor:pointer; }
    .q-choix-text { flex:1; border:1.5px solid var(--border); border-radius:7px; padding:7px 10px; font-size:13px; font-family:inherit; background:#fff; }
    .q-explication { width:100%; border:1.5px solid var(--border); border-radius:9px; padding:9px 12px; font-size:12px; font-family:inherit; resize:vertical; color:#92400e; background:#fffbeb; }
    .q-reponse { width:100%; border:1.5px solid var(--border); border-radius:9px; padding:10px 12px; font-size:13px; font-family:inherit; resize:vertical; background:#eef3ff; }
    .add-q-btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:10px; border:1.5px dashed var(--border); background:transparent; color:var(--text-muted); font-size:13px; font-weight:600; cursor:pointer; width:100%; justify-content:center; margin-bottom:22px; }
    .add-q-btn:hover { border-color:var(--primary); color:var(--primary); background:#f7f9fd; }
    .save-bar { position:sticky; bottom:0; background:var(--card); border:1px solid var(--border); border-radius:14px; padding:14px 20px; margin-top:10px; display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; box-shadow:0 -4px 20px rgba(0,0,0,.06); z-index:20; }
    #saveStatus { font-size:12px; font-weight:600; }
    #saveStatus.ok { color:#0f766e; } #saveStatus.ko { color:var(--danger); } #saveStatus.pending { color:var(--text-muted); }
</style>
@endsection

@section('content')

<div class="top-actions">
    <a href="{{ route('admin.examens.index') }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour à l'historique</a>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.examens.pdf', $examen) }}?corrige=0" target="_blank" class="btn-am secondary">
            <i class="fa-solid fa-print"></i> Imprimer le sujet
        </a>
        <a href="{{ route('admin.examens.pdf', $examen) }}?corrige=1" target="_blank" class="btn-am success">
            <i class="fa-solid fa-key"></i> Imprimer avec corrigé
        </a>
    </div>
</div>

<!-- Header examen -->
<div class="examen-header-card">
    @if($examen->statut === 'genere')
        <input type="text" id="editTitre" class="editor-title-input" value="{{ $contenu['titre'] ?? $examen->titre }}" placeholder="Titre de l'examen">
    @else
        <h2>{{ $contenu['titre'] ?? $examen->titre }}</h2>
    @endif
    <div class="examen-header-meta">
        <span><i class="fa-solid fa-door-open"></i> {{ $examen->classe->nom ?? 'Classe non spécifiée' }}</span>
        <span><i class="fa-solid fa-book"></i> {{ $contenu['matiere'] ?? $examen->matiere->nom ?? '—' }}</span>
        <span><i class="fa-regular fa-clock"></i> {{ $contenu['duree_minutes'] ?? 60 }} minutes</span>
        <span><i class="fa-solid fa-scale-balanced"></i> Barème sur <span id="baremeTotalDisplay">{{ $contenu['bareme_total'] ?? 20 }}</span></span>
        <span><i class="fa-solid fa-gauge"></i> {{ ucfirst($examen->difficulte) }}</span>
    </div>
</div>

@if($examen->statut === 'genere')
    {{-- ═══════════════════ MODE ÉDITION (brouillon) ═══════════════════ --}}
    <div class="editor-toolbar">
        <span class="txt"><i class="fa-solid fa-pen-to-square"></i> Mode édition — modifiez, ajoutez, supprimez ou réorganisez les questions</span>
    </div>

    <div class="section-divider-title">Texte à étudier (optionnel)</div>
    <textarea id="editTexteSupport" class="texte-support-editor" placeholder="Collez ici le texte à étudier si cet examen en comporte un...">{{ $contenu['texte_support'] ?? '' }}</textarea>

    <div class="section-divider-title" style="margin-top:26px;">Questions à choix multiples</div>
    <div id="qcmContainer">
        @foreach(($contenu['qcm'] ?? []) as $q)
        <div class="q-card qcm-card" draggable="true">
            <div class="q-card-head">
                <span class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                <span class="q-badge">QCM</span>
                <div class="q-points-wrap"><input type="number" class="q-points" value="{{ $q['points'] ?? 1 }}" min="0" step="0.5"> pts</div>
                <button type="button" class="q-delete" onclick="this.closest('.q-card').remove()"><i class="fa-solid fa-trash"></i></button>
            </div>
            <textarea class="q-question" rows="2" placeholder="Texte de la question...">{{ $q['question'] ?? '' }}</textarea>
            <div class="q-choix-list">
                @php $uid = 'qcm_'.uniqid(); @endphp
                @foreach(($q['choix'] ?? ['','','','']) as $idx => $choix)
                <div class="q-choix-item">
                    <input type="radio" name="correct-{{ $uid }}" class="q-correct-radio" {{ $idx == ($q['bonne_reponse'] ?? -1) ? 'checked' : '' }}>
                    <span class="choix-letter">{{ chr(65 + $idx) }}</span>
                    <input type="text" class="q-choix-text" value="{{ $choix }}">
                </div>
                @endforeach
            </div>
            <textarea class="q-explication" rows="1" placeholder="Explication de la bonne réponse (optionnel)...">{{ $q['explication'] ?? '' }}</textarea>
        </div>
        @endforeach
    </div>
    <button type="button" class="add-q-btn" onclick="ajouterQcm()"><i class="fa-solid fa-plus"></i> Ajouter une question QCM</button>

    <div class="section-divider-title">Questions ouvertes</div>
    <div id="ouvertesContainer">
        @foreach(($contenu['questions_ouvertes'] ?? []) as $q)
        <div class="q-card ouverte-card" draggable="true">
            <div class="q-card-head">
                <span class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                <span class="q-badge ouverte">Ouverte</span>
                <div class="q-points-wrap"><input type="number" class="q-points" value="{{ $q['points'] ?? 2 }}" min="0" step="0.5"> pts</div>
                <button type="button" class="q-delete" onclick="this.closest('.q-card').remove()"><i class="fa-solid fa-trash"></i></button>
            </div>
            <textarea class="q-question" rows="2" placeholder="Texte de la question...">{{ $q['question'] ?? '' }}</textarea>
            <textarea class="q-reponse" rows="2" placeholder="Réponse attendue / corrigé...">{{ $q['reponse_attendue'] ?? '' }}</textarea>
        </div>
        @endforeach
    </div>
    <button type="button" class="add-q-btn" onclick="ajouterOuverte()"><i class="fa-solid fa-plus"></i> Ajouter une question ouverte</button>

    <div class="save-bar">
        <span id="saveStatus" class="pending"><i class="fa-solid fa-circle-info"></i> Modifications non enregistrées seront perdues si vous quittez la page</span>
        <button type="button" class="btn-am primary" onclick="enregistrerModifications()" id="btnSave"><i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications</button>
    </div>

@else
    {{-- ═══════════════════ MODE LECTURE SEULE (déjà envoyé par l'enseignant) ═══════════════════ --}}
    @if(!empty($contenu['texte_support']))
    <div class="section-divider-title">Texte à étudier</div>
    <div class="question-card" style="white-space:pre-wrap; line-height:1.7; font-size:13.5px;">
        {{ $contenu['texte_support'] }}
    </div>
    @endif

    <!-- Toggle corrigé -->
    <div class="corrige-toggle">
        <label class="toggle-switch">
            <input type="checkbox" id="toggleCorrige" onchange="toggleCorrige(this)">
            <span class="toggle-slider"></span>
        </label>
        <div>
            <div style="font-size:13px; font-weight:600; color:var(--text);">Afficher le corrigé</div>
            <div style="font-size:11px; color:var(--text-muted);">Voir les bonnes réponses et explications</div>
        </div>
    </div>

    <!-- QCM -->
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
            <i class="fa-solid fa-lightbulb"></i>
            <div><strong>Explication :</strong> {{ $q['explication'] }}</div>
        </div>
        @endif
    </div>
    @endforeach
    @endif

    <!-- Questions ouvertes -->
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
@endif

@endsection

@section('scripts')
<script>
function toggleCorrige(checkbox) {
    const show = checkbox.checked;

    document.querySelectorAll('.correct-answer').forEach(el => {
        const isCorrect = el.dataset.correct === '1';
        el.classList.toggle('correct', show && isCorrect);
        el.querySelector('.correct-icon').style.display = (show && isCorrect) ? 'block' : 'none';
    });

    document.querySelectorAll('.explication-corrige, .reponse-corrige').forEach(el => {
        el.style.display = show ? 'block' : 'none';
    });
}

@if($examen->statut === 'genere')
/* ═══════════════════ ÉDITEUR DE QUESTIONS ═══════════════════ */

let qcmUidCounter = 0;

function ajouterQcm() {
    const uid = 'qcm_new_' + (qcmUidCounter++) + '_' + Date.now();
    const div = document.createElement('div');
    div.className = 'q-card qcm-card';
    div.draggable = true;
    div.innerHTML = `
        <div class="q-card-head">
            <span class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
            <span class="q-badge">QCM</span>
            <div class="q-points-wrap"><input type="number" class="q-points" value="1" min="0" step="0.5"> pts</div>
            <button type="button" class="q-delete" onclick="this.closest('.q-card').remove()"><i class="fa-solid fa-trash"></i></button>
        </div>
        <textarea class="q-question" rows="2" placeholder="Texte de la question..."></textarea>
        <div class="q-choix-list">
            ${[0,1,2,3].map(i => `
            <div class="q-choix-item">
                <input type="radio" name="correct-${uid}" class="q-correct-radio" ${i === 0 ? 'checked' : ''}>
                <span class="choix-letter">${String.fromCharCode(65+i)}</span>
                <input type="text" class="q-choix-text" value="" placeholder="Choix ${String.fromCharCode(65+i)}">
            </div>`).join('')}
        </div>
        <textarea class="q-explication" rows="1" placeholder="Explication de la bonne réponse (optionnel)..."></textarea>
    `;
    document.getElementById('qcmContainer').appendChild(div);
    div.querySelector('.q-question').focus();
    marquerNonSauvegarde();
}

function ajouterOuverte() {
    const div = document.createElement('div');
    div.className = 'q-card ouverte-card';
    div.draggable = true;
    div.innerHTML = `
        <div class="q-card-head">
            <span class="drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
            <span class="q-badge ouverte">Ouverte</span>
            <div class="q-points-wrap"><input type="number" class="q-points" value="2" min="0" step="0.5"> pts</div>
            <button type="button" class="q-delete" onclick="this.closest('.q-card').remove()"><i class="fa-solid fa-trash"></i></button>
        </div>
        <textarea class="q-question" rows="2" placeholder="Texte de la question..."></textarea>
        <textarea class="q-reponse" rows="2" placeholder="Réponse attendue / corrigé..."></textarea>
    `;
    document.getElementById('ouvertesContainer').appendChild(div);
    div.querySelector('.q-question').focus();
    marquerNonSauvegarde();
}

/* --- Glisser-déposer (réorganisation) --- */
function getDragAfterElement(container, y) {
    const cards = [...container.querySelectorAll('.q-card:not(.dragging)')];
    return cards.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
        }
        return closest;
    }, { offset: -Infinity }).element;
}

function activerGlisserDeposer(containerId) {
    const container = document.getElementById(containerId);
    let dragEl = null;

    container.addEventListener('dragstart', e => {
        if (!e.target.closest('.drag-handle')) { e.preventDefault(); return; }
        dragEl = e.target.closest('.q-card');
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => dragEl.classList.add('dragging'), 0);
    });

    container.addEventListener('dragend', () => {
        if (dragEl) dragEl.classList.remove('dragging');
        dragEl = null;
        marquerNonSauvegarde();
    });

    container.addEventListener('dragover', e => {
        e.preventDefault();
        if (!dragEl) return;
        const afterEl = getDragAfterElement(container, e.clientY);
        if (afterEl == null) container.appendChild(dragEl);
        else container.insertBefore(dragEl, afterEl);
    });
}
activerGlisserDeposer('qcmContainer');
activerGlisserDeposer('ouvertesContainer');

/* --- Indicateur de modifications non enregistrées --- */
function marquerNonSauvegarde() {
    const status = document.getElementById('saveStatus');
    status.className = 'pending';
    status.innerHTML = '<i class="fa-solid fa-circle-info"></i> Modifications non enregistrées';
}
document.addEventListener('input', e => {
    if (e.target.closest('#qcmContainer, #ouvertesContainer, #editTexteSupport, #editTitre')) marquerNonSauvegarde();
});

/* --- Collecte des données et sauvegarde --- */
function collecterDonnees() {
    const qcm = [...document.querySelectorAll('#qcmContainer .q-card')].map(card => {
        const choixInputs = [...card.querySelectorAll('.q-choix-text')];
        const radios = [...card.querySelectorAll('.q-correct-radio')];
        return {
            question: card.querySelector('.q-question').value.trim(),
            choix: choixInputs.map(i => i.value.trim()),
            bonne_reponse: Math.max(0, radios.findIndex(r => r.checked)),
            points: parseFloat(card.querySelector('.q-points').value) || 0,
            explication: card.querySelector('.q-explication').value.trim(),
        };
    }).filter(q => q.question !== '');

    const ouvertes = [...document.querySelectorAll('#ouvertesContainer .q-card')].map(card => ({
        question: card.querySelector('.q-question').value.trim(),
        reponse_attendue: card.querySelector('.q-reponse').value.trim(),
        points: parseFloat(card.querySelector('.q-points').value) || 0,
    })).filter(q => q.question !== '');

    return {
        titre: document.getElementById('editTitre').value.trim(),
        texte_support: document.getElementById('editTexteSupport').value,
        qcm,
        questions_ouvertes: ouvertes,
    };
}

async function enregistrerModifications() {
    const btn = document.getElementById('btnSave');
    const status = document.getElementById('saveStatus');
    const payload = collecterDonnees();

    if (payload.qcm.length === 0 && payload.questions_ouvertes.length === 0) {
        status.className = 'ko';
        status.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Ajoutez au moins une question avant d\'enregistrer.';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';

    try {
        const res = await fetch('{{ route("admin.examens.questions.sauvegarder", $examen) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (res.ok && data.success) {
            status.className = 'ok';
            status.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
            document.getElementById('baremeTotalDisplay').textContent = data.bareme_total;
        } else {
            status.className = 'ko';
            status.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + (data.message || 'Erreur lors de l\'enregistrement.');
        }
    } catch (e) {
        status.className = 'ko';
        status.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Erreur réseau lors de l\'enregistrement.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications';
    }
}

window.addEventListener('beforeunload', e => {
    const status = document.getElementById('saveStatus');
    if (status.className === 'pending') {
        e.preventDefault();
        e.returnValue = '';
    }
});
@endif
</script>
@endsection
