@extends('eleve.layouts.app')

@section('title', 'Accueil')
@section('page-title', 'Salut ' . (auth()->user()->prenom ?? '') . ' 👋')
@section('page-subtitle', $eleve->classe->nom ?? 'Aucune classe')

@section('extra-css')
<style>
    .top-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:24px; }
    @media(max-width:800px) { .top-grid { grid-template-columns:1fr; } }

    .stat-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:22px; box-shadow:var(--shadow-sm); text-align:center; }
    .stat-card-val { font-size:32px; font-weight:800; color:#0d9488; }
    .stat-card-lbl { font-size:12px; color:var(--text-muted); margin-top:6px; }

    .prochain-cours-card { background:linear-gradient(135deg,#0d9488,#14b8a6); border-radius:16px; padding:24px; color:#fff; margin-bottom:24px; }
    .prochain-cours-label { font-size:11px; text-transform:uppercase; letter-spacing:1px; opacity:.8; margin-bottom:8px; }
    .prochain-cours-matiere { font-size:22px; font-weight:800; margin-bottom:6px; }
    .prochain-cours-meta { display:flex; gap:20px; font-size:13px; opacity:.9; }
    .prochain-cours-meta span { display:flex; align-items:center; gap:6px; }

    .annonces-card { background:var(--card); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .annonces-header { padding:16px 20px; border-bottom:1px solid var(--border); background:#f7f9fd; }
    .annonces-header h5 { font-size:14px; font-weight:700; color:var(--text); margin:0; }
    .annonce-item { padding:14px 20px; border-bottom:1px solid #f0f4fa; }
    .annonce-item:last-child { border-bottom:none; }
    .annonce-titre { font-size:13px; font-weight:600; color:var(--text); margin-bottom:4px; }
    .annonce-date { font-size:11px; color:var(--text-muted); }
    .empty-annonces { padding:30px 20px; text-align:center; color:var(--text-muted); font-size:13px; }
</style>
@endsection

@section('content')

@if($prochainCours)
<div class="prochain-cours-card">
    <div class="prochain-cours-label">Prochain cours aujourd'hui</div>
    <div class="prochain-cours-matiere">{{ $prochainCours['matiere'] }}</div>
    <div class="prochain-cours-meta">
        <span><i class="fa-regular fa-clock"></i> {{ $prochainCours['heure'] }}</span>
        <span><i class="fa-solid fa-chalkboard-user"></i> {{ $prochainCours['enseignant'] }}</span>
    </div>
</div>
@endif

<div class="top-grid">
    <div class="stat-card">
        <div class="stat-card-val">{{ $stats['moyenne_actuelle'] !== null ? number_format($stats['moyenne_actuelle'],1) : '—' }}</div>
        <div class="stat-card-lbl">Moyenne actuelle /20</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-val">{{ $stats['absences'] }}</div>
        <div class="stat-card-lbl">Absences enregistrées</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-val">{{ $stats['ressources'] }}</div>
        <div class="stat-card-lbl">Ressources disponibles</div>
    </div>
</div>

<div class="annonces-card" style="margin-bottom:20px;">
    <div class="annonces-header"><h5><i class="fa-solid fa-house-laptop" style="color:#b45309;margin-right:8px;"></i>Devoirs à rendre</h5></div>
    @forelse($devoirs as $d)
    <div class="annonce-item" style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
        <div>
            <div class="annonce-titre">{{ $d->matiere->nom ?? 'Matière' }}</div>
            <div style="font-size:12.5px; color:#6b7280; margin-top:2px;">{{ \Illuminate\Support\Str::limit($d->devoirs, 90) }}</div>
        </div>
        <span style="font-size:12px; font-weight:700; color:#b45309; background:#fef3c7; border-radius:20px; padding:4px 12px; white-space:nowrap;">
            <i class="fa-regular fa-calendar-check"></i> {{ $d->date_remise->format('d/m') }}
        </span>
    </div>
    @empty
    <div class="empty-annonces">Aucun devoir à rendre pour l'instant. 🎉</div>
    @endforelse
</div>

<div class="annonces-card">
    <div class="annonces-header"><h5><i class="fa-solid fa-bullhorn" style="color:#0d9488;margin-right:8px;"></i>Annonces récentes</h5></div>
    @forelse($annonces as $annonce)
    <div class="annonce-item">
        <div class="annonce-titre">{{ $annonce->titre }}</div>
        <div class="annonce-date">{{ $annonce->created_at->diffForHumans() }}</div>
    </div>
    @empty
    <div class="empty-annonces">Aucune annonce pour le moment.</div>
    @endforelse
</div>

@endsection
