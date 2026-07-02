@extends('admin.layouts.app')

@section('title', 'Parents')
@section('page-title', 'Parents')
@section('page-subtitle', 'Gestion des parents d\'élèves')

@section('extra-css')
<style>
    .stats-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
    .stat-mini { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:var(--shadow-sm); }
    .stat-mini-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .stat-mini-icon.orange { background:#fffbeb; color:var(--warning); }
    .stat-mini-icon.green  { background:#ecfdf5; color:var(--success); }
    .stat-mini-icon.blue   { background:#eef3ff; color:var(--primary); }
    .stat-mini-icon.gray   { background:#f1f5f9; color:var(--text-muted); }
    .stat-mini-val { font-size:24px; font-weight:800; color:var(--text); line-height:1; }
    .stat-mini-lbl { font-size:11px; color:var(--text-muted); margin-top:3px; }

    .toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px 20px; margin-bottom:18px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; box-shadow:var(--shadow-sm); }
    .toolbar-search { position:relative; flex:1; min-width:200px; }
    .toolbar-search input { width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; transition:all .2s; }
    .toolbar-search input:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.1); }
    .toolbar-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px; pointer-events:none; }

    .table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .par-table { width:100%; border-collapse:collapse; }
    .par-table thead tr { background:#f7f9fd; border-bottom:1px solid var(--border); }
    .par-table th { padding:12px 16px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; }
    .par-table td { padding:13px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #f0f4fa; vertical-align:middle; }
    .par-table tr:last-child td { border-bottom:none; }
    .par-table tr:hover td { background:#fafbff; }

    .par-avatar { width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,var(--warning),#f59e0b); color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .par-info { display:flex; align-items:center; gap:11px; }
    .par-nom { font-weight:600; }
    .par-profession { font-size:11px; color:var(--text-muted); margin-top:1px; }

    .enfants-list { display:flex; flex-wrap:wrap; gap:5px; }
    .enfant-tag { font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; background:#eef3ff; color:var(--primary); white-space:nowrap; }

    .badge-compte { font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .badge-compte.oui { background:#ecfdf5; color:var(--success); }
    .badge-compte.non { background:#f1f5f9; color:var(--text-muted); }

    .action-btns { display:flex; gap:6px; }
    .action-btn { width:30px; height:30px; border-radius:7px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--text-muted); cursor:pointer; transition:all .18s; text-decoration:none; }
    .action-btn:hover.view   { background:#eef3ff; color:var(--primary); border-color:var(--primary); }
    .action-btn:hover.edit   { background:#fffbeb; color:var(--warning); border-color:var(--warning); }
    .action-btn:hover.delete { background:#fef2f2; color:var(--danger); border-color:var(--danger); }

    .btn-am { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; transition:all .2s; border:none; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-primary-am { background:linear-gradient(135deg,var(--warning),#f59e0b); color:#fff; }
    .btn-primary-am:hover { box-shadow:0 6px 20px rgba(234,179,8,.35); transform:translateY(-1px); color:#fff; }

    .empty-state { padding:60px 20px; text-align:center; color:var(--text-muted); }
    .empty-state i { font-size:48px; margin-bottom:16px; opacity:.3; display:block; }
    .pagination-wrapper { padding:16px 20px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .page-info { font-size:12px; color:var(--text-muted); }
    .page-link { font-size:12px; border-color:var(--border); color:var(--text); border-radius:7px !important; }
    .page-link:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
    .page-item.active .page-link { background:var(--primary); border-color:var(--primary); }

    @media(max-width:768px) { .stats-bar { grid-template-columns:1fr 1fr; } }
</style>
@endsection

@section('content')

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-left:4px solid var(--success);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:#065f46;display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-mini"><div class="stat-mini-icon orange"><i class="fa-solid fa-people-roof"></i></div><div><div class="stat-mini-val">{{ $stats['total'] }}</div><div class="stat-mini-lbl">Total parents</div></div></div>
    <div class="stat-mini"><div class="stat-mini-icon green"><i class="fa-solid fa-circle-check"></i></div><div><div class="stat-mini-val">{{ $stats['avec_compte'] }}</div><div class="stat-mini-lbl">Avec compte</div></div></div>
    <div class="stat-mini"><div class="stat-mini-icon gray"><i class="fa-solid fa-circle-xmark"></i></div><div><div class="stat-mini-val">{{ $stats['sans_compte'] }}</div><div class="stat-mini-lbl">Sans compte</div></div></div>
    <div class="stat-mini"><div class="stat-mini-icon blue"><i class="fa-solid fa-user-plus"></i></div><div><div class="stat-mini-val">{{ $stats['new_mois'] }}</div><div class="stat-mini-lbl">Nouveaux ce mois</div></div></div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <form method="GET" action="{{ route('admin.parents.index') }}" style="display:contents;" id="filterForm">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Rechercher par nom, prénom, email…" value="{{ request('search') }}" oninput="debounceSubmit()">
        </div>
        @if(request('search'))
        <a href="{{ route('admin.parents.index') }}" style="display:inline-flex;align-items:center;gap:7px;padding:9px 14px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;background:var(--bg);color:var(--text);border:1.5px solid var(--border);">
            <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
    </form>
    <div style="margin-left:auto;">
        <a href="{{ route('admin.parents.create') }}" class="btn-am btn-primary-am">
            <i class="fa-solid fa-plus"></i> Ajouter un parent
        </a>
    </div>
</div>

<!-- Table -->
<div class="table-card">
    <table class="par-table">
        <thead>
            <tr>
                <th>Parent</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Enfants</th>
                <th>Profession</th>
                <th>Compte</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parents as $parent)
            <tr>
                <td>
                    <div class="par-info">
                        <div class="par-avatar">{{ strtoupper(substr($parent->prenom,0,1).substr($parent->nom,0,1)) }}</div>
                        <div>
                            <div class="par-nom">{{ $parent->nom }} {{ $parent->prenom }}</div>
                            <div class="par-profession">{{ $parent->profession ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td style="font-size:13px;">{{ $parent->telephone ?? '—' }}</td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $parent->email ?? '—' }}</td>
                <td>
                    <div class="enfants-list">
                        @forelse($parent->eleves->take(3) as $eleve)
                            <span class="enfant-tag">{{ $eleve->prenom }} {{ $eleve->nom }}</span>
                        @empty
                            <span style="color:var(--text-muted);font-size:12px;">Aucun</span>
                        @endforelse
                        @if($parent->eleves->count() > 3)
                            <span class="enfant-tag">+{{ $parent->eleves->count()-3 }}</span>
                        @endif
                    </div>
                </td>
                <td style="font-size:13px;">{{ $parent->profession ?? '—' }}</td>
                <td><span class="badge-compte {{ $parent->user_id?'oui':'non' }}">{{ $parent->user_id?'Actif':'Aucun' }}</span></td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.parents.show', $parent) }}" class="action-btn view" title="Voir"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('admin.parents.edit', $parent) }}" class="action-btn edit" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="action-btn delete" onclick="confirmDelete({{ $parent->id }},'{{ $parent->prenom }} {{ $parent->nom }}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7">
                <div class="empty-state">
                    <i class="fa-solid fa-people-roof"></i>
                    <h4>Aucun parent trouvé</h4>
                    <p>Commencez par ajouter un parent.</p>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($parents->hasPages())
    <div class="pagination-wrapper">
        <div class="page-info">{{ $parents->firstItem() }} – {{ $parents->lastItem() }} sur {{ $parents->total() }}</div>
        {{ $parents->links() }}
    </div>
    @endif
</div>

<!-- Modal suppression -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;">
        <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:22px;color:var(--danger);"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 style="font-size:18px;margin-bottom:8px;">Désactiver ce parent ?</h3>
        <p id="deleteModalText" style="font-size:14px;color:var(--text-muted);margin-bottom:24px;"></p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button onclick="closeDeleteModal()" style="padding:10px 24px;border-radius:9px;border:1.5px solid var(--border);background:var(--bg);font-size:13px;font-weight:600;cursor:pointer;">Annuler</button>
            <form id="deleteForm" method="POST">@csrf @method('DELETE')
                <button type="submit" style="padding:10px 24px;border-radius:9px;border:none;background:var(--danger);color:#fff;font-size:13px;font-weight:600;cursor:pointer;">Désactiver</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let st;
function debounceSubmit() { clearTimeout(st); st = setTimeout(()=>document.getElementById('filterForm').submit(),500); }
function confirmDelete(id,nom) {
    document.getElementById('deleteModalText').textContent=`Le parent "${nom}" sera désactivé.`;
    document.getElementById('deleteForm').action=`{{ url('admin/parents') }}/${id}`;
    document.getElementById('deleteModal').style.display='flex';
}
function closeDeleteModal() { document.getElementById('deleteModal').style.display='none'; }
document.getElementById('deleteModal').addEventListener('click',function(e){if(e.target===this)closeDeleteModal();});
</script>
@endsection
