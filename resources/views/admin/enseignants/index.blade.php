@extends('admin.layouts.app')

@section('title', 'Enseignants')
@section('page-title', 'Enseignants')
@section('page-subtitle', 'Gestion du corps enseignant')

@section('extra-css')
<style>
    .stats-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
    .stat-mini { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:var(--shadow-sm); }
    .stat-mini-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .stat-mini-icon.green  { background:#ecfdf5; color:var(--success); }
    .stat-mini-icon.blue   { background:#eef3ff; color:var(--primary); }
    .stat-mini-icon.orange { background:#fffbeb; color:var(--warning); }
    .stat-mini-icon.purple { background:#f3eeff; color:#7c5cbf; }
    .stat-mini-val { font-size:24px; font-weight:800; color:var(--text); line-height:1; }
    .stat-mini-lbl { font-size:11px; color:var(--text-muted); margin-top:3px; }

    .toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px 20px; margin-bottom:18px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; box-shadow:var(--shadow-sm); }
    .toolbar-search { position:relative; flex:1; min-width:200px; }
    .toolbar-search input { width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; transition:all .2s; }
    .toolbar-search input:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.1); }
    .toolbar-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px; pointer-events:none; }
    .toolbar-select { padding:9px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; cursor:pointer; min-width:160px; }

    .table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .ens-table { width:100%; border-collapse:collapse; }
    .ens-table thead tr { background:#f7f9fd; border-bottom:1px solid var(--border); }
    .ens-table th { padding:12px 16px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; white-space:nowrap; }
    .ens-table td { padding:13px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #f0f4fa; vertical-align:middle; }
    .ens-table tr:last-child td { border-bottom:none; }
    .ens-table tr:hover td { background:#fafbff; }

    .ens-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .ens-avatar img { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid var(--border); }
    .ens-info { display:flex; align-items:center; gap:12px; }
    .ens-nom  { font-weight:600; }
    .ens-email { font-size:11px; color:var(--text-muted); margin-top:1px; }

    .badge-matiere { display:inline-block; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; background:#f3eeff; color:#7c5cbf; }
    .badge-compte  { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .badge-compte.oui { background:#ecfdf5; color:var(--success); }
    .badge-compte.non { background:#f1f5f9; color:var(--text-muted); }

    .nb-classes { display:inline-flex; align-items:center; gap:5px; font-size:12px; color:var(--text-muted); }
    .nb-classes strong { color:var(--primary); font-size:16px; font-weight:700; }

    .action-btns { display:flex; gap:6px; }
    .action-btn { width:30px; height:30px; border-radius:7px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--text-muted); cursor:pointer; transition:all .18s; text-decoration:none; }
    .action-btn:hover.view   { background:#eef3ff; color:var(--primary);  border-color:var(--primary); }
    .action-btn:hover.edit   { background:#fffbeb; color:var(--warning);  border-color:var(--warning); }
    .action-btn:hover.delete { background:#fef2f2; color:var(--danger);   border-color:var(--danger); }

    .btn-am { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; transition:all .2s; border:none; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-primary-am { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-primary-am:hover { box-shadow:0 6px 20px rgba(26,79,160,.3); transform:translateY(-1px); color:#fff; }
    .btn-secondary-am { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
    .btn-secondary-am:hover { border-color:var(--primary); color:var(--primary); }

    .empty-state { padding:60px 20px; text-align:center; color:var(--text-muted); }
    .empty-state i { font-size:48px; margin-bottom:16px; opacity:.3; display:block; }
    .pagination-wrapper { padding:16px 20px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .page-info { font-size:12px; color:var(--text-muted); }
    .page-link { font-size:12px; font-weight:500; border-color:var(--border); color:var(--text); border-radius:7px !important; }
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

@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--danger);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:#991b1b;display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
</div>
@endif

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-mini">
        <div class="stat-mini-icon green"><i class="fa-solid fa-chalkboard-user"></i></div>
        <div><div class="stat-mini-val">{{ $stats['total'] }}</div><div class="stat-mini-lbl">Enseignants actifs</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon blue"><i class="fa-solid fa-door-open"></i></div>
        <div><div class="stat-mini-val">{{ $stats['classes'] }}</div><div class="stat-mini-lbl">Classes actives</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon orange"><i class="fa-solid fa-circle-check"></i></div>
        <div><div class="stat-mini-val">{{ $stats['actifs'] }}</div><div class="stat-mini-lbl">Avec compte</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon purple"><i class="fa-solid fa-circle-xmark"></i></div>
        <div><div class="stat-mini-val">{{ $stats['inactifs'] }}</div><div class="stat-mini-lbl">Désactivés</div></div>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <form method="GET" action="{{ route('admin.enseignants.index') }}" style="display:contents;" id="filterForm">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Rechercher par nom, prénom, matière…"
                   value="{{ request('search') }}" oninput="debounceSubmit()">
        </div>
        <select class="toolbar-select" name="matiere" onchange="this.form.submit()">
            <option value="">Toutes les matières</option>
            @foreach($matieres as $m)
            <option value="{{ $m }}" {{ request('matiere')==$m?'selected':'' }}>{{ $m }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','matiere']))
        <a href="{{ route('admin.enseignants.index') }}" class="btn-am btn-secondary-am">
            <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
    </form>
    <div style="margin-left:auto;">
        <a href="{{ route('admin.enseignants.create') }}" class="btn-am btn-primary-am">
            <i class="fa-solid fa-plus"></i> Ajouter un enseignant
        </a>
    </div>
</div>

<!-- Table -->
<div class="table-card">
    <table class="ens-table">
        <thead>
            <tr>
                <th>Enseignant</th>
                <th>Matière principale</th>
                <th>Classes</th>
                <th>Contact</th>
                <th>Diplôme</th>
                <th>Compte</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($enseignants as $ens)
            <tr>
                <td>
                    <div class="ens-info">
                        @if($ens->photo)
                            <img src="{{ asset('storage/'.$ens->photo) }}" class="ens-avatar" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                        @else
                            <div class="ens-avatar">{{ strtoupper(substr($ens->prenom,0,1).substr($ens->nom,0,1)) }}</div>
                        @endif
                        <div>
                            <div class="ens-nom">{{ $ens->nom }} {{ $ens->prenom }}</div>
                            <div class="ens-email">{{ $ens->email ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($ens->matiere)
                        <span class="badge-matiere">{{ $ens->matiere }}</span>
                    @else
                        <span style="color:var(--text-muted);">—</span>
                    @endif
                </td>
                <td>
                    <div class="nb-classes">
                        <strong>{{ $ens->classes->count() }}</strong>
                        classe{{ $ens->classes->count() > 1 ? 's' : '' }}
                    </div>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">
                    {{ $ens->telephone ?? '—' }}
                </td>
                <td style="font-size:12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ $ens->diplome ?? '—' }}
                </td>
                <td>
                    <span class="badge-compte {{ $ens->user_id ? 'oui' : 'non' }}">
                        {{ $ens->user_id ? 'Actif' : 'Aucun' }}
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.enseignants.show', $ens) }}" class="action-btn view" title="Voir"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('admin.enseignants.edit', $ens) }}" class="action-btn edit" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="action-btn delete" title="Désactiver"
                                onclick="confirmDelete({{ $ens->id }}, '{{ $ens->prenom }} {{ $ens->nom }}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7">
                <div class="empty-state">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <h4>Aucun enseignant trouvé</h4>
                    <p>Commencez par ajouter un enseignant.</p>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($enseignants->hasPages())
    <div class="pagination-wrapper">
        <div class="page-info">{{ $enseignants->firstItem() }} – {{ $enseignants->lastItem() }} sur {{ $enseignants->total() }}</div>
        {{ $enseignants->links() }}
    </div>
    @endif
</div>

<!-- Modal suppression -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:22px;color:var(--danger);">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 style="font-size:18px;margin-bottom:8px;">Désactiver cet enseignant ?</h3>
        <p id="deleteModalText" style="font-size:14px;color:var(--text-muted);margin-bottom:24px;"></p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button onclick="closeDeleteModal()" style="padding:10px 24px;border-radius:9px;border:1.5px solid var(--border);background:var(--bg);font-size:13px;font-weight:600;cursor:pointer;">Annuler</button>
            <form id="deleteForm" method="POST">
                @csrf @method('DELETE')
                <button type="submit" style="padding:10px 24px;border-radius:9px;border:none;background:var(--danger);color:#fff;font-size:13px;font-weight:600;cursor:pointer;">
                    <i class="fa-solid fa-trash" style="margin-right:6px;"></i>Désactiver
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let searchTimer;
function debounceSubmit() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
}
function confirmDelete(id, nom) {
    document.getElementById('deleteModalText').textContent = `L'enseignant "${nom}" sera désactivé.`;
    document.getElementById('deleteForm').action = `{{ url('admin/enseignants') }}/${id}`;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }
document.getElementById('deleteModal').addEventListener('click', function(e) { if(e.target===this) closeDeleteModal(); });
</script>
@endsection
