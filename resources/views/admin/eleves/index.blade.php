@extends('admin.layouts.app')

@section('title', 'Gestion des Élèves')
@section('page-title', 'Élèves')
@section('page-subtitle', 'Gestion des élèves inscrits')

@section('extra-css')
<style>
    /* ─── STATS BAR ─── */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 22px;
    }
    .stat-mini {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: var(--shadow-sm);
    }
    .stat-mini-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .stat-mini-icon.blue   { background:#eef3ff; color:var(--primary); }
    .stat-mini-icon.green  { background:#ecfdf5; color:var(--success); }
    .stat-mini-icon.pink   { background:#fff0f7; color:#d63384; }
    .stat-mini-icon.orange { background:#fffbeb; color:var(--warning); }
    .stat-mini-val  { font-size: 24px; font-weight: 800; color: var(--text); line-height:1; }
    .stat-mini-lbl  { font-size: 11px; color: var(--text-muted); margin-top:3px; }

    /* ─── TOOLBAR ─── */
    .toolbar {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 18px;
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: var(--shadow-sm);
    }
    .toolbar-search {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .toolbar-search input {
        width: 100%;
        padding: 9px 14px 9px 38px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-size: 13.5px;
        font-family: 'Inter', sans-serif;
        color: var(--text);
        background: var(--bg);
        outline: none;
        transition: all .2s;
    }
    .toolbar-search input:focus {
        border-color: var(--primary);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(26,79,160,.1);
    }
    .toolbar-search i {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 13px;
        pointer-events: none;
    }
    .toolbar-select {
        padding: 9px 12px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        color: var(--text);
        background: var(--bg);
        outline: none;
        cursor: pointer;
        transition: border-color .2s;
        min-width: 140px;
    }
    .toolbar-select:focus { border-color: var(--primary); }

    /* ─── TABLE ─── */
    .table-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    .eleves-table { width: 100%; border-collapse: collapse; }
    .eleves-table thead tr {
        background: #f7f9fd;
        border-bottom: 1px solid var(--border);
    }
    .eleves-table th {
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .5px;
        text-align: left;
        white-space: nowrap;
    }
    .eleves-table th.sortable { cursor: pointer; user-select: none; }
    .eleves-table th.sortable:hover { color: var(--primary); }
    .eleves-table td {
        padding: 12px 16px;
        font-size: 13.5px;
        color: var(--text);
        border-bottom: 1px solid #f0f4fa;
        vertical-align: middle;
    }
    .eleves-table tr:last-child td { border-bottom: none; }
    .eleves-table tr:hover td { background: #fafbff; }

    /* Avatar */
    .eleve-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border);
    }
    .eleve-avatar-initiales {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .eleve-info { display: flex; align-items: center; gap: 11px; }
    .eleve-nom  { font-weight: 600; color: var(--text); }
    .eleve-mat  { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

    /* Badges */
    .badge-classe {
        display: inline-block;
        font-size: 11px; font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
        background: #eef3ff; color: var(--primary);
    }
    .badge-niveau {
        display: inline-block;
        font-size: 10px; font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
    }
    .badge-niveau.prep    { background:#ecfdf5; color:#0d9488; }
    .badge-niveau.prim    { background:#fffbeb; color:var(--warning); }
    .badge-niveau.college { background:#eef3ff; color:var(--primary); }
    .badge-niveau.lycee   { background:#f3eeff; color:#7c5cbf; }

    .badge-sexe {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px;
        border-radius: 50%;
        font-size: 11px; font-weight: 700;
    }
    .badge-sexe.m { background:#e0eeff; color:var(--primary); }
    .badge-sexe.f { background:#ffe0f0; color:#d63384; }

    /* Actions */
    .action-btns { display: flex; gap: 6px; }
    .action-btn {
        width: 30px; height: 30px;
        border-radius: 7px;
        border: 1px solid var(--border);
        background: var(--bg);
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
        color: var(--text-muted);
        cursor: pointer;
        transition: all .18s;
        text-decoration: none;
    }
    .action-btn:hover.view    { background:#eef3ff; color:var(--primary);  border-color:var(--primary); }
    .action-btn:hover.edit    { background:#fffbeb; color:var(--warning);  border-color:var(--warning); }
    .action-btn:hover.delete  { background:#fef2f2; color:var(--danger);   border-color:var(--danger); }
    .action-btn:hover.print   { background:#ecfdf5; color:var(--success);  border-color:var(--success); }

    /* Empty state */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: var(--text-muted);
    }
    .empty-state i { font-size: 48px; margin-bottom: 16px; opacity:.3; }
    .empty-state h4 { font-size: 16px; margin-bottom: 8px; color:var(--text); }
    .empty-state p  { font-size: 13px; }

    /* Pagination */
    .pagination-wrapper {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .page-info { font-size: 12px; color: var(--text-muted); }
    .pagination { margin: 0; }
    .page-link {
        font-size: 12px; font-weight: 500;
        border-color: var(--border);
        color: var(--text);
        border-radius: 7px !important;
    }
    .page-link:hover  { background: var(--primary); color: #fff; border-color: var(--primary); }
    .page-item.active .page-link { background: var(--primary); border-color: var(--primary); }

    /* Btn actions */
    .btn-action {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 13px; font-weight: 600;
        text-decoration: none;
        transition: all .2s;
        border: none; cursor: pointer;
        font-family: 'Inter', sans-serif;
    }
    .btn-primary-am {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: #fff;
    }
    .btn-primary-am:hover { box-shadow: 0 6px 20px rgba(26,79,160,.3); transform:translateY(-1px); color:#fff; }
    .btn-secondary-am {
        background: var(--bg);
        color: var(--text);
        border: 1.5px solid var(--border);
    }
    .btn-secondary-am:hover { border-color:var(--primary); color:var(--primary); background:#eef3ff; }
    .btn-success-am {
        background: #ecfdf5;
        color: var(--success);
        border: 1.5px solid #a7f3d0;
    }
    .btn-success-am:hover { background:var(--success); color:#fff; }

    @media (max-width: 768px) {
        .stats-bar { grid-template-columns: 1fr 1fr; }
        .eleves-table th:nth-child(4),
        .eleves-table td:nth-child(4),
        .eleves-table th:nth-child(5),
        .eleves-table td:nth-child(5) { display: none; }
    }
</style>
@endsection

@section('content')

    <!-- Alert -->
    @if(session('success'))
    <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-left:4px solid var(--success);
                border-radius:10px; padding:12px 16px; margin-bottom:18px; font-size:13px;
                color:#065f46; display:flex; align-items:center; gap:10px;">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
    @endif

    <!-- Stats mini -->
    <div class="stats-bar">
        <div class="stat-mini">
            <div class="stat-mini-icon blue"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="stat-mini-val">{{ $stats['total'] }}</div>
                <div class="stat-mini-lbl">Total élèves</div>
            </div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-icon green"><i class="fa-solid fa-mars"></i></div>
            <div>
                <div class="stat-mini-val">{{ $stats['garcons'] }}</div>
                <div class="stat-mini-lbl">Garçons</div>
            </div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-icon pink"><i class="fa-solid fa-venus"></i></div>
            <div>
                <div class="stat-mini-val">{{ $stats['filles'] }}</div>
                <div class="stat-mini-lbl">Filles</div>
            </div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-icon orange"><i class="fa-solid fa-user-plus"></i></div>
            <div>
                <div class="stat-mini-val">{{ $stats['new_mois'] }}</div>
                <div class="stat-mini-lbl">Nouveaux ce mois</div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <form method="GET" action="{{ route('admin.eleves.index') }}"
              style="display:contents;" id="filterForm">

            <div class="toolbar-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search"
                       placeholder="Rechercher par nom, prénom, matricule…"
                       value="{{ request('search') }}"
                       oninput="debounceSubmit()">
            </div>

            <select class="toolbar-select" name="niveau" onchange="this.form.submit()">
                <option value="">Tous les niveaux</option>
                @foreach(['Préparatoire','Primaire','Collège','Lycée'] as $n)
                <option value="{{ $n }}" {{ request('niveau') == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="classe_id" onchange="this.form.submit()">
                <option value="">Toutes les classes</option>
                @foreach($classes as $cl)
                <option value="{{ $cl->id }}" {{ request('classe_id') == $cl->id ? 'selected' : '' }}>
                    {{ $cl->nom }}
                </option>
                @endforeach
            </select>

            <select class="toolbar-select" name="sexe" onchange="this.form.submit()" style="min-width:110px;">
                <option value="">Tous</option>
                <option value="M" {{ request('sexe')=='M' ? 'selected':'' }}>Garçons</option>
                <option value="F" {{ request('sexe')=='F' ? 'selected':'' }}>Filles</option>
            </select>

            @if(request()->hasAny(['search','niveau','classe_id','sexe']))
            <a href="{{ route('admin.eleves.index') }}" class="btn-action btn-secondary-am" title="Réinitialiser">
                <i class="fa-solid fa-xmark"></i>
            </a>
            @endif
        </form>

        <div style="margin-left:auto; display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('admin.eleves.import.form') }}" class="btn-action btn-success-am">
                <i class="fa-solid fa-file-import"></i> Importer
            </a>
            <a href="{{ route('admin.eleves.export.excel') }}" class="btn-action btn-secondary-am">
                <i class="fa-solid fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.eleves.export.pdf') }}" class="btn-action btn-secondary-am" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('admin.eleves.create') }}" class="btn-action btn-primary-am">
                <i class="fa-solid fa-plus"></i> Ajouter
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <table class="eleves-table">
            <thead>
                <tr>
                    <th class="sortable">Élève</th>
                    <th>Matricule</th>
                    <th>Classe</th>
                    <th>Niveau</th>
                    <th>Sexe</th>
                    <th>Parent</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eleves as $eleve)
                <tr>
                    <td>
                        <div class="eleve-info">
                            @if($eleve->photo)
                                <img src="{{ asset('storage/'.$eleve->photo) }}"
                                     class="eleve-avatar" alt="">
                            @else
                                <div class="eleve-avatar-initiales">
                                    {{ strtoupper(substr($eleve->prenom,0,1).substr($eleve->nom,0,1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="eleve-nom">{{ $eleve->nom }} {{ $eleve->prenom }}</div>
                                <div class="eleve-mat">
                                    @if($eleve->date_naissance)
                                        {{ $eleve->date_naissance->format('d/m/Y') }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="font-size:12px; background:#f0f4fa; padding:3px 7px; border-radius:5px;">
                            {{ $eleve->matricule }}
                        </code>
                    </td>
                    <td>
                        @if($eleve->classe)
                            <span class="badge-classe">{{ $eleve->classe->nom }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        @if($eleve->classe)
                            @php
                                $niveauClass = match($eleve->classe->niveau) {
                                    'Préparatoire' => 'prep',
                                    'Primaire'     => 'prim',
                                    'Collège'      => 'college',
                                    'Lycée'        => 'lycee',
                                    default        => 'college',
                                };
                            @endphp
                            <span class="badge-niveau {{ $niveauClass }}">{{ $eleve->classe->niveau }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($eleve->sexe)
                            <span class="badge-sexe {{ strtolower($eleve->sexe) }}">
                                {{ $eleve->sexe }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td style="font-size:13px;">
                        @if($eleve->parent)
                            {{ $eleve->parent->prenom }} {{ $eleve->parent->nom }}
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px; color:var(--text-muted);">
                        {{ $eleve->telephone ?: ($eleve->email ?: '—') }}
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.eleves.show', $eleve) }}"
                               class="action-btn view" title="Voir la fiche">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.eleves.edit', $eleve) }}"
                               class="action-btn edit" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a href="{{ route('admin.eleves.fiche', $eleve) }}"
                               class="action-btn print" title="Imprimer fiche" target="_blank">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <button type="button"
                                    class="action-btn delete"
                                    title="Désactiver"
                                    onclick="confirmDelete({{ $eleve->id }}, '{{ $eleve->prenom }} {{ $eleve->nom }}')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fa-solid fa-users-slash"></i>
                            <h4>Aucun élève trouvé</h4>
                            <p>
                                @if(request()->hasAny(['search','niveau','classe_id','sexe']))
                                    Aucun résultat pour ces filtres.
                                    <a href="{{ route('admin.eleves.index') }}" style="color:var(--primary);">
                                        Réinitialiser les filtres
                                    </a>
                                @else
                                    Commencez par ajouter un élève.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($eleves->hasPages())
        <div class="pagination-wrapper">
            <div class="page-info">
                Affichage de {{ $eleves->firstItem() }} à {{ $eleves->lastItem() }}
                sur {{ $eleves->total() }} élèves
            </div>
            {{ $eleves->links() }}
        </div>
        @endif
    </div>

    <!-- Modal confirmation suppression -->
    <div id="deleteModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5);
         z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; padding:32px; max-width:420px; width:90%;
                    box-shadow:0 20px 60px rgba(0,0,0,.2); text-align:center;">
            <div style="width:56px; height:56px; background:#fef2f2; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        margin:0 auto 16px; font-size:22px; color:var(--danger);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size:18px; margin-bottom:8px;">Désactiver cet élève ?</h3>
            <p id="deleteModalText" style="font-size:14px; color:var(--text-muted); margin-bottom:24px;"></p>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button onclick="closeDeleteModal()"
                        style="padding:10px 24px; border-radius:9px; border:1.5px solid var(--border);
                               background:var(--bg); font-size:13px; font-weight:600; cursor:pointer;">
                    Annuler
                </button>
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="padding:10px 24px; border-radius:9px; border:none;
                                   background:var(--danger); color:#fff; font-size:13px;
                                   font-weight:600; cursor:pointer;">
                        <i class="fa-solid fa-trash" style="margin-right:6px;"></i>Désactiver
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // Debounce recherche
    let searchTimer;
    function debounceSubmit() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
    }

    // Modal suppression
    function confirmDelete(id, nom) {
        document.getElementById('deleteModalText').textContent =
            `L'élève "${nom}" sera désactivé. Vous pourrez le réactiver depuis les paramètres.`;
        document.getElementById('deleteForm').action =
            `{{ url('admin/eleves') }}/${id}`;
        document.getElementById('deleteModal').style.display = 'flex';
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
@endsection
