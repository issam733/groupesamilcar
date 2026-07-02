@extends('admin.layouts.app')

@section('title', 'Annonces')
@section('page-title', 'Annonces')
@section('page-subtitle', 'Communication avec la communauté scolaire')

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

    .annonces-list { display:flex; flex-direction:column; gap:12px; }
    .annonce-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px 20px; box-shadow:var(--shadow-sm); transition:all .2s; }
    .annonce-card:hover { box-shadow:var(--shadow); }
    .annonce-top { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:8px; }
    .annonce-titre { font-size:14.5px; font-weight:700; color:var(--text); }
    .annonce-contenu { font-size:13px; color:var(--text-muted); line-height:1.5; margin:8px 0 12px; max-height:42px; overflow:hidden; text-overflow:ellipsis; }

    .badges-row { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .cible-badge { font-size:10.5px; font-weight:700; padding:3px 10px; border-radius:20px; }
    .cible-badge.all          { background:#eef3ff; color:var(--primary); }
    .cible-badge.enseignants  { background:#ecfdf5; color:var(--success); }
    .cible-badge.parents      { background:#fffbeb; color:var(--warning); }
    .cible-badge.eleves       { background:#f3eeff; color:#7c5cbf; }

    .statut-badge { font-size:10.5px; font-weight:700; padding:3px 10px; border-radius:20px; }
    .statut-badge.publie    { background:#ecfdf5; color:var(--success); }
    .statut-badge.brouillon { background:#f1f5f9; color:var(--text-muted); }

    .annonce-meta { font-size:11px; color:var(--text-muted); margin-left:auto; }
    .annonce-actions { display:flex; gap:6px; margin-top:14px; padding-top:14px; border-top:1px solid #f0f4fa; }
    .action-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:8px; border:1px solid var(--border); background:var(--bg); font-size:12px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all .15s; text-decoration:none; }
    .action-btn:hover.edit   { background:#fffbeb; color:var(--warning); border-color:var(--warning); }
    .action-btn:hover.email  { background:#eef3ff; color:var(--primary); border-color:var(--primary); }
    .action-btn:hover.delete { background:#fef2f2; color:var(--danger); border-color:var(--danger); }

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

<div class="stats-bar">
    <div class="stat-mini"><div class="stat-mini-val">{{ $stats['total'] }}</div><div class="stat-mini-lbl">Total annonces</div></div>
    <div class="stat-mini"><div class="stat-mini-val" style="color:var(--success);">{{ $stats['publiees'] }}</div><div class="stat-mini-lbl">Publiées</div></div>
    <div class="stat-mini"><div class="stat-mini-val" style="color:var(--text-muted);">{{ $stats['brouillons'] }}</div><div class="stat-mini-lbl">Brouillons</div></div>
    <div class="stat-mini"><div class="stat-mini-val">{{ $stats['ce_mois'] }}</div><div class="stat-mini-lbl">Ce mois</div></div>
</div>

<div class="toolbar">
    <form method="GET" action="{{ route('admin.annonces.index') }}" style="display:contents;" id="filterForm">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Rechercher une annonce…" value="{{ request('search') }}" oninput="debounceSubmit()">
        </div>
        <select class="toolbar-select" name="cible" onchange="this.form.submit()">
            <option value="">Toutes les cibles</option>
            <option value="all" {{ request('cible')=='all'?'selected':'' }}>Tous</option>
            <option value="enseignants" {{ request('cible')=='enseignants'?'selected':'' }}>Enseignants</option>
            <option value="parents" {{ request('cible')=='parents'?'selected':'' }}>Parents</option>
            <option value="eleves" {{ request('cible')=='eleves'?'selected':'' }}>Élèves</option>
        </select>
    </form>
    <div style="margin-left:auto;">
        <a href="{{ route('admin.annonces.create') }}" class="btn-am btn-primary-am">
            <i class="fa-solid fa-plus"></i> Nouvelle annonce
        </a>
    </div>
</div>

@if($annonces->count())
<div class="annonces-list">
    @foreach($annonces as $annonce)
    @php
        $cibleLabels = ['all'=>'Tous','enseignants'=>'Enseignants','parents'=>'Parents','eleves'=>'Élèves'];
    @endphp
    <div class="annonce-card">
        <div class="annonce-top">
            <div>
                <div class="annonce-titre">{{ $annonce->titre }}</div>
            </div>
            <div class="annonce-meta">{{ $annonce->created_at->diffForHumans() }}</div>
        </div>
        <div class="annonce-contenu">{{ $annonce->contenu }}</div>
        <div class="badges-row">
            <span class="cible-badge {{ $annonce->cible }}">{{ $cibleLabels[$annonce->cible] }}</span>
            <span class="statut-badge {{ $annonce->publie ? 'publie' : 'brouillon' }}">{{ $annonce->publie ? 'Publiée' : 'Brouillon' }}</span>
            @if($annonce->auteur)
            <span style="font-size:11px; color:var(--text-muted);">par {{ $annonce->auteur->prenom }} {{ $annonce->auteur->nom }}</span>
            @endif
        </div>
        <div class="annonce-actions">
            <a href="{{ route('admin.annonces.edit', $annonce) }}" class="action-btn edit"><i class="fa-solid fa-pen"></i> Modifier</a>
            <form method="POST" action="{{ route('admin.annonces.renvoyer', $annonce) }}" style="display:inline;" onsubmit="return confirm('Renvoyer les notifications email pour cette annonce ?')">
                @csrf
                <button type="submit" class="action-btn email"><i class="fa-solid fa-envelope"></i> Renvoyer emails</button>
            </form>
            <form method="POST" action="{{ route('admin.annonces.destroy', $annonce) }}" style="display:inline;" onsubmit="return confirm('Supprimer cette annonce ?')">
                @csrf @method('DELETE')
                <button type="submit" class="action-btn delete"><i class="fa-solid fa-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<div class="pagination-wrapper">{{ $annonces->links() }}</div>

@else
<div class="empty-state">
    <i class="fa-solid fa-bullhorn"></i>
    <h4 style="color:var(--text);margin-bottom:8px;">Aucune annonce pour le moment</h4>
    <p>Publiez votre première annonce pour informer la communauté scolaire.</p>
</div>
@endif

@endsection

@section('scripts')
<script>
let st;
function debounceSubmit() { clearTimeout(st); st = setTimeout(()=>document.getElementById('filterForm').submit(),500); }
</script>
@endsection
