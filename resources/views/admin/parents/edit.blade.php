@extends('admin.layouts.app')

@section('title', 'Modifier — '.$parent->nom)
@section('page-title', 'Modifier un parent')
@section('page-subtitle', $parent->prenom.' '.$parent->nom)

@section('extra-css')
<style>
    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; max-width:780px; }
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
    .field-group input { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; transition:all .2s; }
    .field-group input:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }
    .field-group input.is-invalid { border-color:var(--danger); }
    .field-error { font-size:11.5px; color:var(--danger); margin-top:5px; display:flex; align-items:center; gap:5px; }
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
        <h5>Modifier la fiche parent</h5>
    </div>

    <form method="POST" action="{{ route('admin.parents.update', $parent) }}">
        @csrf @method('PUT')
        <div class="form-body">
            <div class="section-label"><i class="fa-solid fa-circle-user" style="color:var(--warning);margin-right:6px;"></i>Informations personnelles</div>
            <div class="form-row cols-2">
                <div class="field-group">
                    <label>Nom <span class="req">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom',$parent->nom) }}" required class="{{ $errors->has('nom')?'is-invalid':'' }}">
                    @error('nom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Prénom <span class="req">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom',$parent->prenom) }}" required class="{{ $errors->has('prenom')?'is-invalid':'' }}">
                    @error('prenom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row cols-3">
                <div class="field-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone',$parent->telephone) }}">
                </div>
                <div class="field-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email',$parent->email) }}" class="{{ $errors->has('email')?'is-invalid':'' }}">
                    @error('email')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Profession</label>
                    <input type="text" name="profession" value="{{ old('profession',$parent->profession) }}">
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('admin.parents.show', $parent) }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button type="submit" class="btn-am primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
        </div>
    </form>
</div>
@endsection
