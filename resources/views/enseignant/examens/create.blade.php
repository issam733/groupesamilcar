@extends('enseignant.layouts.app')

@section('title', 'Générer un examen IA')
@section('page-title', 'Générer un examen IA')
@section('page-subtitle', 'Propulsé par l\'intelligence artificielle')

@section('extra-css')
<style>
    .layout-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }
    @media(max-width:1000px){ .layout-grid { grid-template-columns:1fr; } }
    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .form-header { padding:18px 26px; border-bottom:1px solid var(--border); background:linear-gradient(135deg,#6d28d9,#1a4fa0); display:flex; align-items:center; gap:12px; }
    .form-header-icon { width:36px; height:36px; background:rgba(255,255,255,.2); color:#fff; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:16px; }
    .form-header h5 { font-size:15px; font-weight:700; color:#fff; margin:0; }
    .form-header p { font-size:11px; color:rgba(255,255,255,.85); margin-top:2px; }
    .form-body { padding:26px; }
    .section-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:14px; }
    .form-row { display:grid; gap:16px; margin-bottom:18px; }
    .form-row.cols-2 { grid-template-columns:1fr 1fr; }
    .field-group label { display:block; font-size:12px; font-weight:600; margin-bottom:7px; }
    .field-group input, .field-group select, .field-group textarea { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; background:#fafaff; outline:none; transition:all .2s; }
    .field-group input:focus, .field-group select:focus, .field-group textarea:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(109,40,217,.1); }
    .field-group textarea { resize:vertical; min-height:150px; line-height:1.5; }
    .field-hint { font-size:11px; color:var(--text-muted); margin-top:5px; }
    .section-divider { border:none; border-top:1px solid var(--border); margin:22px 0; }
    .pill-row { display:flex; gap:8px; }
    .pill { flex:1; padding:11px; border-radius:9px; border:1.5px solid var(--border); background:#fafaff; text-align:center; font-size:12.5px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all .2s; }
    .pill.active { background:#f3eeff; color:var(--primary); border-color:var(--primary); }
    .pill .flag { font-size:20px; display:block; margin-bottom:3px; }
    .form-footer { padding:18px 26px; border-top:1px solid var(--border); background:#faf8ff; }
    .btn-generate { width:100%; padding:14px; background:linear-gradient(135deg,#6d28d9,#1a4fa0); border:none; border-radius:11px; color:#fff; font-size:14.5px; font-weight:700; cursor:pointer; transition:all .3s; display:flex; align-items:center; justify-content:center; gap:10px; }
    .btn-generate:hover { box-shadow:0 8px 25px rgba(109,40,217,.4); transform:translateY(-1px); }
    .btn-generate:disabled { opacity:.6; cursor:not-allowed; transform:none; }
    .tips-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:22px; box-shadow:var(--shadow-sm); }
    .tip-item { display:flex; gap:10px; margin-bottom:14px; }
    .tip-icon { width:28px; height:28px; border-radius:8px; background:#f3eeff; color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; }
    .tip-text { font-size:12px; color:var(--text-muted); line-height:1.5; }
    .api-warning { background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:14px 16px; margin-bottom:20px; font-size:12.5px; color:#991b1b; display:flex; gap:10px; }
    .loading-overlay { display:none; position:fixed; inset:0; background:rgba(46,16,101,.88); z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:18px; }
    .loading-overlay.show { display:flex; }
    .loading-spinner { width:58px; height:58px; border:4px solid rgba(255,255,255,.2); border-top-color:#a78bfa; border-radius:50%; animation:spin 1s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .loading-text { color:#fff; font-size:15px; font-weight:600; }
    @media(max-width:700px){ .form-row.cols-2 { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')

@if(!$apiConfiguree)
<div class="api-warning">
    <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;"></i>
    <div><strong>Clé API Groq non configurée.</strong> La génération ne fonctionnera pas tant que <code>GROQ_API_KEY</code> n'est pas définie dans le fichier <code>.env</code>. Contactez l'administrateur.</div>
</div>
@endif

@if($matieres->isEmpty())
<div class="api-warning" style="background:#fffbeb; border-color:#fde68a; color:#92400e;">
    <i class="fa-solid fa-circle-info" style="margin-top:2px;"></i>
    <div>Aucune matière ne vous est assignée. Vous pouvez tout de même générer un examen « libre », mais vous ne pourrez l'envoyer à une classe que si une classe lui est associée.</div>
</div>
@endif

<div class="layout-grid">
    <div class="form-card">
        <div class="form-header">
            <div class="form-header-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <div><h5>Générateur d'examen IA</h5><p>Analyse votre cours et crée un examen complet en quelques secondes</p></div>
        </div>

        <form id="examenForm">
            @csrf
            <div class="form-body">
                <div class="section-label">Contexte pédagogique</div>
                <div class="form-row cols-2">
                    <div class="field-group">
                        <label>Classe</label>
                        <select id="classeSelect" name="classe_id" onchange="onClasseChange()">
                            <option value="">— Aucune classe spécifique —</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" data-niveau="{{ $c->niveau }}">{{ $c->nom }} ({{ $c->niveau }})</option>
                            @endforeach
                        </select>
                        <div class="field-hint">Obligatoire pour pouvoir envoyer l'examen aux élèves.</div>
                    </div>
                    <div class="field-group">
                        <label>Matière</label>
                        <select id="matiereSelect" name="matiere_id">
                            <option value="">— Choisir une classe d'abord —</option>
                        </select>
                    </div>
                </div>

                <div class="field-group" style="margin-bottom:18px;">
                    <label>Niveau / Description (libre)</label>
                    <input type="text" name="niveau" id="niveauInput" placeholder="ex : 9ème année, Collège">
                </div>

                <hr class="section-divider">

                <div class="section-label">Langue de l'examen</div>
                <div class="pill-row" style="margin-bottom:22px;">
                    <div class="pill active" data-lang="fr" onclick="selectLang(this)"><span class="flag">🇫🇷</span>Français</div>
                    <div class="pill" data-lang="ar" onclick="selectLang(this)"><span class="flag">🇹🇳</span>العربية</div>
                    <div class="pill" data-lang="en" onclick="selectLang(this)"><span class="flag">🇬🇧</span>English</div>
                </div>
                <input type="hidden" name="langue" id="langueInput" value="fr">

                <div class="section-label">Difficulté</div>
                <div class="pill-row" style="margin-bottom:22px;">
                    <div class="pill" data-diff="facile" onclick="selectDiff(this)">😊 Facile</div>
                    <div class="pill active" data-diff="moyen" onclick="selectDiff(this)">🎯 Moyen</div>
                    <div class="pill" data-diff="difficile" onclick="selectDiff(this)">🔥 Difficile</div>
                </div>
                <input type="hidden" name="difficulte" id="difficulteInput" value="moyen">

                <div class="section-label">Composition</div>
                <div class="form-row cols-2">
                    <div class="field-group"><label>Nombre de QCM</label><input type="number" name="nb_qcm" value="10" min="0" max="30"></div>
                    <div class="field-group"><label>Questions ouvertes</label><input type="number" name="nb_ouvertes" value="5" min="0" max="15"></div>
                </div>

                <hr class="section-divider">

                <div class="section-label">Contenu du cours</div>
                <div class="field-group">
                    <textarea name="contenu_cours" id="contenuTexte" placeholder="Collez ici le contenu du cours : définitions, théorèmes, exemples, exercices résolus… Plus c'est détaillé, meilleur sera l'examen."></textarea>
                    <div class="field-hint"><i class="fa-solid fa-circle-info"></i> Minimum 50 caractères.</div>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-generate" id="generateBtn" {{ !$apiConfiguree ? 'disabled' : '' }}>
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Générer l'examen avec l'IA
                </button>
            </div>
        </form>
    </div>

    <div class="tips-card">
        <div style="font-size:13px; font-weight:700; margin-bottom:14px;"><i class="fa-solid fa-lightbulb" style="color:var(--warning);"></i> Conseils</div>
        <div class="tip-item"><div class="tip-icon"><i class="fa-solid fa-1"></i></div><div class="tip-text"><strong>Soyez précis</strong> — incluez définitions, formules et exemples.</div></div>
        <div class="tip-item"><div class="tip-icon"><i class="fa-solid fa-2"></i></div><div class="tip-text"><strong>Indiquez le niveau</strong> — l'IA adapte le vocabulaire.</div></div>
        <div class="tip-item"><div class="tip-icon"><i class="fa-solid fa-3"></i></div><div class="tip-text"><strong>Vérifiez toujours</strong> le contenu avant de l'envoyer aux élèves.</div></div>
        <div class="tip-item"><div class="tip-icon"><i class="fa-solid fa-4"></i></div><div class="tip-text"><strong>~10-20 secondes</strong> selon la longueur du cours.</div></div>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text">Génération de l'examen en cours…</div>
    <div style="color:rgba(255,255,255,.6); font-size:12px;">L'IA analyse le cours et rédige les questions</div>
</div>

@endsection

@section('scripts')
<script>
@php $classesMatieresData = $classes->mapWithKeys(fn($c) => [$c->id => $matieres->where('classe_id', $c->id)->map(fn($m) => ['id'=>$m->id,'nom'=>$m->nom])->values()]); @endphp
const classesMatieres = @json($classesMatieresData);

function onClasseChange() {
    const classeId = document.getElementById('classeSelect').value;
    const sel = document.getElementById('matiereSelect');
    sel.innerHTML = '<option value="">— Aucune —</option>';
    if (classeId && classesMatieres[classeId]) {
        classesMatieres[classeId].forEach(m => sel.innerHTML += `<option value="${m.id}">${m.nom}</option>`);
        const opt = document.getElementById('classeSelect').selectedOptions[0];
        document.getElementById('niveauInput').value = opt.dataset.niveau || '';
    }
}
function selectLang(el){ document.querySelectorAll('[data-lang]').forEach(b=>b.classList.remove('active')); el.classList.add('active'); document.getElementById('langueInput').value=el.dataset.lang; }
function selectDiff(el){ document.querySelectorAll('[data-diff]').forEach(b=>b.classList.remove('active')); el.classList.add('active'); document.getElementById('difficulteInput').value=el.dataset.diff; }

document.getElementById('examenForm').addEventListener('submit', function(e){
    e.preventDefault();
    const texte = document.getElementById('contenuTexte').value;
    if (texte.trim().length < 50) { alert('Le contenu du cours est trop court (minimum 50 caractères).'); return; }

    const formData = new FormData(this);
    document.getElementById('loadingOverlay').classList.add('show');
    document.getElementById('generateBtn').disabled = true;

    fetch('{{ route("enseignant.examens.generer") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loadingOverlay').classList.remove('show');
        if (data.success) { window.location.href = data.redirect; }
        else { document.getElementById('generateBtn').disabled = false; alert('Erreur : ' + (data.message || 'Une erreur est survenue.')); }
    })
    .catch(err => {
        document.getElementById('loadingOverlay').classList.remove('show');
        document.getElementById('generateBtn').disabled = false;
        alert('Erreur de connexion : ' + err.message);
    });
});
</script>
@endsection
