@extends('admin.layouts.app')

@section('title', 'Ajouter un parent')
@section('page-title', 'Ajouter un parent')
@section('page-subtitle', 'Nouvelle fiche parent')

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
    .section-divider { border:none; border-top:1px solid var(--border); margin:24px 0; }

    .toggle-section { background:#f7f9fd; border:1px solid var(--border); border-radius:12px; padding:18px 20px; }
    .toggle-header { display:flex; align-items:center; justify-content:space-between; cursor:pointer; }
    .toggle-header h6 { font-size:13px; font-weight:600; color:var(--text); margin:0; }
    .toggle-body { margin-top:16px; display:none; }
    .toggle-body.open { display:block; }
    .toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; transition:.3s; cursor:pointer; }
    .toggle-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
    input:checked + .toggle-slider { background:var(--success); }
    input:checked + .toggle-slider::before { transform:translateX(20px); }

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
        <div class="form-header-icon"><i class="fa-solid fa-people-roof"></i></div>
        <h5>Nouvelle fiche parent</h5>
    </div>

    <form method="POST" action="{{ route('admin.parents.store') }}">
        @csrf
        <div class="form-body">

            <div class="section-label"><i class="fa-solid fa-circle-user" style="color:var(--warning);margin-right:6px;"></i>Informations personnelles</div>
            <div class="form-row cols-2">
                <div class="field-group">
                    <label>Nom <span class="req">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom') }}" placeholder="BEN ALI" required class="{{ $errors->has('nom')?'is-invalid':'' }}">
                    @error('nom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Prénom <span class="req">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Karim" required class="{{ $errors->has('prenom')?'is-invalid':'' }}">
                    @error('prenom')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="field-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}" placeholder="+216 XX XXX XXX">
                </div>
                <div class="field-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="parent@email.com" class="{{ $errors->has('email')?'is-invalid':'' }}">
                    @error('email')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label>Profession</label>
                    <input type="text" name="profession" value="{{ old('profession') }}" placeholder="Ingénieur">
                </div>
            </div>

            <hr class="section-divider">

            <div class="toggle-section">
                <div class="toggle-header" onclick="toggleCompte()">
                    <h6><i class="fa-solid fa-key" style="color:var(--primary);margin-right:8px;"></i>Créer un compte de connexion pour ce parent</h6>
                    <label class="toggle-switch" onclick="event.stopPropagation()">
                        <input type="checkbox" name="creer_compte" id="toggleCompte" value="1" onchange="toggleCompteBody(this)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="toggle-body" id="compteBody">
                    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:12px;color:#92400e;margin-bottom:16px;">
                        <i class="fa-solid fa-lightbulb" style="color:var(--warning);margin-right:6px;"></i>
                        Le parent pourra suivre la scolarité de son/ses enfant(s) : notes, absences, attestations.
                    </div>
                    <div class="form-row cols-2">
                        <div class="field-group">
                            <label>Email de connexion</label>
                            <input type="email" name="email_connexion" value="{{ old('email_connexion') }}" placeholder="parent@amilcar.tn">
                        </div>
                        <div class="field-group">
                            <label>Mot de passe initial</label>
                            <input type="password" name="password" placeholder="Min. 8 caractères">
                        </div>
                    </div>
                    <div class="field-group" style="max-width:50%;">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" placeholder="Confirmer">
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:8px;">
                        <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
                        Mot de passe par défaut si vide : <strong>Amilcar2026!</strong>
                    </div>
                </div>
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('admin.parents.index') }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button type="submit" class="btn-am primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer le parent</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function toggleCompteBody(checkbox) { document.getElementById('compteBody').classList.toggle('open', checkbox.checked); }
function toggleCompte() { const cb=document.getElementById('toggleCompte'); cb.checked=!cb.checked; toggleCompteBody(cb); }
</script>
@endsection
