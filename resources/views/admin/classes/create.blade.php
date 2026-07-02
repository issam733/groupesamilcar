@extends('admin.layouts.app')

@section('title', 'Nouvelle classe')
@section('page-title', 'Nouvelle classe')
@section('page-subtitle', 'Créer une classe et ses matières')

@section('extra-css')
<style>
    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; max-width:900px; }
    .form-header { padding:18px 28px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; gap:10px; }
    .form-header-icon { width:32px; height:32px; background:var(--primary); color:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
    .form-header h5 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
    .form-body { padding:28px; }
    .section-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:16px; }
    .form-row { display:grid; gap:18px; margin-bottom:18px; }
    .form-row.cols-2 { grid-template-columns:1fr 1fr; }
    .form-row.cols-3 { grid-template-columns:1fr 1fr 1fr; }
    .form-row.cols-4 { grid-template-columns:1fr 1fr 1fr 1fr; }
    .field-group label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .field-group label .req { color:var(--danger); margin-left:2px; }
    .field-group input, .field-group select {
        width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px;
        font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; transition:all .2s;
    }
    .field-group input:focus, .field-group select:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }
    .field-error { font-size:11.5px; color:var(--danger); margin-top:5px; display:flex; align-items:center; gap:5px; }
    .section-divider { border:none; border-top:1px solid var(--border); margin:24px 0; }

    /* Matières builder */
    .matieres-builder { border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .matieres-builder-header { padding:14px 18px; background:#f7f9fd; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
    .matieres-builder-header h6 { font-size:13px; font-weight:700; color:var(--text); margin:0; }
    .matiere-row { display:grid; grid-template-columns:2fr 1fr 1fr 2fr 36px; gap:10px; align-items:end; padding:14px 18px; border-bottom:1px solid #f0f4fa; }
    .matiere-row:last-child { border-bottom:none; }
    .matiere-row.header-row { padding:10px 18px; background:#fafbff; border-bottom:1px solid var(--border); }
    .matiere-row.header-row span { font-size:10px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; }
    .matiere-row input, .matiere-row select {
        padding:8px 12px; border:1.5px solid var(--border); border-radius:8px;
        font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:#fff; outline:none; width:100%;
    }
    .matiere-row input:focus, .matiere-row select:focus { border-color:var(--primary); }
    .btn-remove-matiere { width:34px; height:34px; border-radius:8px; border:1px solid #fecaca; background:#fef2f2; color:var(--danger); display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; transition:all .2s; flex-shrink:0; }
    .btn-remove-matiere:hover { background:var(--danger); color:#fff; }
    .btn-add-matiere { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:9px; border:1.5px dashed var(--primary); background:#eef3ff; color:var(--primary); font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; font-family:'Inter',sans-serif; margin:14px 18px; }
    .btn-add-matiere:hover { background:var(--primary); color:#fff; border-style:solid; }

    /* Footer */
    .form-footer { padding:18px 28px; border-top:1px solid var(--border); background:#f7f9fd; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-am.primary:hover { box-shadow:0 6px 20px rgba(26,79,160,.35); transform:translateY(-1px); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
    .btn-am.secondary:hover { border-color:var(--primary); color:var(--primary); }

    @media(max-width:700px) { .form-row.cols-2,.form-row.cols-3,.form-row.cols-4 { grid-template-columns:1fr; } .matiere-row { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-header">
        <div class="form-header-icon"><i class="fa-solid fa-door-open"></i></div>
        <h5>Nouvelle classe</h5>
    </div>

    <form method="POST" action="{{ route('admin.classes.store') }}" id="classeForm">
        @csrf
        <div class="form-body">

            <!-- Informations de la classe -->
            <div class="section-label"><i class="fa-solid fa-door-open" style="color:var(--primary);margin-right:6px;"></i>Informations de la classe</div>

            <div class="form-row cols-3">
                <div class="field-group">
                    <label>Nom de la classe <span class="req">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom') }}" placeholder="9ème Base A" required>
                    @error('nom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Niveau <span class="req">*</span></label>
                    <select name="niveau" required onchange="updateAnneeScolaire()">
                        <option value="">— Sélectionner —</option>
                        @foreach(['Préparatoire','Primaire','Collège','Lycée'] as $n)
                        <option value="{{ $n }}" {{ old('niveau')==$n?'selected':'' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                    @error('niveau')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Effectif maximum</label>
                    <input type="number" name="effectif_max" value="{{ old('effectif_max', 35) }}" min="5" max="50">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="field-group">
                    <label>Enseignant responsable</label>
                    <select name="enseignant_id">
                        <option value="">— Aucun —</option>
                        @foreach($enseignants as $ens)
                        <option value="{{ $ens->id }}" {{ old('enseignant_id')==$ens->id?'selected':'' }}>
                            {{ $ens->prenom }} {{ $ens->nom }}{{ $ens->matiere ? ' ('.$ens->matiere.')' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label>Année scolaire</label>
                    <input type="text" name="annee_scolaire" value="{{ old('annee_scolaire', date('Y').'-'.(date('Y')+1)) }}" placeholder="2026-2027">
                </div>
            </div>

            <hr class="section-divider">

            <!-- Matières dynamiques -->
            <div class="section-label"><i class="fa-solid fa-book-open" style="color:var(--primary);margin-right:6px;"></i>Matières enseignées</div>
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Ajoutez les matières de cette classe. Vous pourrez les modifier à tout moment.</p>

            <div class="matieres-builder">
                <div class="matiere-row header-row">
                    <span>Matière</span>
                    <span>Coefficient</span>
                    <span>H/semaine</span>
                    <span>Enseignant</span>
                    <span></span>
                </div>
                <div id="matieresContainer">
                    <!-- Lignes générées dynamiquement -->
                </div>
                <button type="button" class="btn-add-matiere" onclick="addMatiere()">
                    <i class="fa-solid fa-plus"></i> Ajouter une matière
                </button>
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('admin.classes.index') }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button type="submit" class="btn-am primary"><i class="fa-solid fa-floppy-disk"></i> Créer la classe</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
@php $enseignantsData = $enseignants->map(fn($e) => ['id'=>$e->id,'nom'=>$e->prenom.' '.$e->nom,'matiere'=>$e->matiere]); @endphp
const enseignants = @json($enseignantsData);
const matieresSuggestions = ['Mathématiques','Français','Arabe','Anglais','Sciences','Physique-Chimie','Histoire-Géographie','Informatique','Éducation physique','Musique','Arts plastiques','Philosophie','Économie'];

let rowIndex = 0;

function addMatiere(nom='', coef=1, heures=2, ensId='') {
    const container = document.getElementById('matieresContainer');
    const idx = rowIndex++;

    // Options enseignants
    let opts = '<option value="">— Aucun —</option>';
    enseignants.forEach(e => {
        opts += `<option value="${e.id}" ${ensId==e.id?'selected':''}>${e.nom}${e.matiere?' ('+e.matiere+')':''}</option>`;
    });

    // Datalist suggestions
    const dlId = `dl_${idx}`;
    let dlOpts = matieresSuggestions.map(m => `<option value="${m}">`).join('');

    container.insertAdjacentHTML('beforeend', `
        <datalist id="${dlId}">${dlOpts}</datalist>
        <div class="matiere-row" id="row_${idx}">
            <input type="text" name="matieres[${idx}][nom]" value="${nom}"
                   placeholder="Mathématiques" list="${dlId}" required>
            <input type="number" name="matieres[${idx}][coefficient]" value="${coef}"
                   min="0.5" max="10" step="0.5">
            <input type="number" name="matieres[${idx}][heures_semaine]" value="${heures}"
                   min="1" max="10">
            <select name="matieres[${idx}][enseignant_id]">${opts}</select>
            <button type="button" class="btn-remove-matiere" onclick="removeMatiere('row_${idx}')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    `);
}

function removeMatiere(rowId) {
    document.getElementById(rowId)?.remove();
    // Supprimer aussi le datalist associé
    const idx = rowId.replace('row_', '');
    document.getElementById('dl_' + idx)?.remove();
}

// Pré-remplir avec matières par défaut selon niveau
function updateAnneeScolaire() {}

// Ajouter quelques matières par défaut au chargement
window.addEventListener('DOMContentLoaded', () => {
    @if(old('matieres'))
        @foreach(old('matieres', []) as $idx => $m)
        addMatiere('{{ $m['nom'] ?? '' }}', {{ $m['coefficient'] ?? 1 }}, {{ $m['heures_semaine'] ?? 2 }}, '{{ $m['enseignant_id'] ?? '' }}');
        @endforeach
    @else
        // Matières de démarrage suggérées
        addMatiere('Mathématiques', 4, 4);
        addMatiere('Français', 3, 4);
        addMatiere('Arabe', 3, 4);
        addMatiere('Anglais', 2, 3);
        addMatiere('Sciences', 2, 2);
    @endif
});
</script>
@endsection
