@extends('admin.layouts.app')

@section('title', 'Générer un examen IA')
@section('page-title', 'Générer un examen IA')
@section('page-subtitle', 'Propulsé par l\'intelligence artificielle')

@section('extra-css')
<style>
    .layout-grid { display:grid; grid-template-columns:1fr 380px; gap:20px; align-items:start; }
    @media(max-width:1000px) { .layout-grid { grid-template-columns:1fr; } }

    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .form-header { padding:18px 28px; border-bottom:1px solid var(--border); background:linear-gradient(135deg,#7c5cbf,#1a4fa0); display:flex; align-items:center; gap:12px; }
    .form-header-icon { width:36px; height:36px; background:rgba(255,255,255,.2); color:#fff; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:16px; }
    .form-header h5 { font-size:15px; font-weight:700; color:#fff; margin:0; }
    .form-header p { font-size:11px; color:rgba(255,255,255,.8); margin-top:2px; }
    .form-body { padding:28px; }

    .section-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:14px; }
    .form-row { display:grid; gap:16px; margin-bottom:18px; }
    .form-row.cols-2 { grid-template-columns:1fr 1fr; }
    .form-row.cols-3 { grid-template-columns:1fr 1fr 1fr; }
    .field-group label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .field-group input, .field-group select, .field-group textarea {
        width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px;
        font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; transition:all .2s;
    }
    .field-group input:focus, .field-group select:focus, .field-group textarea:focus { border-color:#7c5cbf; background:#fff; box-shadow:0 0 0 3px rgba(124,92,191,.1); }
    .field-group textarea { resize:vertical; min-height:160px; font-family:'Inter',sans-serif; line-height:1.5; }
    .field-hint { font-size:11px; color:var(--text-muted); margin-top:5px; }
    .section-divider { border:none; border-top:1px solid var(--border); margin:22px 0; }

    /* Source toggle */
    .source-toggle { display:flex; gap:8px; margin-bottom:16px; }
    .source-btn { flex:1; padding:10px; border-radius:9px; border:1.5px solid var(--border); background:#fafbff; text-align:center; font-size:12.5px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all .2s; }
    .source-btn.active { background:#f3eeff; color:#7c5cbf; border-color:#7c5cbf; }
    .source-btn i { display:block; font-size:16px; margin-bottom:5px; }

    /* PDF upload */
    .pdf-upload-area { border:2px dashed var(--border); border-radius:12px; padding:24px; text-align:center; cursor:pointer; transition:all .2s; background:#fafbff; position:relative; }
    .pdf-upload-area:hover { border-color:#7c5cbf; background:#f3eeff; }
    .pdf-upload-area input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; }
    .pdf-filename { font-size:12px; color:#7c5cbf; font-weight:600; margin-top:8px; }

    /* Difficulty selector */
    .diff-selector { display:flex; gap:8px; }
    .diff-btn { flex:1; padding:10px; border-radius:9px; border:1.5px solid var(--border); background:#fafbff; text-align:center; font-size:12.5px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all .2s; }
    .diff-btn.active.facile    { background:#ecfdf5; color:#0d9488; border-color:#0d9488; }
    .diff-btn.active.moyen     { background:#fffbeb; color:var(--warning); border-color:var(--warning); }
    .diff-btn.active.difficile { background:#fef2f2; color:var(--danger); border-color:var(--danger); }

    /* Language selector */
    .lang-selector { display:flex; gap:8px; }
    .lang-btn { flex:1; padding:12px; border-radius:9px; border:1.5px solid var(--border); background:#fafbff; text-align:center; cursor:pointer; transition:all .2s; }
    .lang-btn.active { background:#eef3ff; border-color:var(--primary); }
    .lang-btn .flag { font-size:22px; display:block; margin-bottom:4px; }
    .lang-btn .name { font-size:11px; font-weight:600; color:var(--text-muted); }
    .lang-btn.active .name { color:var(--primary); }

    /* Footer */
    .form-footer { padding:18px 28px; border-top:1px solid var(--border); background:#f7f9fd; }
    .btn-generate { width:100%; padding:14px; background:linear-gradient(135deg,#7c5cbf,#1a4fa0); border:none; border-radius:11px; color:#fff; font-size:14.5px; font-weight:700; cursor:pointer; transition:all .3s; font-family:'Inter',sans-serif; display:flex; align-items:center; justify-content:center; gap:10px; }
    .btn-generate:hover { box-shadow:0 8px 25px rgba(124,92,191,.4); transform:translateY(-1px); }
    .btn-generate:disabled { opacity:.6; cursor:not-allowed; transform:none; }

    /* Sidebar tips */
    .tips-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:22px; box-shadow:var(--shadow-sm); }
    .tips-title { font-size:13px; font-weight:700; color:var(--text); margin-bottom:14px; display:flex; align-items:center; gap:8px; }
    .tip-item { display:flex; gap:10px; margin-bottom:14px; }
    .tip-item:last-child { margin-bottom:0; }
    .tip-icon { width:28px; height:28px; border-radius:8px; background:#f3eeff; color:#7c5cbf; display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; }
    .tip-text { font-size:12px; color:var(--text-muted); line-height:1.5; }
    .tip-text strong { color:var(--text); }

    .api-warning { background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:14px 16px; margin-bottom:20px; font-size:12.5px; color:#991b1b; display:flex; gap:10px; align-items:flex-start; }

    /* Loading overlay */
    .loading-overlay { display:none; position:fixed; inset:0; background:rgba(15,23,42,.85); z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:20px; }
    .loading-overlay.show { display:flex; }
    .loading-spinner { width:60px; height:60px; border:4px solid rgba(255,255,255,.2); border-top-color:#7c5cbf; border-radius:50%; animation:spin 1s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .loading-text { color:#fff; font-size:15px; font-weight:600; }
    .loading-sub { color:rgba(255,255,255,.6); font-size:12px; }

    @media(max-width:700px) { .form-row.cols-2,.form-row.cols-3 { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')

@if(!$apiConfiguree)
<div class="api-warning">
    <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;"></i>
    <div>
        <strong>Clé API Groq non configurée.</strong> La génération d'examens ne fonctionnera pas
        tant que la clé n'est pas définie dans le fichier <code>.env</code> (variable <code>GROQ_API_KEY</code>).
        Contactez l'administrateur système.
    </div>
</div>
@endif

<div class="layout-grid">

    <!-- Formulaire principal -->
    <div class="form-card">
        <div class="form-header">
            <div class="form-header-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <div>
                <h5>Générateur d'examen IA</h5>
                <p>Analyse votre cours et crée un examen complet en quelques secondes</p>
            </div>
        </div>

        <form id="examenForm">
            @csrf
            <div class="form-body">

                <!-- Classe / Matière -->
                <div class="section-label">Contexte pédagogique</div>
                <div class="form-row cols-2">
                    <div class="field-group">
                        <label>Classe (optionnel)</label>
                        <select id="classeSelect" name="classe_id" onchange="onClasseChange()">
                            <option value="">— Aucune classe spécifique —</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" data-niveau="{{ $c->niveau }}">{{ $c->nom }} ({{ $c->niveau }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label>Matière</label>
                        <select id="matiereSelect" name="matiere_id">
                            <option value="">— Sélectionner une classe d'abord —</option>
                        </select>
                        <input type="hidden" id="matiereNomInput" name="matiere_nom">
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="field-group">
                        <label>Niveau / Description (libre)</label>
                        <input type="text" name="niveau" id="niveauInput" placeholder="ex: 9ème année, Collège">
                    </div>
                    <div class="field-group">
                        <label>Enseignant (optionnel)</label>
                        <select name="enseignant_id">
                            <option value="">— Aucun —</option>
                            @foreach($enseignants as $e)
                            <option value="{{ $e->id }}">{{ $e->prenom }} {{ $e->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="section-divider">

                <!-- Langue -->
                <div class="section-label">Langue de l'examen</div>
                <div class="lang-selector" style="margin-bottom:22px;">
                    <div class="lang-btn active" data-lang="fr" onclick="selectLang(this)">
                        <span class="flag">🇫🇷</span><span class="name">Français</span>
                    </div>
                    <div class="lang-btn" data-lang="ar" onclick="selectLang(this)">
                        <span class="flag">🇹🇳</span><span class="name">العربية</span>
                    </div>
                    <div class="lang-btn" data-lang="en" onclick="selectLang(this)">
                        <span class="flag">🇬🇧</span><span class="name">English</span>
                    </div>
                </div>
                <input type="hidden" name="langue" id="langueInput" value="fr">

                <!-- Difficulté -->
                <div class="section-label">Niveau de difficulté</div>
                <div class="diff-selector" style="margin-bottom:22px;">
                    <div class="diff-btn" data-diff="facile" onclick="selectDiff(this)">😊 Facile</div>
                    <div class="diff-btn active moyen" data-diff="moyen" onclick="selectDiff(this)">🎯 Moyen</div>
                    <div class="diff-btn" data-diff="difficile" onclick="selectDiff(this)">🔥 Difficile</div>
                </div>
                <input type="hidden" name="difficulte" id="difficulteInput" value="moyen">

                <!-- Nombre de questions -->
                <div class="section-label">Composition de l'examen</div>
                <div class="form-row cols-2">
                    <div class="field-group">
                        <label>Nombre de QCM</label>
                        <input type="number" name="nb_qcm" value="10" min="0" max="30">
                    </div>
                    <div class="field-group">
                        <label>Nombre de questions ouvertes</label>
                        <input type="number" name="nb_ouvertes" value="5" min="0" max="15">
                    </div>
                </div>

                <hr class="section-divider">

                <!-- Source du contenu -->
                <div class="section-label">Source du cours</div>
                <div class="source-toggle">
                    <div class="source-btn active" data-source="texte" onclick="selectSource(this)">
                        <i class="fa-solid fa-keyboard"></i> Coller le texte
                    </div>
                    <div class="source-btn" data-source="pdf" onclick="selectSource(this)">
                        <i class="fa-solid fa-file-pdf"></i> Importer un PDF
                    </div>
                </div>

                <div id="sourceTexte">
                    <div class="field-group">
                        <textarea name="contenu_cours_texte" id="contenuTexte"
                            placeholder="Collez ici le contenu du cours (définitions, théorèmes, exemples, exercices résolus...). Plus le contenu est détaillé, meilleur sera l'examen généré."></textarea>
                        <div class="field-hint"><i class="fa-solid fa-circle-info"></i> Minimum 50 caractères recommandé pour un bon résultat.</div>
                    </div>
                </div>

                <div id="sourcePdf" style="display:none;">
                    <div class="pdf-upload-area" id="pdfArea">
                        <input type="file" name="fichier_pdf" id="pdfInput" accept=".pdf" onchange="onPdfSelect(this)">
                        <i class="fa-solid fa-file-pdf" style="font-size:32px; color:#7c5cbf; margin-bottom:10px; display:block;"></i>
                        <div style="font-size:13px; color:var(--text-muted);">
                            <strong style="color:#7c5cbf;">Cliquez</strong> ou glissez un fichier PDF<br>
                            <span style="font-size:11px;">Le contenu sera extrait automatiquement · max 10 Mo</span>
                        </div>
                        <div class="pdf-filename" id="pdfFilename"></div>
                    </div>
                    <div class="field-hint" style="margin-top:8px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:var(--warning);"></i>
                        L'extraction de texte depuis le PDF nécessite la bibliothèque <code>smalot/pdfparser</code> côté serveur (voir notes d'intégration).
                    </div>
                </div>

            </div>

            <div class="form-footer">
                <button type="submit" class="btn-generate" id="generateBtn" {{ !$apiConfiguree ? 'disabled' : '' }}>
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Générer l'examen avec l'IA
                </button>
            </div>
        </form>
    </div>

    <!-- Sidebar conseils -->
    <div class="tips-card">
        <div class="tips-title"><i class="fa-solid fa-lightbulb" style="color:var(--warning);"></i> Conseils pour un bon résultat</div>
        <div class="tip-item">
            <div class="tip-icon"><i class="fa-solid fa-1"></i></div>
            <div class="tip-text"><strong>Soyez précis</strong> — incluez définitions, formules et exemples concrets du cours.</div>
        </div>
        <div class="tip-item">
            <div class="tip-icon"><i class="fa-solid fa-2"></i></div>
            <div class="tip-text"><strong>Indiquez le niveau</strong> — l'IA adapte la complexité du vocabulaire et des questions.</div>
        </div>
        <div class="tip-item">
            <div class="tip-icon"><i class="fa-solid fa-3"></i></div>
            <div class="tip-text"><strong>Vérifiez toujours</strong> le contenu généré avant de le distribuer aux élèves.</div>
        </div>
        <div class="tip-item">
            <div class="tip-icon"><i class="fa-solid fa-4"></i></div>
            <div class="tip-text"><strong>Génération ~10-20 secondes</strong> selon la longueur du cours et le nombre de questions.</div>
        </div>
    </div>

</div>

<!-- Loading overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text">Génération de l'examen en cours…</div>
    <div class="loading-sub">L'IA analyse le cours et crée les questions</div>
</div>

@endsection

@section('scripts')
<script>
@php $classesMatieresData = $classes->mapWithKeys(fn($c) => [$c->id => $c->matieres->map(fn($m) => ['id'=>$m->id,'nom'=>$m->nom])]); @endphp
const classesMatieres = @json($classesMatieresData);

function onClasseChange() {
    const classeId = document.getElementById('classeSelect').value;
    const matiereSelect = document.getElementById('matiereSelect');
    const niveauInput = document.getElementById('niveauInput');

    matiereSelect.innerHTML = '<option value="">— Aucune —</option>';

    if (classeId && classesMatieres[classeId]) {
        classesMatieres[classeId].forEach(m => {
            matiereSelect.innerHTML += `<option value="${m.id}">${m.nom}</option>`;
        });
        const opt = document.getElementById('classeSelect').selectedOptions[0];
        niveauInput.value = opt.dataset.niveau || '';
    }
}

function selectLang(el) {
    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('langueInput').value = el.dataset.lang;
}

function selectDiff(el) {
    document.querySelectorAll('.diff-btn').forEach(b => b.classList.remove('active', 'facile', 'moyen', 'difficile'));
    el.classList.add('active', el.dataset.diff);
    document.getElementById('difficulteInput').value = el.dataset.diff;
}

function selectSource(el) {
    document.querySelectorAll('.source-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    const source = el.dataset.source;
    document.getElementById('sourceTexte').style.display = source === 'texte' ? 'block' : 'none';
    document.getElementById('sourcePdf').style.display = source === 'pdf' ? 'block' : 'none';
}

function onPdfSelect(input) {
    if (input.files && input.files[0]) {
        document.getElementById('pdfFilename').textContent = '📄 ' + input.files[0].name;
    }
}

document.getElementById('examenForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const sourceActive = document.querySelector('.source-btn.active').dataset.source;

    // Le contenu envoyé au backend dépend de la source choisie
    if (sourceActive === 'texte') {
        const texte = document.getElementById('contenuTexte').value;
        if (texte.length < 50) {
            alert('Le contenu du cours est trop court (minimum 50 caractères).');
            return;
        }
        formData.set('contenu_cours', texte);
    } else {
        const pdfInput = document.getElementById('pdfInput');
        if (!pdfInput.files.length) {
            alert('Veuillez sélectionner un fichier PDF.');
            return;
        }
        // NOTE: en environnement réel, le contenu_cours serait extrait du PDF côté serveur
        // avant l'appel à l'IA. Ici on envoie un texte indicatif + le fichier.
        formData.set('contenu_cours', 'Voir le fichier PDF joint pour le contenu complet du cours.');
    }

    document.getElementById('loadingOverlay').classList.add('show');
    document.getElementById('generateBtn').disabled = true;

    fetch('{{ route("admin.examens.generer") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loadingOverlay').classList.remove('show');
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            document.getElementById('generateBtn').disabled = false;
            alert('Erreur : ' + (data.message || 'Une erreur est survenue.'));
        }
    })
    .catch(err => {
        document.getElementById('loadingOverlay').classList.remove('show');
        document.getElementById('generateBtn').disabled = false;
        alert('Erreur de connexion : ' + err.message);
    });
});
</script>
@endsection
