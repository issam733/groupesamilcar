@extends('admin.layouts.app')

@section('title', 'Modifier l\'annonce')
@section('page-title', 'Modifier l\'annonce')
@section('page-subtitle', $annonce->titre)

@section('extra-css')
<style>
    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; max-width:680px; }
    .form-header { padding:18px 28px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; gap:10px; }
    .form-header-icon { width:32px; height:32px; background:var(--warning); color:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
    .form-header h5 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
    .form-body { padding:28px; }
    .section-label { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:14px; }
    .field-group { margin-bottom:20px; }
    .field-group label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .field-group label .req { color:var(--danger); margin-left:2px; }
    .field-group input, .field-group textarea {
        width:100%; padding:11px 14px; border:1.5px solid var(--border); border-radius:9px;
        font-size:14px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; transition:all .2s;
    }
    .field-group input:focus, .field-group textarea:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }
    .field-group textarea { min-height:160px; resize:vertical; line-height:1.5; }
    .field-error { font-size:11.5px; color:var(--danger); margin-top:5px; display:flex; align-items:center; gap:5px; }

    .cible-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:20px; }
    .cible-card { padding:14px 8px; border-radius:11px; border:1.5px solid var(--border); background:#fafbff; text-align:center; cursor:pointer; transition:all .2s; }
    .cible-card i { display:block; font-size:18px; margin-bottom:6px; color:var(--text-muted); }
    .cible-card span { font-size:11.5px; font-weight:600; color:var(--text-muted); }
    .cible-card.active { border-color:var(--warning); background:#fffbeb; }
    .cible-card.active i, .cible-card.active span { color:var(--warning); }

    .toggle-row { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:#f7f9fd; border-radius:10px; margin-bottom:14px; }
    .toggle-row-text strong { font-size:13px; color:var(--text); display:block; }
    .toggle-row-text span { font-size:11.5px; color:var(--text-muted); }
    .toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; transition:.3s; cursor:pointer; }
    .toggle-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
    input:checked + .toggle-slider { background:var(--success); }
    input:checked + .toggle-slider::before { transform:translateX(20px); }

    .form-footer { padding:18px 28px; border-top:1px solid var(--border); background:#f7f9fd; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--warning),#f59e0b); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
    @media(max-width:600px) { .cible-grid { grid-template-columns:1fr 1fr; } }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-header">
        <div class="form-header-icon"><i class="fa-solid fa-pen"></i></div>
        <h5>Modifier l'annonce</h5>
    </div>

    <form method="POST" action="{{ route('admin.annonces.update', $annonce) }}">
        @csrf @method('PUT')
        <div class="form-body">

            <div class="field-group">
                <label>Titre <span class="req">*</span></label>
                <input type="text" name="titre" value="{{ old('titre', $annonce->titre) }}" required>
                @error('titre')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>

            <div class="field-group">
                <label>Contenu <span class="req">*</span></label>
                <textarea name="contenu">{{ old('contenu', $annonce->contenu) }}</textarea>
                @error('contenu')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>

            <div class="section-label">Destinataires</div>
            <div class="cible-grid">
                <div class="cible-card {{ $annonce->cible=='all'?'active':'' }}" data-cible="all" onclick="selectCible(this)">
                    <i class="fa-solid fa-users"></i><span>Tous</span>
                </div>
                <div class="cible-card {{ $annonce->cible=='enseignants'?'active':'' }}" data-cible="enseignants" onclick="selectCible(this)">
                    <i class="fa-solid fa-chalkboard-user"></i><span>Enseignants</span>
                </div>
                <div class="cible-card {{ $annonce->cible=='parents'?'active':'' }}" data-cible="parents" onclick="selectCible(this)">
                    <i class="fa-solid fa-people-roof"></i><span>Parents</span>
                </div>
                <div class="cible-card {{ $annonce->cible=='eleves'?'active':'' }}" data-cible="eleves" onclick="selectCible(this)">
                    <i class="fa-solid fa-user-graduate"></i><span>Élèves</span>
                </div>
            </div>
            <input type="hidden" name="cible" id="cibleInput" value="{{ $annonce->cible }}">

            <div class="toggle-row">
                <div class="toggle-row-text">
                    <strong>Publiée</strong>
                    <span>Visible par les destinataires</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="publie" value="1" {{ $annonce->publie ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('admin.annonces.index') }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button type="submit" class="btn-am primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function selectCible(el) {
    document.querySelectorAll('.cible-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('cibleInput').value = el.dataset.cible;
}
</script>
@endsection
