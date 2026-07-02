@extends('admin.layouts.app')

@section('title', 'Notes')
@section('page-title', 'Gestion des notes')
@section('page-subtitle', 'Sélectionnez une classe et un trimestre')

@section('extra-css')
<style>
    .selector-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); padding:28px; max-width:600px; margin-bottom:24px; }
    .selector-row { display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:20px; }
    .field-group label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .field-group select { width:100%; padding:11px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:14px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; }
    .field-group select:focus { border-color:var(--primary); background:#fff; }
    .trim-toggle { display:flex; gap:8px; }
    .trim-btn { flex:1; padding:10px; border-radius:9px; border:1.5px solid var(--border); background:#fafbff; text-align:center; font-size:13px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all .2s; }
    .trim-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }

    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:11px 24px; border-radius:9px; font-size:14px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; width:100%; justify-content:center; }
    .btn-am.primary:hover { box-shadow:0 6px 20px rgba(26,79,160,.35); }
    .btn-am.primary:disabled { opacity:.5; cursor:not-allowed; }

    .quick-links { display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:600px; }
    .quick-link-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px; text-decoration:none; display:flex; align-items:center; gap:14px; transition:all .2s; box-shadow:var(--shadow-sm); }
    .quick-link-card:hover { transform:translateY(-2px); box-shadow:var(--shadow); border-color:var(--primary); }
    .quick-link-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .quick-link-icon.blue { background:#eef3ff; color:var(--primary); }
    .quick-link-icon.green { background:#ecfdf5; color:var(--success); }
    .quick-link-title { font-size:13.5px; font-weight:700; color:var(--text); }
    .quick-link-sub { font-size:11.5px; color:var(--text-muted); margin-top:2px; }
</style>
@endsection

@section('content')

<div class="selector-card">
    <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:18px;">
        <i class="fa-solid fa-star-half-stroke" style="color:var(--primary); margin-right:6px;"></i>
        Saisie des notes
    </div>

    <div class="selector-row">
        <div class="field-group">
            <label>Classe</label>
            <select id="classeSelect">
                <option value="">— Sélectionner une classe —</option>
                @foreach($classes->groupBy('niveau') as $niveau => $cls)
                <optgroup label="{{ $niveau }}">
                    @foreach($cls as $c)
                    <option value="{{ $c->id }}">{{ $c->nom }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
        </div>
        <div class="field-group">
            <label>Trimestre</label>
            <div class="trim-toggle">
                <div class="trim-btn {{ $trimestre==1?'active':'' }}" data-trim="1" onclick="selectTrim(this)">T1</div>
                <div class="trim-btn {{ $trimestre==2?'active':'' }}" data-trim="2" onclick="selectTrim(this)">T2</div>
                <div class="trim-btn {{ $trimestre==3?'active':'' }}" data-trim="3" onclick="selectTrim(this)">T3</div>
            </div>
        </div>
    </div>

    <button class="btn-am primary" id="goBtn" onclick="goToSaisie()" disabled>
        <i class="fa-solid fa-arrow-right"></i> Accéder à la saisie
    </button>
</div>

<div class="quick-links">
    <a href="{{ route('admin.bulletins.index') }}" class="quick-link-card">
        <div class="quick-link-icon blue"><i class="fa-solid fa-file-lines"></i></div>
        <div>
            <div class="quick-link-title">Bulletins</div>
            <div class="quick-link-sub">Générer et imprimer les bulletins</div>
        </div>
    </a>
</div>

@endsection

@section('scripts')
<script>
let selectedTrim = {{ $trimestre }};

function selectTrim(el) {
    document.querySelectorAll('.trim-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    selectedTrim = el.dataset.trim;
    updateGoBtn();
}

document.getElementById('classeSelect').addEventListener('change', updateGoBtn);

function updateGoBtn() {
    const classeId = document.getElementById('classeSelect').value;
    document.getElementById('goBtn').disabled = !classeId;
}

function goToSaisie() {
    const classeId = document.getElementById('classeSelect').value;
    if (!classeId) return;
    window.location.href = `{{ url('admin/notes') }}/${classeId}/${selectedTrim}`;
}
</script>
@endsection
