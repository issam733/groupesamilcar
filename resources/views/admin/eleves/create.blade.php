@extends('admin.layouts.app')

@section('title', 'Ajouter un élève')
@section('page-title', 'Ajouter un élève')
@section('page-subtitle', 'Remplir la fiche d\'inscription')

@section('extra-css')
<style>
    .form-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        max-width: 860px;
    }
    .form-section-header {
        padding: 18px 28px;
        border-bottom: 1px solid var(--border);
        background: #f7f9fd;
        display: flex; align-items: center; gap: 10px;
    }
    .form-section-header i {
        width: 32px; height: 32px;
        background: var(--primary); color: #fff;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
    }
    .form-section-header h5 { font-size: 15px; font-weight: 700; color: var(--text); margin: 0; }
    .form-body { padding: 28px; }
    .form-row  { display: grid; gap: 18px; margin-bottom: 18px; }
    .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
    .form-row.cols-1 { grid-template-columns: 1fr; }
    .field-group label {
        display: block; font-size: 12px; font-weight: 600;
        color: var(--text); margin-bottom: 7px; letter-spacing: .3px;
    }
    .field-group label .req { color: var(--danger); margin-left: 2px; }
    .field-group input,
    .field-group select,
    .field-group textarea {
        width: 100%; padding: 10px 14px;
        border: 1.5px solid var(--border); border-radius: 9px;
        font-size: 13.5px; font-family: 'Inter', sans-serif;
        color: var(--text); background: #fafbff; outline: none; transition: all .2s;
    }
    .field-group input:focus,
    .field-group select:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 3px rgba(26,79,160,.08); }
    .field-group input.is-invalid { border-color: var(--danger); }
    .field-error { font-size: 11.5px; color: var(--danger); margin-top: 5px; display: flex; align-items: center; gap: 5px; }
    .field-hint  { font-size: 11px; color: var(--text-muted); margin-top: 5px; }
    .section-divider { border: none; border-top: 1px solid var(--border); margin: 24px 0; }
    .photo-upload-area {
        border: 2px dashed var(--border); border-radius: 12px;
        padding: 24px; text-align: center; cursor: pointer;
        transition: all .2s; background: #fafbff; position: relative;
    }
    .photo-upload-area:hover { border-color: var(--primary); background: #eef3ff; }
    .photo-upload-area input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .photo-preview {
        width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
        border: 3px solid var(--primary); margin: 0 auto 10px; display: block;
    }
    .photo-upload-icon {
        width: 52px; height: 52px; background: var(--bg); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 10px; font-size: 22px; color: var(--text-muted);
    }
    .form-footer {
        padding: 18px 28px; border-top: 1px solid var(--border); background: #f7f9fd;
        display: flex; justify-content: space-between; align-items: center; gap: 12px;
    }
    .btn-am {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px;
        border-radius: 9px; font-size: 13.5px; font-weight: 600;
        cursor: pointer; border: none; font-family: 'Inter', sans-serif;
        transition: all .2s; text-decoration: none;
    }
    .btn-am.primary { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; }
    .btn-am.primary:hover { box-shadow: 0 6px 20px rgba(26,79,160,.35); transform:translateY(-1px); color:#fff; }
    .btn-am.secondary { background: var(--bg); color: var(--text); border: 1.5px solid var(--border); }
    .btn-am.secondary:hover { border-color:var(--primary); color:var(--primary); }
    @media (max-width: 700px) { .form-row.cols-2, .form-row.cols-3 { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-section-header">
        <i class="fa-solid fa-user-plus"></i>
        <h5>Nouvelle fiche élève</h5>
    </div>

    <form method="POST" action="{{ route('admin.eleves.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-body">

            <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;">
                <i class="fa-solid fa-circle-user" style="color:var(--primary);margin-right:6px;"></i>Informations personnelles
            </div>

            <div class="form-row cols-1">
                <div class="field-group">
                    <label>Matricule <span class="req">*</span></label>
                    <input type="text" name="matricule" value="{{ old('matricule') }}" placeholder="Matricule officiel de l'élève" required class="{{ $errors->has('matricule') ? 'is-invalid':'' }}">
                    @error('matricule')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="field-group">
                    <label>Nom <span class="req">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom') }}" placeholder="BEN ALI" required class="{{ $errors->has('nom') ? 'is-invalid':'' }}">
                    @error('nom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Prénom <span class="req">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Mohamed" required class="{{ $errors->has('prenom') ? 'is-invalid':'' }}">
                    @error('prenom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Sexe</label>
                    <select name="sexe">
                        <option value="">— Sélectionner —</option>
                        <option value="M" {{ old('sexe')=='M'?'selected':'' }}>Masculin</option>
                        <option value="F" {{ old('sexe')=='F'?'selected':'' }}>Féminin</option>
                    </select>
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="field-group">
                    <label>Date de naissance</label>
                    <input type="date" name="date_naissance" value="{{ old('date_naissance') }}">
                </div>
                <div class="field-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}" placeholder="+216 XX XXX XXX">
                </div>
                <div class="field-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="eleve@amilcar.tn" class="{{ $errors->has('email') ? 'is-invalid':'' }}">
                    @error('email')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row cols-1">
                <div class="field-group">
                    <label>Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse') }}" placeholder="Rue, Ville, Code postal">
                </div>
            </div>

            <hr class="section-divider">

            <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;">
                <i class="fa-solid fa-graduation-cap" style="color:var(--primary);margin-right:6px;"></i>Scolarité
            </div>

            <div class="form-row cols-2">
                <div class="field-group">
                    <label>Classe</label>
                    <select name="classe_id">
                        <option value="">— Sélectionner une classe —</option>
                        @foreach($classes->groupBy('niveau') as $niveau => $cls)
                            <optgroup label="{{ $niveau }}">
                                @foreach($cls as $cl)
                                <option value="{{ $cl->id }}" {{ old('classe_id')==$cl->id?'selected':'' }}>
                                    {{ $cl->nom }} ({{ $cl->effectif() }}/{{ $cl->effectif_max }})
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label>Parent / Tuteur responsable</label>
                    <select name="parent_id">
                        <option value="">— Sélectionner un parent —</option>
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id')==$parent->id?'selected':'' }}>
                            {{ $parent->prenom }} {{ $parent->nom }}{{ $parent->telephone ? ' — '.$parent->telephone : '' }}
                        </option>
                        @endforeach
                    </select>
                    <div class="field-hint">
                        <a href="{{ route('admin.parents.create') }}" target="_blank" style="color:var(--primary);text-decoration:none;font-weight:500;">
                            <i class="fa-solid fa-plus"></i> Créer un nouveau parent
                        </a>
                    </div>
                </div>
            </div>

            <hr class="section-divider">

            <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;">
                <i class="fa-solid fa-camera" style="color:var(--primary);margin-right:6px;"></i>Photo de l'élève
            </div>

            <div class="form-row cols-2">
                <div class="field-group">
                    <div class="photo-upload-area" id="photoArea">
                        <input type="file" name="photo" accept="image/*" id="photoInput" onchange="previewPhoto(this)">
                        <div id="photoPlaceholder">
                            <div class="photo-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div style="font-size:13px;color:var(--text-muted);">
                                <strong style="color:var(--primary);">Cliquez</strong> ou glissez une photo<br>
                                <span style="font-size:11px;">JPG, PNG · max 2 Mo</span>
                            </div>
                        </div>
                        <img id="photoPreviewImg" class="photo-preview" style="display:none;" alt="">
                    </div>
                    @error('photo')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div style="display:flex;align-items:center;">
                    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;font-size:13px;color:#92400e;">
                        <i class="fa-solid fa-lightbulb" style="color:var(--warning);margin-right:6px;"></i>
                        <strong>Conseil :</strong> Photo récente en format portrait sur fond neutre.
                        Elle sera utilisée sur la fiche, les bulletins et les attestations.
                    </div>
                </div>
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('admin.eleves.index') }}" class="btn-am secondary">
                <i class="fa-solid fa-arrow-left"></i> Retour à la liste
            </a>
            <div style="display:flex;gap:10px;">
                <button type="reset" class="btn-am secondary"><i class="fa-solid fa-rotate-left"></i> Réinitialiser</button>
                <button type="submit" class="btn-am primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer l'élève</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('photoPlaceholder').style.display = 'none';
            const img = document.getElementById('photoPreviewImg');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
const area = document.getElementById('photoArea');
area.addEventListener('dragover', e => { e.preventDefault(); area.style.borderColor='var(--primary)'; });
area.addEventListener('dragleave', () => area.style.borderColor='');
area.addEventListener('drop', e => {
    e.preventDefault(); area.style.borderColor='';
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        document.getElementById('photoInput').files = e.dataTransfer.files;
        previewPhoto({ files: e.dataTransfer.files });
    }
});
</script>
@endsection
