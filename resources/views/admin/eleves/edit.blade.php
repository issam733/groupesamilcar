@extends('admin.layouts.app')

@section('title', 'Modifier — '.$eleve->prenom.' '.$eleve->nom)
@section('page-title', 'Modifier l\'élève')
@section('page-subtitle', $eleve->matricule.' · '.$eleve->prenom.' '.$eleve->nom)

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
    .form-section { padding: 26px 28px; border-bottom: 1px solid var(--border); }
    .form-section:last-child { border-bottom: none; }
    .section-title {
        font-size: 13px; font-weight: 700; color: var(--primary);
        text-transform: uppercase; letter-spacing: .8px;
        margin-bottom: 20px; display: flex; align-items: center; gap: 9px;
    }
    .section-title::after { content:''; flex:1; height:1px; background:var(--border); }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .form-grid.triple { grid-template-columns: 1fr 1fr 1fr; }
    .form-group label {
        display: block; font-size: 12px; font-weight: 600;
        color: var(--text); margin-bottom: 7px;
    }
    .form-group label .req { color: var(--danger); margin-left: 3px; }
    .form-control-am {
        width: 100%; padding: 10px 14px;
        border: 1.5px solid var(--border); border-radius: 9px;
        font-size: 13.5px; font-family: 'Inter', sans-serif;
        color: var(--text); background: #fafbff; outline: none; transition: all .2s;
    }
    .form-control-am:focus {
        border-color: var(--primary); background: #fff;
        box-shadow: 0 0 0 3px rgba(26,79,160,.1);
    }
    .form-control-am.is-invalid { border-color: var(--danger); }
    .invalid-feedback { font-size: 11px; color: var(--danger); margin-top: 5px; display: block; }
    .photo-current {
        width: 80px; height: 80px; border-radius: 50%;
        object-fit: cover; border: 3px solid var(--primary);
        display: block; margin: 0 auto 10px;
    }
    .photo-upload-zone {
        border: 2px dashed var(--border); border-radius: 12px;
        padding: 20px; text-align: center; cursor: pointer;
        transition: all .2s; position: relative; background: #fafbff;
    }
    .photo-upload-zone:hover { border-color: var(--primary); background: #f0f4ff; }
    .photo-upload-zone input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width:100%; height:100%;
    }
    .radio-group { display: flex; gap: 12px; }
    .radio-option {
        flex: 1; border: 1.5px solid var(--border); border-radius: 9px;
        padding: 10px 14px; display: flex; align-items: center; gap: 9px;
        cursor: pointer; transition: all .2s;
    }
    .radio-option:hover { border-color: var(--primary); background: #f0f4ff; }
    .radio-option.selected { border-color: var(--primary); background: #eef3ff; }
    .radio-option span { font-size: 13px; font-weight: 500; }
    .form-footer {
        padding: 20px 28px; background: #fafbff;
        border-top: 1px solid var(--border);
        display: flex; align-items: center; gap: 12px;
    }
    .btn-am {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 22px; border-radius: 9px;
        font-size: 13.5px; font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer; border: none; transition: all .2s; text-decoration: none;
    }
    .btn-primary-am { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color:#fff; }
    .btn-primary-am:hover { box-shadow: 0 6px 20px rgba(26,79,160,.3); transform:translateY(-1px); color:#fff; }
    .btn-light-am { background: var(--bg); color: var(--text); border: 1.5px solid var(--border); }
    .btn-light-am:hover { border-color: var(--primary); color: var(--primary); }
    @media (max-width: 640px) {
        .form-grid, .form-grid.triple { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')

<!-- Info bar -->
<div style="background:#eef3ff; border:1px solid #c7d9f8; border-radius:10px;
            padding:12px 18px; margin-bottom:20px; font-size:13px; color:var(--primary);
            display:flex; align-items:center; gap:10px; max-width:860px;">
    <i class="fa-solid fa-circle-info"></i>
    Modification de la fiche de
    <strong>{{ $eleve->prenom }} {{ $eleve->nom }}</strong>
    — Matricule : <code>{{ $eleve->matricule }}</code>
</div>

<div class="form-card">
    <form method="POST"
          action="{{ route('admin.eleves.update', $eleve) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- ── Identité ── -->
        <div class="form-section">
            <div class="section-title"><i class="fa-solid fa-id-card"></i> Identité de l'élève</div>
            <div class="form-grid">

                <!-- Photo -->
                <div class="form-group" style="grid-row: span 2;">
                    <label>Photo</label>
                    <div class="photo-upload-zone">
                        <input type="file" name="photo" accept="image/*" onchange="previewPhoto(this)">
                        @if($eleve->photo)
                            <img id="photoPreview"
                                 src="{{ asset('storage/'.$eleve->photo) }}"
                                 class="photo-current" alt="">
                        @else
                            <img id="photoPreview" class="photo-current" src="" alt=""
                                 style="display:none;">
                            <div id="photoIcon">
                                <i class="fa-solid fa-camera" style="font-size:28px; color:var(--text-muted);"></i>
                            </div>
                        @endif
                        <p style="font-size:12px; color:var(--text-muted); margin-top:8px;">
                            Cliquer pour changer la photo
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Matricule <span class="req">*</span></label>
                    <input type="text" name="matricule"
                           class="form-control-am @error('matricule') is-invalid @enderror"
                           value="{{ old('matricule', $eleve->matricule) }}" required>
                    @error('matricule')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Nom <span class="req">*</span></label>
                    <input type="text" name="nom"
                           class="form-control-am @error('nom') is-invalid @enderror"
                           value="{{ old('nom', $eleve->nom) }}" required>
                    @error('nom')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Prénom <span class="req">*</span></label>
                    <input type="text" name="prenom"
                           class="form-control-am @error('prenom') is-invalid @enderror"
                           value="{{ old('prenom', $eleve->prenom) }}" required>
                    @error('prenom')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="date_naissance"
                           class="form-control-am"
                           value="{{ old('date_naissance', $eleve->date_naissance?->format('Y-m-d')) }}">
                </div>

                <div class="form-group">
                    <label>Sexe</label>
                    <div class="radio-group">
                        <label class="radio-option {{ old('sexe',$eleve->sexe) == 'M' ? 'selected' : '' }}"
                               onclick="selectSexe(this)">
                            <input type="radio" name="sexe" value="M"
                                   {{ old('sexe',$eleve->sexe) == 'M' ? 'checked' : '' }}>
                            <i class="fa-solid fa-mars" style="color:var(--primary);"></i>
                            <span>Masculin</span>
                        </label>
                        <label class="radio-option {{ old('sexe',$eleve->sexe) == 'F' ? 'selected' : '' }}"
                               onclick="selectSexe(this)">
                            <input type="radio" name="sexe" value="F"
                                   {{ old('sexe',$eleve->sexe) == 'F' ? 'checked' : '' }}>
                            <i class="fa-solid fa-venus" style="color:#d63384;"></i>
                            <span>Féminin</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Scolarité ── -->
        <div class="form-section">
            <div class="section-title"><i class="fa-solid fa-graduation-cap"></i> Scolarité</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Classe</label>
                    <select name="classe_id" class="form-control-am">
                        <option value="">— Sélectionner —</option>
                        @foreach($classes->groupBy('niveau') as $niveau => $cls)
                            <optgroup label="{{ $niveau }}">
                                @foreach($cls as $cl)
                                <option value="{{ $cl->id }}"
                                        {{ old('classe_id', $eleve->classe_id) == $cl->id ? 'selected' : '' }}>
                                    {{ $cl->nom }} ({{ $cl->effectif() }}/{{ $cl->effectif_max }})
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Parent responsable</label>
                    <select name="parent_id" class="form-control-am">
                        <option value="">— Sélectionner —</option>
                        @foreach($parents as $p)
                        <option value="{{ $p->id }}"
                                {{ old('parent_id', $eleve->parent_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->prenom }} {{ $p->nom }}
                            @if($p->telephone) — {{ $p->telephone }}@endif
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- ── Coordonnées ── -->
        <div class="form-section">
            <div class="section-title"><i class="fa-solid fa-address-book"></i> Coordonnées</div>
            <div class="form-grid triple">
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="telephone" class="form-control-am"
                           value="{{ old('telephone', $eleve->telephone) }}" placeholder="+216 XX XXX XXX">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email"
                           class="form-control-am @error('email') is-invalid @enderror"
                           value="{{ old('email', $eleve->email) }}">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" name="adresse" class="form-control-am"
                           value="{{ old('adresse', $eleve->adresse) }}">
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="submit" class="btn-am btn-primary-am">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
            </button>
            <a href="{{ route('admin.eleves.show', $eleve) }}" class="btn-am btn-light-am">
                <i class="fa-solid fa-xmark"></i> Annuler
            </a>
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
                const prev = document.getElementById('photoPreview');
                prev.src = e.target.result;
                prev.style.display = 'block';
                const icon = document.getElementById('photoIcon');
                if (icon) icon.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    function selectSexe(el) {
        document.querySelectorAll('.radio-option').forEach(r => r.classList.remove('selected'));
        el.classList.add('selected');
    }
</script>
@endsection
