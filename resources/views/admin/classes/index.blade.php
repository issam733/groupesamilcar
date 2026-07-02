@extends('admin.layouts.app')

@section('title', 'Classes')
@section('page-title', 'Classes')
@section('page-subtitle', 'Gestion des classes et matières')

@section('extra-css')
<style>
    .stats-bar { display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:22px; }
    @media(max-width:900px) { .stats-bar { grid-template-columns:repeat(3,1fr); } }
    .stat-mini { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:14px 16px; text-align:center; box-shadow:var(--shadow-sm); }
    .stat-mini-val { font-size:22px; font-weight:800; color:var(--primary); line-height:1; }
    .stat-mini-lbl { font-size:10px; color:var(--text-muted); margin-top:4px; text-transform:uppercase; letter-spacing:.5px; }

    .toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:14px 18px; margin-bottom:18px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; box-shadow:var(--shadow-sm); }
    .toolbar-search { position:relative; flex:1; min-width:180px; }
    .toolbar-search input { width:100%; padding:9px 14px 9px 36px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; transition:all .2s; }
    .toolbar-search input:focus { border-color:var(--primary); background:#fff; }
    .toolbar-search i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:12px; pointer-events:none; }
    .toolbar-select { padding:9px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; cursor:pointer; }
    .btn-am { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; transition:all .2s; border:none; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-primary-am { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-primary-am:hover { box-shadow:0 6px 20px rgba(26,79,160,.3); transform:translateY(-1px); color:#fff; }

    /* Classes grid */
    .niveau-section { margin-bottom:28px; }
    .niveau-header { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
    .niveau-badge { padding:5px 14px; border-radius:20px; font-size:12px; font-weight:700; }
    .niveau-badge.prep    { background:#ecfdf5; color:#0d9488; }
    .niveau-badge.primaire{ background:#fffbeb; color:var(--warning); }
    .niveau-badge.college { background:#eef3ff; color:var(--primary); }
    .niveau-badge.lycee   { background:#f3eeff; color:#7c5cbf; }
    .niveau-line { flex:1; height:1px; background:var(--border); }

    .classes-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
    .classe-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); transition:transform .2s,box-shadow .2s; }
    .classe-card:hover { transform:translateY(-3px); box-shadow:var(--shadow); }

    .classe-card-header { padding:16px 18px 12px; position:relative; }
    .classe-card-header::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; border-radius:0; }
    .classe-card.prep .classe-card-header::before    { background:#0d9488; }
    .classe-card.primaire .classe-card-header::before{ background:var(--warning); }
    .classe-card.college .classe-card-header::before { background:var(--primary); }
    .classe-card.lycee .classe-card-header::before   { background:#7c5cbf; }

    .classe-nom { font-size:16px; font-weight:700; color:var(--text); margin-bottom:4px; }
    .classe-meta { font-size:12px; color:var(--text-muted); }

    .classe-effectif-bar { padding:0 18px 12px; }
    .bar-label { display:flex; justify-content:space-between; font-size:11px; color:var(--text-muted); margin-bottom:5px; }
    .bar-track { background:#f0f4fa; border-radius:20px; height:6px; overflow:hidden; }
    .bar-fill { height:100%; border-radius:20px; transition:width .5s; }
    .bar-fill.ok      { background:var(--success); }
    .bar-fill.warning { background:var(--warning); }
    .bar-fill.full    { background:var(--danger); }

    .classe-matieres { padding:0 18px 14px; display:flex; flex-wrap:wrap; gap:5px; }
    .matiere-chip { font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; background:#f0f4fa; color:var(--text-muted); }

    .classe-card-footer { padding:12px 18px; border-top:1px solid var(--border); background:#fafbff; display:flex; align-items:center; justify-content:space-between; }
    .classe-enseignant { font-size:12px; color:var(--text-muted); display:flex; align-items:center; gap:6px; }
    .classe-actions { display:flex; gap:6px; }
    .btn-icon { width:28px; height:28px; border-radius:7px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; font-size:12px; color:var(--text-muted); cursor:pointer; transition:all .18s; text-decoration:none; }
    .btn-icon:hover.view { background:#eef3ff; color:var(--primary); border-color:var(--primary); }
    .btn-icon:hover.edit { background:#fffbeb; color:var(--warning); border-color:var(--warning); }
    .btn-icon:hover.delete { background:#fef2f2; color:var(--danger); border-color:var(--danger); }

    .empty-niveau { text-align:center; color:var(--text-muted); font-size:13px; padding:20px; background:var(--card); border-radius:12px; border:1px dashed var(--border); }
</style>
@endsection

@section('content')

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-left:4px solid var(--success);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:#065f46;display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--danger);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:#991b1b;display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
</div>
@endif

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-mini"><div class="stat-mini-val">{{ $stats['total'] }}</div><div class="stat-mini-lbl">Total classes</div></div>
    <div class="stat-mini"><div class="stat-mini-val" style="color:#0d9488;">{{ $stats['prep'] }}</div><div class="stat-mini-lbl">Préparatoire</div></div>
    <div class="stat-mini"><div class="stat-mini-val" style="color:var(--warning);">{{ $stats['primaire'] }}</div><div class="stat-mini-lbl">Primaire</div></div>
    <div class="stat-mini"><div class="stat-mini-val">{{ $stats['college'] }}</div><div class="stat-mini-lbl">Collège</div></div>
    <div class="stat-mini"><div class="stat-mini-val" style="color:#7c5cbf;">{{ $stats['lycee'] }}</div><div class="stat-mini-lbl">Lycée</div></div>
    <div class="stat-mini"><div class="stat-mini-val">{{ $stats['total_eleves'] }}</div><div class="stat-mini-lbl">Total élèves</div></div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <form method="GET" action="{{ route('admin.classes.index') }}" style="display:contents;" id="filterForm">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Rechercher une classe…" value="{{ request('search') }}" oninput="debounceSubmit()">
        </div>
        <select class="toolbar-select" name="niveau" onchange="this.form.submit()">
            <option value="">Tous les niveaux</option>
            @foreach(['Préparatoire','Primaire','Collège','Lycée'] as $n)
            <option value="{{ $n }}" {{ request('niveau')==$n?'selected':'' }}>{{ $n }}</option>
            @endforeach
        </select>
    </form>
    <div style="margin-left:auto;">
        <a href="{{ route('admin.classes.create') }}" class="btn-am btn-primary-am">
            <i class="fa-solid fa-plus"></i> Nouvelle classe
        </a>
    </div>
</div>

<!-- Classes par niveau -->
@php
    $niveaux = [
        'Préparatoire' => ['css'=>'prep',     'icon'=>'fa-seedling'],
        'Primaire'     => ['css'=>'primaire',  'icon'=>'fa-star'],
        'Collège'      => ['css'=>'college',   'icon'=>'fa-graduation-cap'],
        'Lycée'        => ['css'=>'lycee',     'icon'=>'fa-university'],
    ];
    $classesByNiveau = $classes->groupBy('niveau');
@endphp

@foreach($niveaux as $niveauNom => $meta)
    @php $classesNiveau = $classesByNiveau[$niveauNom] ?? collect(); @endphp
    @if(isset($classesNiveau) || !request('niveau') || request('niveau')===$niveauNom)
    <div class="niveau-section">
        <div class="niveau-header">
            <span class="niveau-badge {{ $meta['css'] }}">
                <i class="fa-solid {{ $meta['icon'] }}" style="margin-right:5px;"></i>
                {{ $niveauNom }}
                <span style="margin-left:6px;opacity:.7;">({{ $classesNiveau->count() ?? 0 }})</span>
            </span>
            <div class="niveau-line"></div>
        </div>

        @if(isset($classesNiveau) && $classesNiveau->count())
        <div class="classes-grid">
            @foreach($classesNiveau as $classe)
            @php
                $pct = $classe->effectif_max > 0 ? ($classe->effectif() / $classe->effectif_max) * 100 : 0;
                $barClass = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warning' : 'ok');
            @endphp
            <div class="classe-card {{ $meta['css'] }}">
                <div class="classe-card-header">
                    <div class="classe-nom">{{ $classe->nom }}</div>
                    <div class="classe-meta">{{ $classe->annee_scolaire }}</div>
                </div>

                <div class="classe-effectif-bar">
                    <div class="bar-label">
                        <span>{{ $classe->effectif() }} élèves</span>
                        <span>max {{ $classe->effectif_max }}</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill {{ $barClass }}" style="width:{{ min($pct,100) }}%"></div>
                    </div>
                </div>

                @if($classe->matieres->count())
                <div class="classe-matieres">
                    @foreach($classe->matieres->take(5) as $mat)
                        <span class="matiere-chip">{{ $mat->nom }}</span>
                    @endforeach
                    @if($classe->matieres->count() > 5)
                        <span class="matiere-chip">+{{ $classe->matieres->count()-5 }}</span>
                    @endif
                </div>
                @endif

                <div class="classe-card-footer">
                    <div class="classe-enseignant">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        {{ $classe->enseignant ? $classe->enseignant->nomComplet : 'Non assigné' }}
                    </div>
                    <div class="classe-actions">
                        <a href="{{ route('admin.classes.show', $classe) }}" class="btn-icon view" title="Voir"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('admin.classes.edit', $classe) }}" class="btn-icon edit" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="btn-icon delete" title="Désactiver"
                            onclick="confirmDelete({{ $classe->id }}, '{{ $classe->nom }}', {{ $classe->effectif() }})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-niveau">
            <i class="fa-solid fa-door-closed" style="margin-right:8px;opacity:.4;"></i>
            Aucune classe {{ strtolower($niveauNom) }} pour le moment.
            <a href="{{ route('admin.classes.create') }}" style="color:var(--primary);font-weight:600;margin-left:4px;">Créer une classe</a>
        </div>
        @endif
    </div>
    @endif
@endforeach

<!-- Modal suppression -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;">
        <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:22px;color:var(--danger);">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 style="font-size:18px;margin-bottom:8px;">Désactiver cette classe ?</h3>
        <p id="deleteModalText" style="font-size:14px;color:var(--text-muted);margin-bottom:24px;"></p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button onclick="closeDeleteModal()" style="padding:10px 24px;border-radius:9px;border:1.5px solid var(--border);background:var(--bg);font-size:13px;font-weight:600;cursor:pointer;">Annuler</button>
            <form id="deleteForm" method="POST">
                @csrf @method('DELETE')
                <button type="submit" style="padding:10px 24px;border-radius:9px;border:none;background:var(--danger);color:#fff;font-size:13px;font-weight:600;cursor:pointer;">Désactiver</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let searchTimer;
function debounceSubmit() { clearTimeout(searchTimer); searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500); }
function confirmDelete(id, nom, effectif) {
    let msg = `La classe "${nom}" sera désactivée.`;
    if (effectif > 0) msg = `⚠️ La classe "${nom}" contient ${effectif} élève(s). Transférez-les d'abord avant de la désactiver.`;
    document.getElementById('deleteModalText').textContent = msg;
    document.getElementById('deleteForm').action = `{{ url('admin/classes') }}/${id}`;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }
document.getElementById('deleteModal').addEventListener('click', function(e) { if(e.target===this) closeDeleteModal(); });
</script>
@endsection
