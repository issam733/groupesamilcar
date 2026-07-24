@extends('admin.layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')
@section('page-subtitle', 'Configuration de l\'établissement')

@section('extra-css')
<style>
    .param-grid { display:grid; gap:24px; max-width:900px; }
    .param-card { background:var(--card); border:1px solid var(--border); border-radius:16px; overflow:hidden; }
    .param-header { padding:18px 26px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; gap:12px; }
    .param-header-icon { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px; color:#fff; flex-shrink:0; }
    .param-header h5 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
    .param-header p { font-size:12px; color:var(--text-muted); margin:2px 0 0; }
    .param-body { padding:24px 26px; }

    .provider-choice { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:22px; }
    .provider-option { position:relative; border:1.5px solid var(--border); border-radius:12px; padding:16px 18px; cursor:pointer; transition:all .15s; }
    .provider-option:hover { border-color:var(--primary); }
    .provider-option input { position:absolute; opacity:0; }
    .provider-option .po-title { font-size:13.5px; font-weight:700; color:var(--text); display:flex; align-items:center; gap:8px; }
    .provider-option .po-sub { font-size:11.5px; color:var(--text-muted); margin-top:4px; }
    .provider-option.checked { border-color:var(--primary); background:#f0f5ff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }

    .field-group { margin-bottom:16px; }
    .field-group label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .field-group .hint { font-size:11px; color:var(--text-muted); margin-top:5px; }
    .key-input-wrap { position:relative; }
    .key-input-wrap input { width:100%; padding:10px 44px 10px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; }
    .key-input-wrap input:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }
    .key-toggle-eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:13px; }
    .current-key-badge { display:inline-flex; align-items:center; gap:6px; font-size:11.5px; color:var(--text-muted); background:var(--bg); padding:4px 10px; border-radius:20px; margin-bottom:8px; }

    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),#3b6fc9); color:#fff; }
    .btn-am.primary:hover { box-shadow:0 6px 20px rgba(26,79,160,.3); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
    .btn-am.secondary:hover { border-color:var(--primary); color:var(--primary); }
    .btn-am.danger-outline { background:#fff; color:var(--danger); border:1.5px solid #fecaca; }
    .btn-am.danger-outline:hover { background:#fef2f2; }
    .btn-am.success-outline { background:#fff; color:#0f766e; border:1.5px solid #99f6e4; }
    .btn-am.success-outline:hover { background:#f0fdfa; }
    .btn-am:disabled { opacity:.6; cursor:not-allowed; }

    .form-actions { display:flex; align-items:center; gap:12px; margin-top:6px; flex-wrap:wrap; }
    #testResult { font-size:12.5px; font-weight:600; display:none; align-items:center; gap:6px; }
    #testResult.ok { color:#0f766e; } #testResult.ko { color:var(--danger); }

    .admins-table { width:100%; border-collapse:collapse; margin-top:4px; }
    .admins-table th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); padding:8px 10px; border-bottom:1.5px solid var(--border); }
    .admins-table td { padding:11px 10px; border-bottom:1px solid var(--border); font-size:13px; color:var(--text); vertical-align:middle; }
    .admin-avatar { width:30px; height:30px; border-radius:50%; background:var(--primary); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; margin-right:9px; }
    .badge-status { font-size:10.5px; font-weight:700; padding:3px 10px; border-radius:20px; }
    .badge-status.actif { background:#ecfdf5; color:#0f766e; }
    .badge-status.inactif { background:#fef2f2; color:#dc2626; }

    .add-admin-toggle { display:inline-flex; align-items:center; gap:8px; }
    .add-admin-form { display:none; margin-top:20px; padding-top:20px; border-top:1px solid var(--border); }
    .add-admin-form.open { display:block; }
    .form-row { display:grid; gap:14px; margin-bottom:14px; }
    .form-row.cols-2 { grid-template-columns:1fr 1fr; }
    .form-row input { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; }
    .form-row input:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }
    .confirm-box { background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:14px 16px; margin-top:4px; }
    .confirm-box label { font-size:12px; font-weight:600; color:#92400e; margin-bottom:7px; display:block; }
    @media(max-width:700px) { .provider-choice, .form-row.cols-2 { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')

@if(session('success'))
    <div class="alert-flash" style="padding:13px 18px; border-radius:10px; font-size:13.5px; margin-bottom:18px; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; max-width:900px;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div style="padding:13px 18px; border-radius:10px; font-size:13.5px; margin-bottom:18px; background:#fef2f2; color:#991b1b; border:1px solid #fecaca; max-width:900px;">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
    </div>
@endif

<div class="param-grid">

    {{-- ═══════════ CARTE 1 : Intelligence artificielle ═══════════ --}}
    <div class="param-card">
        <div class="param-header">
            <div class="param-header-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7);"><i class="fa-solid fa-microchip"></i></div>
            <div>
                <h5>Intelligence artificielle</h5>
                <p>Choisissez le fournisseur utilisé pour générer examens, résumés et rapports</p>
            </div>
        </div>
        <div class="param-body">
            <form method="POST" action="{{ route('admin.parametres.ia.update') }}" id="iaForm">
                @csrf
                <div class="provider-choice">
                    <label class="provider-option {{ $iaSettings->provider === 'groq' ? 'checked' : '' }}" id="opt-groq">
                        <input type="radio" name="provider" value="groq" {{ $iaSettings->provider === 'groq' ? 'checked' : '' }} onchange="selectProvider('groq')">
                        <div class="po-title"><i class="fa-solid fa-bolt" style="color:#f59e0b;"></i> Groq</div>
                        <div class="po-sub">Fournisseur par défaut — rapide et économique</div>
                    </label>
                    <label class="provider-option {{ $iaSettings->provider === 'anthropic' ? 'checked' : '' }}" id="opt-anthropic">
                        <input type="radio" name="provider" value="anthropic" {{ $iaSettings->provider === 'anthropic' ? 'checked' : '' }} onchange="selectProvider('anthropic')">
                        <div class="po-title"><i class="fa-solid fa-a" style="color:#d97757;"></i> Anthropic</div>
                        <div class="po-sub">Claude — qualité de rédaction supérieure</div>
                    </label>
                </div>

                <div class="field-group">
                    <label>Clé API Groq</label>
                    @if($cles['groq'])
                        <span class="current-key-badge"><i class="fa-solid fa-key"></i> Clé actuelle : {{ $cles['groq'] }}</span>
                    @endif
                    <div class="key-input-wrap">
                        <input type="password" name="groq_api_key" id="groq_api_key" placeholder="{{ $cles['groq'] ? 'Laisser vide pour conserver la clé actuelle' : 'gsk_...' }}">
                        <button type="button" class="key-toggle-eye" onclick="toggleEye('groq_api_key', this)"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="hint">Laissez vide si vous ne souhaitez pas changer la clé existante.</div>
                </div>

                <div class="field-group">
                    <label>Clé API Anthropic</label>
                    @if($cles['anthropic'])
                        <span class="current-key-badge"><i class="fa-solid fa-key"></i> Clé actuelle : {{ $cles['anthropic'] }}</span>
                    @endif
                    <div class="key-input-wrap">
                        <input type="password" name="anthropic_api_key" id="anthropic_api_key" placeholder="{{ $cles['anthropic'] ? 'Laisser vide pour conserver la clé actuelle' : 'sk-ant-...' }}">
                        <button type="button" class="key-toggle-eye" onclick="toggleEye('anthropic_api_key', this)"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="hint">Laissez vide si vous ne souhaitez pas changer la clé existante.</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-am primary"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                    <button type="button" class="btn-am secondary" onclick="testerConnexion()" id="btnTester"><i class="fa-solid fa-plug"></i> Tester la connexion</button>
                    <span id="testResult"></span>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ CARTE 2 : Administrateurs ═══════════ --}}
    <div class="param-card">
        <div class="param-header">
            <div class="param-header-icon" style="background:linear-gradient(135deg,var(--primary),#3b6fc9);"><i class="fa-solid fa-user-shield"></i></div>
            <div>
                <h5>Administrateurs</h5>
                <p>Gérer les comptes ayant un accès administrateur complet</p>
            </div>
        </div>
        <div class="param-body">
            <table class="admins-table">
                <thead>
                    <tr><th>Administrateur</th><th>Email</th><th>Statut</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                    <tr>
                        <td>
                            <span class="admin-avatar">{{ $admin->initialesAvatar() }}</span>
                            {{ $admin->prenom }} {{ $admin->nom }}
                            @if($admin->id === auth()->id())<span style="font-size:10.5px; color:var(--text-muted);"> (vous)</span>@endif
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td><span class="badge-status {{ $admin->actif ? 'actif' : 'inactif' }}">{{ $admin->actif ? 'Actif' : 'Désactivé' }}</span></td>
                        <td>
                            @if($admin->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.parametres.admins.toggle', $admin) }}" onsubmit="return confirm('{{ $admin->actif ? 'Désactiver' : 'Réactiver' }} ce compte administrateur ?');">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-am {{ $admin->actif ? 'danger-outline' : 'success-outline' }}" style="padding:6px 14px; font-size:12px;">
                                    {{ $admin->actif ? 'Désactiver' : 'Réactiver' }}
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:18px;">
                <button type="button" class="btn-am secondary add-admin-toggle" onclick="toggleAddAdmin()">
                    <i class="fa-solid fa-user-plus"></i> Ajouter un administrateur
                </button>
            </div>

            <div class="add-admin-form" id="addAdminForm">
                <form method="POST" action="{{ route('admin.parametres.admins.store') }}">
                    @csrf
                    <div class="form-row cols-2">
                        <div class="field-group">
                            <label>Nom <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom') }}" required>
                        </div>
                        <div class="field-group">
                            <label>Prénom <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="prenom" value="{{ old('prenom') }}" required>
                        </div>
                    </div>
                    <div class="field-group">
                        <label>Email <span style="color:var(--danger);">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-row cols-2">
                        <div class="field-group">
                            <label>Mot de passe <span style="color:var(--danger);">*</span></label>
                            <input type="password" name="password" required minlength="8">
                        </div>
                        <div class="field-group">
                            <label>Confirmer le mot de passe <span style="color:var(--danger);">*</span></label>
                            <input type="password" name="password_confirmation" required minlength="8">
                        </div>
                    </div>

                    <div class="confirm-box">
                        <label><i class="fa-solid fa-lock"></i> Confirmez avec votre propre mot de passe pour valider la création</label>
                        <input type="password" name="password_admin_actuel" required placeholder="Votre mot de passe actuel">
                    </div>

                    <div class="form-actions" style="margin-top:16px;">
                        <button type="submit" class="btn-am primary"><i class="fa-solid fa-check"></i> Créer l'administrateur</button>
                        <button type="button" class="btn-am secondary" onclick="toggleAddAdmin()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function selectProvider(p) {
    document.getElementById('opt-groq').classList.toggle('checked', p === 'groq');
    document.getElementById('opt-anthropic').classList.toggle('checked', p === 'anthropic');
}
function toggleEye(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'fa-solid fa-eye-slash'; }
    else { input.type = 'password'; icon.className = 'fa-solid fa-eye'; }
}
function toggleAddAdmin() {
    document.getElementById('addAdminForm').classList.toggle('open');
}
async function testerConnexion() {
    const btn = document.getElementById('btnTester');
    const result = document.getElementById('testResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Test en cours…';
    result.style.display = 'none';

    try {
        const res = await fetch('{{ route('admin.parametres.ia.tester') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        });
        const data = await res.json();
        result.style.display = 'inline-flex';
        result.className = data.success ? 'ok' : 'ko';
        result.innerHTML = `<i class="fa-solid ${data.success ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${data.message}`;
    } catch (e) {
        result.style.display = 'inline-flex';
        result.className = 'ko';
        result.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Erreur réseau lors du test.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plug"></i> Tester la connexion';
    }
}
</script>
@endsection
