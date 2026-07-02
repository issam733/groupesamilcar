@extends('admin.layouts.app')

@section('title', 'Modifier — '.$enseignant->nom)
@section('page-title', 'Modifier un enseignant')
@section('page-subtitle', $enseignant->prenom.' '.$enseignant->nom)

@section('extra-css')
<style>
    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; max-width:860px; }
    .form-header { padding:18px 28px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; gap:10px; }
    .form-header-icon { width:32px; height:32px; background:var(--warning); color:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
    .form-header h5 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
    .form-body { padding:28px; }
    .section-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:16px; }
    .form-row { display:grid; gap:18px; margin-bottom:18px; }
    .form-row.cols-2 { grid-template-columns:1fr 1fr; }
    .form-row.cols-3 { grid-template-columns:1fr 1fr 1fr; }
    .field-group label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .field-group label .req { color:var(--danger); margin-left:2px; }
    .field-group input, .field-group select {
        width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px;
        font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; transition:all .2s;
    }
    .field-group input:focus,.field-group select:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }
    .field-group input.is-invalid { border-color:var(--danger); }
    .field-error { font-size:11.5px; color:var(--danger); margin-top:5px; display:flex; align-items:center; gap:5px; }
    .section-divider { border:none; border-top:1px solid var(--border); margin:24px 0; }
    .photo-current { display:flex; align-items:center; gap:16px; background:var(--bg); border-radius:10px; padding:14px; margin-bottom:12px; }
    .photo-current img { width:60px; height:60px; border-radius:50%; object-fit:cover; border:3px solid var(--warning); }
    .photo-current-init { width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg,var(--success),#0d9488); color:#fff; font-size:20px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .photo-upload-area { border:2px dashed var(--border); border-radius:12px; padding:18px; text-align:center; cursor:pointer; transition:all .2s; background:#fafbff; position:relative; }
    .photo-upload-area:hover { border-color:var(--warning); background:#fffbeb; }
    .photo-upload-area input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
    .form-footer { padding:18px 28px; border-top:1px solid var(--border); background:#f7f9fd; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--warning),#f59e0b); color:#fff; }
    .btn-am.primary:hover { box-shadow:0 6px 20px rgba(234,179,8,.35); transform:translateY(-1px); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
    .btn-am.secondary:hover { border-color:var(--primary); color:var(--primary); }
    @media(max-width:700px) { .form-row.cols-2,.form-row.cols-3 { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-header">
        <div class="form-header-icon"><i class="fa-solid fa-pen"></i></div>
        <h5>Modifier la fiche enseignant</h5>
    </div>

    <form method="POST" action="{{ route('admin.enseignants.update', $enseignant) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="form-body">

            <div class="section-label"><i class="fa-solid fa-circle-user" style="color:var(--warning);margin-right:6px;"></i>Informations personnelles</div>
            <div class="form-row cols-3">
                <div class="field-group">
                    <label>Nom <span class="req">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom',$enseignant->nom) }}" required class="{{ $errors->has('nom')?'is-invalid':'' }}">
                    @error('nom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Prénom <span class="req">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom',$enseignant->prenom) }}" required class="{{ $errors->has('prenom')?'is-invalid':'' }}">
                    @error('prenom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Matière principale</label>
                    <input type="text" name="matiere" value="{{ old('matiere',$enseignant->matiere) }}" list="matieres-list">
                    <datalist id="matieres-list">
                        @foreach(['Mathématiques','Français','Arabe','Anglais','Sciences','Physique','Histoire-Géo','Informatique','Éducation physique'] as $m)
                        <option value="{{ $m }}">
                        @endforeach
                    </datalist>
                </div>
            </div>
            <div class="form-row cols-3">
                <div class="field-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone',$enseignant->telephone) }}" placeholder="+216 XX XXX XXX">
                </div>
                <div class="field-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email',$enseignant->email) }}" class="{{ $errors->has('email')?'is-invalid':'' }}">
                    @error('email')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Diplôme</label>
                    <input type="text" name="diplome" value="{{ old('diplome',$enseignant->diplome) }}">
                </div>
            </div>

            <hr class="section-divider">

            <div class="section-label"><i class="fa-solid fa-camera" style="color:var(--warning);margin-right:6px;"></i>Photo</div>
            <div style="max-width:420px;">
                <div class="photo-current">
                    @if($enseignant->photo)
                        <img src="{{ asset('storage/'.$enseignant->photo) }}" alt="">
                    @else
                        <div class="photo-current-init">{{ $enseignant->initiales }}</div>
                    @endif
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--text);">{{ $enseignant->photo ? 'Photo actuelle' : 'Aucune photo' }}</div>
                        <div style="font-size:12px;color:var(--text-muted);">Sélectionnez une image pour remplacer</div>
                    </div>
                </div>
                <div class="photo-upload-area">
                    <input type="file" name="photo" accept="image/*" id="photoInput" onchange="previewPhoto(this)">
                    <div id="photoPlaceholder">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size:22px;color:var(--text-muted);margin-bottom:8px;display:block;"></i>
                        <div style="font-size:13px;color:var(--text-muted);"><strong style="color:var(--warning);">Cliquez</strong> pour choisir · JPG, PNG · max 2 Mo</div>
                    </div>
                    <img id="photoPreviewImg" style="display:none;width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid var(--warning);margin:0 auto;" alt="">
                </div>
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('admin.enseignants.show', $enseignant) }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button type="submit" class="btn-am primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications</button>
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
            img.src = e.target.result; img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
