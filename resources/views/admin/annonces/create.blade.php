@extends('admin.layouts.app')

@section('title', 'Nouvelle annonce')
@section('page-title', 'Nouvelle annonce')
@section('page-subtitle', 'Publier une communication')

@section('extra-css')
<style>
    .form-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; max-width:680px; }
    .form-header { padding:18px 28px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; gap:10px; }
    .form-header-icon { width:32px; height:32px; background:var(--primary); color:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
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
    .cible-card.active { border-color:var(--primary); background:#eef3ff; }
    .cible-card.active i, .cible-card.active span { color:var(--primary); }

    .toggle-row { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:#f7f9fd; border-radius:10px; margin-bottom:14px; }
    .toggle-row-text strong { font-size:13px; color:var(--text); display:block; }
    .toggle-row-text span { font-size:11.5px; color:var(--text-muted); }
    .toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; transition:.3s; cursor:pointer; }
    .toggle-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
    input:checked + .toggle-slider { background:var(--success); }
    input:checked + .toggle-slider::before { transform:translateX(20px); }

    .email-info { background:#eef3ff; border-radius:10px; padding:12px 16px; font-size:12px; color:#1a4fa0; margin-bottom:20px; display:flex; gap:10px; align-items:flex-start; }

    .form-footer { padding:18px 28px; border-top:1px solid var(--border); background:#f7f9fd; display:flex; justify-content:space-between; align-items:center; gap:12px; }
    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-am.primary:hover { box-shadow:0 6px 20px rgba(26,79,160,.35); transform:translateY(-1px); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }

    @media(max-width:600px) { .cible-grid { grid-template-columns:1fr 1fr; } }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-header">
        <div class="form-header-icon"><i class="fa-solid fa-bullhorn"></i></div>
        <h5>Nouvelle annonce</h5>
    </div>

    <form method="POST" action="{{ route('admin.annonces.store') }}">
        @csrf
        <div class="form-body">

            <div class="field-group">
                <label>Titre <span class="req">*</span></label>
                <input type="text" name="titre" value="{{ old('titre') }}" placeholder="ex: Réunion parents-professeurs — Trimestre 2" required>
                @error('titre')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>

            <div class="field-group">
                <label>Contenu <span class="req">*</span></label>
                <textarea name="contenu" placeholder="Rédigez le message de votre annonce...">{{ old('contenu') }}</textarea>
                @error('contenu')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>

            <div class="section-label">Destinataires</div>
            <div class="cible-grid">
                <div class="cible-card active" data-cible="all" onclick="selectCible(this)">
                    <i class="fa-solid fa-users"></i><span>Tous</span>
                </div>
                <div class="cible-card" data-cible="enseignants" onclick="selectCible(this)">
                    <i class="fa-solid fa-chalkboard-user"></i><span>Enseignants</span>
                </div>
                <div class="cible-card" data-cible="parents" onclick="selectCible(this)">
                    <i class="fa-solid fa-people-roof"></i><span>Parents</span>
                </div>
                <div class="cible-card" data-cible="eleves" onclick="selectCible(this)">
                    <i class="fa-solid fa-user-graduate"></i><span>Élèves</span>
                </div>
            </div>
            <input type="hidden" name="cible" id="cibleInput" value="all">

            <div class="section-label">Options de publication</div>

            <div class="toggle-row">
                <div class="toggle-row-text">
                    <strong>Publier immédiatement</strong>
                    <span>L'annonce sera visible dès l'enregistrement</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="publie" value="1" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <div class="toggle-row-text">
                    <strong>Envoyer par email</strong>
                    <span>Notifie automatiquement les destinataires concernés</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="envoyer_email" value="1" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="email-info">
                <i class="fa-solid fa-circle-info" style="margin-top:1px;"></i>
                <div>Seuls les comptes actifs avec une adresse email valide recevront la notification. L'envoi se fait en arrière-plan et n'affecte pas la rapidité de cette page.</div>
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('admin.annonces.index') }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button type="submit" class="btn-am primary"><i class="fa-solid fa-paper-plane"></i> Publier l'annonce</button>
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
