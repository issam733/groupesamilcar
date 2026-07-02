@extends('admin.layouts.app')

@section('title', 'Attestations')
@section('page-title', 'Attestations')
@section('page-subtitle', 'Historique des attestations générées')

@section('extra-css')
<style>
    .stats-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
    .stat-mini { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 18px; text-align:center; box-shadow:var(--shadow-sm); }
    .stat-mini-val { font-size:24px; font-weight:800; color:var(--text); line-height:1; }
    .stat-mini-lbl { font-size:11px; color:var(--text-muted); margin-top:4px; }

    .toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px 20px; margin-bottom:18px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; box-shadow:var(--shadow-sm); }
    .toolbar-search { position:relative; flex:1; min-width:200px; }
    .toolbar-search input { width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; }
    .toolbar-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px; }
    .toolbar-select { padding:9px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; cursor:pointer; }

    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; transition:all .2s; border:none; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-primary-am { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-primary-am:hover { box-shadow:0 6px 20px rgba(26,79,160,.3); transform:translateY(-1px); color:#fff; }

    .table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .att-table { width:100%; border-collapse:collapse; }
    .att-table thead tr { background:#f7f9fd; border-bottom:1px solid var(--border); }
    .att-table th { padding:12px 16px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; }
    .att-table td { padding:13px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #f0f4fa; vertical-align:middle; }
    .att-table tr:hover td { background:#fafbff; }

    .type-badge { font-size:10.5px; font-weight:700; padding:3px 10px; border-radius:20px; }
    .type-badge.inscription { background:#eef3ff; color:var(--primary); }
    .type-badge.presence    { background:#ecfdf5; color:var(--success); }
    .type-badge.reussite    { background:#f3eeff; color:#7c5cbf; }

    .lang-flag { font-size:14px; }
    .numero-code { font-family:monospace; font-size:11.5px; background:#f0f4fa; padding:3px 8px; border-radius:6px; }

    .action-btns { display:flex; gap:6px; }
    .action-btn { width:30px; height:30px; border-radius:7px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--text-muted); cursor:pointer; transition:all .18s; text-decoration:none; }
    .action-btn:hover.view   { background:#eef3ff; color:var(--primary); border-color:var(--primary); }
    .action-btn:hover.delete { background:#fef2f2; color:var(--danger); border-color:var(--danger); }

    .empty-state { padding:60px 20px; text-align:center; color:var(--text-muted); }
    .empty-state i { font-size:48px; margin-bottom:16px; opacity:.3; display:block; }
    .pagination-wrapper { padding:16px 20px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; }
    .page-info { font-size:12px; color:var(--text-muted); }
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

<div class="stats-bar">
    <div class="stat-mini"><div class="stat-mini-val">{{ $stats['total'] }}</div><div class="stat-mini-lbl">Total générées</div></div>
    <div class="stat-mini"><div class="stat-mini-val" style="color:var(--primary);">{{ $stats['inscription'] }}</div><div class="stat-mini-lbl">Inscription</div></div>
    <div class="stat-mini"><div class="stat-mini-val" style="color:var(--success);">{{ $stats['presence'] }}</div><div class="stat-mini-lbl">Présence</div></div>
    <div class="stat-mini"><div class="stat-mini-val" style="color:#7c5cbf;">{{ $stats['reussite'] }}</div><div class="stat-mini-lbl">Réussite</div></div>
</div>

<div class="toolbar">
    <form method="GET" action="{{ route('admin.attestations.index') }}" style="display:contents;" id="filterForm">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Rechercher par nom ou numéro…" value="{{ request('search') }}" oninput="debounceSubmit()">
        </div>
        <select class="toolbar-select" name="type" onchange="this.form.submit()">
            <option value="">Tous les types</option>
            <option value="inscription" {{ request('type')=='inscription'?'selected':'' }}>Inscription</option>
            <option value="presence" {{ request('type')=='presence'?'selected':'' }}>Présence</option>
            <option value="reussite" {{ request('type')=='reussite'?'selected':'' }}>Réussite</option>
        </select>
    </form>
    <div style="margin-left:auto;">
        <a href="{{ route('admin.attestations.create') }}" class="btn-am btn-primary-am">
            <i class="fa-solid fa-plus"></i> Générer une attestation
        </a>
    </div>
</div>

<div class="table-card">
    <table class="att-table">
        <thead>
            <tr>
                <th>N° unique</th>
                <th>Élève</th>
                <th>Classe</th>
                <th>Type</th>
                <th>Langue</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attestations as $att)
            <tr>
                <td><span class="numero-code">{{ $att->numero_unique }}</span></td>
                <td>{{ $att->eleve->prenom ?? '—' }} {{ $att->eleve->nom ?? '' }}</td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $att->eleve->classe->nom ?? '—' }}</td>
                <td><span class="type-badge {{ $att->type }}">{{ ucfirst($att->type) }}</span></td>
                <td><span class="lang-flag">{{ $att->langue == 'ar' ? '🇹🇳' : '🇫🇷' }}</span></td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $att->created_at->format('d/m/Y') }}</td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.attestations.pdf', $att) }}" target="_blank" class="action-btn view" title="Voir / Imprimer"><i class="fa-solid fa-print"></i></a>
                        <button type="button" class="action-btn delete" title="Supprimer" onclick="confirmDelete({{ $att->id }}, '{{ $att->numero_unique }}')"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7">
                <div class="empty-state">
                    <i class="fa-solid fa-file-certificate"></i>
                    <h4 style="color:var(--text);margin-bottom:8px;">Aucune attestation générée</h4>
                    <p>Commencez par générer une attestation pour un élève.</p>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($attestations->hasPages())
    <div class="pagination-wrapper">
        <div class="page-info">{{ $attestations->firstItem() }} – {{ $attestations->lastItem() }} sur {{ $attestations->total() }}</div>
        {{ $attestations->links() }}
    </div>
    @endif
</div>

<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;">
        <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:22px;color:var(--danger);"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 style="font-size:18px;margin-bottom:8px;">Supprimer cette attestation ?</h3>
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
function confirmDelete(id, num) {
    document.getElementById('deleteModalText').textContent = `L'attestation "${num}" sera définitivement supprimée.`;
    document.getElementById('deleteForm').action = `{{ url('admin/attestations') }}/${id}`;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }
document.getElementById('deleteModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeDeleteModal(); });
</script>
@endsection
