@extends('admin.layouts.app')

@section('title', 'Générer une attestation')
@section('page-title', 'Générer une attestation')
@section('page-subtitle', 'Sélectionnez un élève et le type de document')

@section('extra-css')
<style>
    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; max-width:640px; }
    .form-header { padding:18px 28px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; gap:10px; }
    .form-header-icon { width:32px; height:32px; background:var(--primary); color:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
    .form-header h5 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
    .form-body { padding:28px; }
    .section-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:14px; }

    .eleve-search-box { position:relative; margin-bottom:20px; }
    .eleve-search-box input { width:100%; padding:11px 14px 11px 40px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; }
    .eleve-search-box input:focus { border-color:var(--primary); background:#fff; }
    .eleve-search-box i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); }

    .eleve-results { max-height:240px; overflow-y:auto; border:1px solid var(--border); border-radius:10px; margin-bottom:20px; }
    .eleve-result-item { display:flex; align-items:center; gap:12px; padding:11px 14px; cursor:pointer; transition:background .15s; border-bottom:1px solid #f0f4fa; }
    .eleve-result-item:last-child { border-bottom:none; }
    .eleve-result-item:hover { background:#eef3ff; }
    .eleve-result-item.selected { background:#eef3ff; border-left:3px solid var(--primary); }
    .eleve-avatar-sm { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .eleve-nom-result { font-size:13px; font-weight:600; color:var(--text); }
    .eleve-classe-result { font-size:11px; color:var(--text-muted); }

    .selected-eleve-box { display:none; background:#eef3ff; border:1.5px solid var(--primary); border-radius:10px; padding:14px 16px; margin-bottom:20px; align-items:center; gap:12px; }
    .selected-eleve-box.show { display:flex; }
    .selected-eleve-box .change-btn { margin-left:auto; font-size:11px; color:var(--primary); cursor:pointer; font-weight:600; }

    .type-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:20px; }
    .type-card { padding:16px 10px; border-radius:12px; border:1.5px solid var(--border); background:#fafbff; text-align:center; cursor:pointer; transition:all .2s; }
    .type-card i { display:block; font-size:22px; margin-bottom:8px; color:var(--text-muted); }
    .type-card span { font-size:12px; font-weight:600; color:var(--text-muted); }
    .type-card.active { border-color:var(--primary); background:#eef3ff; }
    .type-card.active i, .type-card.active span { color:var(--primary); }

    .lang-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:24px; }
    .lang-card { padding:14px; border-radius:12px; border:1.5px solid var(--border); background:#fafbff; text-align:center; cursor:pointer; transition:all .2s; }
    .lang-card .flag { font-size:24px; display:block; margin-bottom:6px; }
    .lang-card span.name { font-size:12px; font-weight:600; color:var(--text-muted); }
    .lang-card.active { border-color:var(--success); background:#ecfdf5; }
    .lang-card.active span.name { color:var(--success); }

    .form-footer { padding:18px 28px; border-top:1px solid var(--border); background:#f7f9fd; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-am.primary:disabled { opacity:.5; cursor:not-allowed; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-header">
        <div class="form-header-icon"><i class="fa-solid fa-file-certificate"></i></div>
        <h5>Générer une attestation</h5>
    </div>

    <form method="POST" action="{{ route('admin.attestations.generer') }}" id="attestationForm">
        @csrf
        <div class="form-body">

            <div class="section-label">1. Sélectionner l'élève</div>

            <div class="selected-eleve-box" id="selectedBox">
                <div class="eleve-avatar-sm" id="selectedAvatar"></div>
                <div>
                    <div class="eleve-nom-result" id="selectedNom"></div>
                    <div class="eleve-classe-result" id="selectedClasse"></div>
                </div>
                <span class="change-btn" onclick="resetSelection()">Changer</span>
            </div>

            <div id="searchSection">
                <div class="eleve-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="eleveSearchInput" placeholder="Rechercher un élève par nom…" oninput="filterEleves(this.value)">
                </div>
                <div class="eleve-results" id="eleveResults">
                    @foreach($eleves as $e)
                    <div class="eleve-result-item" data-nom="{{ strtolower($e->prenom.' '.$e->nom) }}"
                         onclick="selectEleve({{ $e->id }}, '{{ addslashes($e->prenom.' '.$e->nom) }}', '{{ addslashes($e->classe->nom ?? 'Aucune classe') }}', '{{ strtoupper(substr($e->prenom,0,1).substr($e->nom,0,1)) }}')">
                        <div class="eleve-avatar-sm">{{ strtoupper(substr($e->prenom,0,1).substr($e->nom,0,1)) }}</div>
                        <div>
                            <div class="eleve-nom-result">{{ $e->prenom }} {{ $e->nom }}</div>
                            <div class="eleve-classe-result">{{ $e->classe->nom ?? 'Aucune classe' }} · {{ $e->matricule }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <input type="hidden" name="eleve_id" id="eleveIdInput" required>

            <div class="section-label" style="margin-top:24px;">2. Type d'attestation</div>
            <div class="type-grid">
                <div class="type-card active" data-type="inscription" onclick="selectType(this)">
                    <i class="fa-solid fa-file-signature"></i><span>Inscription</span>
                </div>
                <div class="type-card" data-type="presence" onclick="selectType(this)">
                    <i class="fa-solid fa-calendar-check"></i><span>Présence</span>
                </div>
                <div class="type-card" data-type="reussite" onclick="selectType(this)">
                    <i class="fa-solid fa-trophy"></i><span>Réussite</span>
                </div>
            </div>
            <input type="hidden" name="type" id="typeInput" value="inscription">

            <div class="section-label">3. Langue du document</div>
            <div class="lang-grid">
                <div class="lang-card active" data-lang="fr" onclick="selectLang(this)">
                    <span class="flag">🇫🇷</span><span class="name">Français</span>
                </div>
                <div class="lang-card" data-lang="ar" onclick="selectLang(this)">
                    <span class="flag">🇹🇳</span><span class="name">العربية</span>
                </div>
            </div>
            <input type="hidden" name="langue" id="langueInput" value="fr">

        </div>

        <div class="form-footer">
            <a href="{{ route('admin.attestations.index') }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button type="submit" class="btn-am primary" id="submitBtn" disabled>
                <i class="fa-solid fa-file-circle-check"></i> Générer le document
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function filterEleves(term) {
    term = term.toLowerCase();
    document.querySelectorAll('.eleve-result-item').forEach(item => {
        item.style.display = item.dataset.nom.includes(term) ? 'flex' : 'none';
    });
}

function selectEleve(id, nom, classe, initiales) {
    document.getElementById('eleveIdInput').value = id;
    document.getElementById('selectedNom').textContent = nom;
    document.getElementById('selectedClasse').textContent = classe;
    document.getElementById('selectedAvatar').textContent = initiales;
    document.getElementById('selectedBox').classList.add('show');
    document.getElementById('searchSection').style.display = 'none';
    document.getElementById('submitBtn').disabled = false;
}

function resetSelection() {
    document.getElementById('eleveIdInput').value = '';
    document.getElementById('selectedBox').classList.remove('show');
    document.getElementById('searchSection').style.display = 'block';
    document.getElementById('submitBtn').disabled = true;
}

function selectType(el) {
    document.querySelectorAll('.type-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('typeInput').value = el.dataset.type;
}

function selectLang(el) {
    document.querySelectorAll('.lang-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('langueInput').value = el.dataset.lang;
}
</script>
@endsection
