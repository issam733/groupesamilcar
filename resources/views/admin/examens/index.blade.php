@extends('admin.layouts.app')

@section('title', 'Examens IA')
@section('page-title', 'Examens IA')
@section('page-subtitle', 'Historique des examens générés')

@section('extra-css')
<style>
    .stats-bar { display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:22px; }
    @media(max-width:900px) { .stats-bar { grid-template-columns:repeat(3,1fr); } }
    .stat-mini { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 18px; text-align:center; box-shadow:var(--shadow-sm); }
    .stat-mini-val { font-size:24px; font-weight:800; color:var(--text); line-height:1; }
    .stat-mini-lbl { font-size:11px; color:var(--text-muted); margin-top:4px; }
    .stat-mini.fr .stat-mini-val { color:#1a4fa0; }
    .stat-mini.ar .stat-mini-val { color:#0d9488; }
    .stat-mini.en .stat-mini-val { color:#7c5cbf; }

    .toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px 20px; margin-bottom:18px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; box-shadow:var(--shadow-sm); }
    .toolbar-search { position:relative; flex:1; min-width:200px; }
    .toolbar-search input { width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; }
    .toolbar-search input:focus { border-color:var(--primary); background:#fff; }
    .toolbar-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px; }
    .toolbar-select { padding:9px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; cursor:pointer; }

    .btn-am { display:inline-flex; align-items:center; gap:7px; padding:10px 18px; border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; transition:all .2s; border:none; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-gen { background:linear-gradient(135deg,#7c5cbf,#1a4fa0); color:#fff; }
    .btn-gen:hover { box-shadow:0 6px 20px rgba(124,92,191,.4); transform:translateY(-1px); color:#fff; }

    .examens-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; }
    .examen-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); transition:all .2s; }
    .examen-card:hover { transform:translateY(-3px); box-shadow:var(--shadow); }

    .examen-card-top { padding:16px 18px 12px; position:relative; }
    .examen-lang-badge { position:absolute; top:14px; right:14px; font-size:10px; font-weight:700; padding:3px 9px; border-radius:20px; text-transform:uppercase; }
    .examen-lang-badge.fr { background:#eef3ff; color:#1a4fa0; }
    .examen-lang-badge.ar { background:#ecfdf5; color:#0d9488; }
    .examen-lang-badge.en { background:#f3eeff; color:#7c5cbf; }

    .examen-titre { font-size:14.5px; font-weight:700; color:var(--text); margin-bottom:6px; padding-right:50px; line-height:1.3; }
    .examen-meta { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
    .meta-chip { font-size:10.5px; font-weight:600; padding:3px 9px; border-radius:20px; background:#f0f4fa; color:var(--text-muted); }
    .meta-chip.diff-facile    { background:#ecfdf5; color:#0d9488; }
    .meta-chip.diff-moyen     { background:#fffbeb; color:var(--warning); }
    .meta-chip.diff-difficile { background:#fef2f2; color:var(--danger); }

    .examen-card-footer { padding:12px 18px; border-top:1px solid var(--border); background:#fafbff; display:flex; align-items:center; justify-content:space-between; }
    .examen-date { font-size:11px; color:var(--text-muted); }
    .examen-actions { display:flex; gap:6px; }
    .btn-icon { width:30px; height:30px; border-radius:7px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--text-muted); cursor:pointer; transition:all .18s; text-decoration:none; }
    .btn-icon:hover.view { background:#eef3ff; color:var(--primary); border-color:var(--primary); }
    .btn-icon:hover.print { background:#ecfdf5; color:var(--success); border-color:var(--success); }
    .btn-icon:hover.delete { background:#fef2f2; color:var(--danger); border-color:var(--danger); }

    .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
    .empty-state i { font-size:48px; opacity:.3; display:block; margin-bottom:16px; }

    .pagination-wrapper { margin-top:20px; display:flex; justify-content:center; }
    .page-link { font-size:12px; border-color:var(--border); color:var(--text); border-radius:7px !important; }
    .page-link:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
    .page-item.active .page-link { background:var(--primary); border-color:var(--primary); }
</style>
@endsection

@section('content')

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-left:4px solid var(--success);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:#065f46;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-mini"><div class="stat-mini-val">{{ $stats['total'] }}</div><div class="stat-mini-lbl">Total générés</div></div>
    <div class="stat-mini"><div class="stat-mini-val">{{ $stats['ce_mois'] }}</div><div class="stat-mini-lbl">Ce mois</div></div>
    <div class="stat-mini fr"><div class="stat-mini-val">{{ $stats['fr'] }}</div><div class="stat-mini-lbl">🇫🇷 Français</div></div>
    <div class="stat-mini ar"><div class="stat-mini-val">{{ $stats['ar'] }}</div><div class="stat-mini-lbl">🇹🇳 Arabe</div></div>
    <div class="stat-mini en"><div class="stat-mini-val">{{ $stats['en'] }}</div><div class="stat-mini-lbl">🇬🇧 Anglais</div></div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <form method="GET" action="{{ route('admin.examens.index') }}" style="display:contents;" id="filterForm">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Rechercher un examen…" value="{{ request('search') }}" oninput="debounceSubmit()">
        </div>
        <select class="toolbar-select" name="classe_id" onchange="this.form.submit()">
            <option value="">Toutes les classes</option>
            @foreach($classes as $c)
            <option value="{{ $c->id }}" {{ request('classe_id')==$c->id?'selected':'' }}>{{ $c->nom }}</option>
            @endforeach
        </select>
        <select class="toolbar-select" name="langue" onchange="this.form.submit()">
            <option value="">Toutes les langues</option>
            <option value="fr" {{ request('langue')=='fr'?'selected':'' }}>Français</option>
            <option value="ar" {{ request('langue')=='ar'?'selected':'' }}>Arabe</option>
            <option value="en" {{ request('langue')=='en'?'selected':'' }}>Anglais</option>
        </select>
    </form>
    <div style="margin-left:auto;">
        <a href="{{ route('admin.examens.create') }}" class="btn-am btn-gen">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Générer un examen
        </a>
    </div>
</div>

<!-- Grid -->
@if($examens->count())
<div class="examens-grid">
    @foreach($examens as $examen)
    @php
        $langFlags = ['fr'=>'🇫🇷','ar'=>'🇹🇳','en'=>'🇬🇧'];
    @endphp
    <div class="examen-card">
        <div class="examen-card-top">
            <span class="examen-lang-badge {{ $examen->langue }}">{{ $langFlags[$examen->langue] ?? '' }} {{ $examen->langue }}</span>
            <div class="examen-titre">{{ $examen->titre }}</div>
            <div class="examen-meta">
                @if($examen->classe)
                    <span class="meta-chip"><i class="fa-solid fa-door-open"></i> {{ $examen->classe->nom }}</span>
                @endif
                @if($examen->matiere)
                    <span class="meta-chip">{{ $examen->matiere->nom }}</span>
                @endif
                <span class="meta-chip diff-{{ $examen->difficulte }}">{{ ucfirst($examen->difficulte) }}</span>
                <span class="meta-chip"><i class="fa-solid fa-list-check"></i> {{ $examen->nb_questions }} questions</span>
            </div>
        </div>
        <div class="examen-card-footer">
            <div class="examen-date"><i class="fa-regular fa-clock"></i> {{ $examen->created_at->diffForHumans() }}</div>
            <div class="examen-actions">
                <a href="{{ route('admin.examens.show', $examen) }}" class="btn-icon view" title="Voir"><i class="fa-solid fa-eye"></i></a>
                <a href="{{ route('admin.examens.pdf', $examen) }}" target="_blank" class="btn-icon print" title="Imprimer"><i class="fa-solid fa-print"></i></a>
                <button type="button" class="btn-icon delete" title="Supprimer"
                    onclick="confirmDelete({{ $examen->id }}, '{{ addslashes($examen->titre) }}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="pagination-wrapper">{{ $examens->links() }}</div>

@else
<div class="empty-state">
    <i class="fa-solid fa-wand-magic-sparkles"></i>
    <h4 style="color:var(--text);margin-bottom:8px;">Aucun examen généré encore</h4>
    <p>Créez votre premier examen avec l'intelligence artificielle.</p>
    <a href="{{ route('admin.examens.create') }}" class="btn-am btn-gen" style="margin-top:16px;display:inline-flex;">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Générer mon premier examen
    </a>
</div>
@endif

<!-- Modal suppression -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;">
        <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:22px;color:var(--danger);"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 style="font-size:18px;margin-bottom:8px;">Supprimer cet examen ?</h3>
        <p id="deleteModalText" style="font-size:14px;color:var(--text-muted);margin-bottom:24px;"></p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button onclick="closeDeleteModal()" style="padding:10px 24px;border-radius:9px;border:1.5px solid var(--border);background:var(--bg);font-size:13px;font-weight:600;cursor:pointer;">Annuler</button>
            <form id="deleteForm" method="POST">@csrf @method('DELETE')
                <button type="submit" style="padding:10px 24px;border-radius:9px;border:none;background:var(--danger);color:#fff;font-size:13px;font-weight:600;cursor:pointer;">Supprimer</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let st;
function debounceSubmit() { clearTimeout(st); st = setTimeout(()=>document.getElementById('filterForm').submit(),500); }
function confirmDelete(id, titre) {
    document.getElementById('deleteModalText').textContent = `L'examen "${titre}" sera définitivement supprimé.`;
    document.getElementById('deleteForm').action = `{{ url('admin/examens') }}/${id}`;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }
document.getElementById('deleteModal').addEventListener('click', function(e) { if(e.target===this) closeDeleteModal(); });
</script>
@endsection
