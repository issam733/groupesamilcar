@extends('admin.layouts.app')

@section('title', 'Modifier — '.$classe->nom)
@section('page-title', 'Modifier la classe')
@section('page-subtitle', $classe->nom)

@section('extra-css')
<style>
    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; max-width:900px; }
    .form-header { padding:18px 28px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; gap:10px; }
    .form-header-icon { width:32px; height:32px; background:var(--warning); color:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
    .form-header h5 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
    .form-body { padding:28px; }
    .section-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:16px; }
    .form-row { display:grid; gap:18px; margin-bottom:18px; }
    .form-row.cols-2 { grid-template-columns:1fr 1fr; }
    .form-row.cols-3 { grid-template-columns:1fr 1fr 1fr; }
    .field-group label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .field-group input, .field-group select { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; }
    .field-group input:focus, .field-group select:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }
    .section-divider { border:none; border-top:1px solid var(--border); margin:24px 0; }
    .matieres-builder { border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .matieres-builder-header { padding:14px 18px; background:#f7f9fd; border-bottom:1px solid var(--border); }
    .matieres-builder-header h6 { font-size:13px; font-weight:700; color:var(--text); margin:0; }
    .matiere-row { display:grid; grid-template-columns:2fr 1fr 1fr 2fr 36px; gap:10px; align-items:end; padding:14px 18px; border-bottom:1px solid #f0f4fa; }
    .matiere-row:last-child { border-bottom:none; }
    .matiere-row.header-row { padding:10px 18px; background:#fafbff; border-bottom:1px solid var(--border); }
    .matiere-row.header-row span { font-size:10px; font-weight:700; color:var(--text-muted); text-transform:uppercase; }
    .matiere-row input, .matiere-row select { padding:8px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:#fff; outline:none; width:100%; }
    .btn-remove-matiere { width:34px; height:34px; border-radius:8px; border:1px solid #fecaca; background:#fef2f2; color:var(--danger); display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; flex-shrink:0; }
    .btn-remove-matiere:hover { background:var(--danger); color:#fff; }
    .btn-add-matiere { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:9px; border:1.5px dashed var(--primary); background:#eef3ff; color:var(--primary); font-size:13px; font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; margin:14px 18px; }
    .btn-add-matiere:hover { background:var(--primary); color:#fff; border-style:solid; }
    .form-footer { padding:18px 28px; border-top:1px solid var(--border); background:#f7f9fd; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--warning),#f59e0b); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
    @media(max-width:700px) { .form-row.cols-2,.form-row.cols-3 { grid-template-columns:1fr; } .matiere-row { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-header">
        <div class="form-header-icon"><i class="fa-solid fa-pen"></i></div>
        <h5>Modifier la classe</h5>
    </div>

    <form method="POST" action="{{ route('admin.classes.update', $classe) }}">
        @csrf @method('PUT')
        <div class="form-body">

            <div class="section-label">Informations de la classe</div>
            <div class="form-row cols-3">
                <div class="field-group">
                    <label>Nom de la classe</label>
                    <input type="text" name="nom" value="{{ old('nom',$classe->nom) }}" required>
                </div>
                <div class="field-group">
                    <label>Niveau</label>
                    <select name="niveau" required>
                        @foreach(['Préparatoire','Primaire','Collège','Lycée'] as $n)
                        <option value="{{ $n }}" {{ old('niveau',$classe->niveau)==$n?'selected':'' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label>Effectif maximum</label>
                    <input type="number" name="effectif_max" value="{{ old('effectif_max',$classe->effectif_max) }}" min="5" max="50">
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="field-group">
                    <label>Enseignant responsable</label>
                    <select name="enseignant_id">
                        <option value="">— Aucun —</option>
                        @foreach($enseignants as $ens)
                        <option value="{{ $ens->id }}" {{ old('enseignant_id',$classe->enseignant_id)==$ens->id?'selected':'' }}>
                            {{ $ens->prenom }} {{ $ens->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label>Année scolaire</label>
                    <input type="text" name="annee_scolaire" value="{{ old('annee_scolaire',$classe->annee_scolaire) }}">
                </div>
            </div>

            <hr class="section-divider">

            <div class="section-label">Matières enseignées</div>
            <div class="matieres-builder">
                <div class="matiere-row header-row">
                    <span>Matière</span><span>Coefficient</span><span>H/semaine</span><span>Enseignant</span><span></span>
                </div>
                <div id="matieresContainer"></div>
                <button type="button" class="btn-add-matiere" onclick="addMatiere()"><i class="fa-solid fa-plus"></i> Ajouter une matière</button>
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('admin.classes.show', $classe) }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button type="submit" class="btn-am primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
@php
    $enseignantsData = $enseignants->map(fn($e) => ['id'=>$e->id,'nom'=>$e->prenom.' '.$e->nom,'matiere'=>$e->matiere]);
    $matieresExistantesData = $classe->matieres->map(fn($m) => ['nom'=>$m->nom,'coefficient'=>$m->coefficient,'heures_semaine'=>$m->heures_semaine,'enseignant_id'=>$m->enseignant_id]);
@endphp
const enseignants = @json($enseignantsData);
const matieresExistantes = @json($matieresExistantesData);
let rowIndex = 0;

function addMatiere(nom='', coef=1, heures=2, ensId='') {
    const container = document.getElementById('matieresContainer');
    const idx = rowIndex++;
    let opts = '<option value="">— Aucun —</option>';
    enseignants.forEach(e => { opts += `<option value="${e.id}" ${ensId==e.id?'selected':''}>${e.nom}${e.matiere?' ('+e.matiere+')':''}</option>`; });

    container.insertAdjacentHTML('beforeend', `
        <div class="matiere-row" id="row_${idx}">
            <input type="text" name="matieres[${idx}][nom]" value="${nom}" placeholder="Mathématiques" required>
            <input type="number" name="matieres[${idx}][coefficient]" value="${coef}" min="0.5" max="10" step="0.5">
            <input type="number" name="matieres[${idx}][heures_semaine]" value="${heures}" min="1" max="10">
            <select name="matieres[${idx}][enseignant_id]">${opts}</select>
            <button type="button" class="btn-remove-matiere" onclick="document.getElementById('row_${idx}').remove()"><i class="fa-solid fa-xmark"></i></button>
        </div>
    `);
}

window.addEventListener('DOMContentLoaded', () => {
    if (matieresExistantes.length) {
        matieresExistantes.forEach(m => addMatiere(m.nom, m.coefficient, m.heures_semaine, m.enseignant_id));
    } else {
        addMatiere();
    }
});
</script>
@endsection
