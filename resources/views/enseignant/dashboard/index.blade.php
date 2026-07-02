@extends('enseignant.layouts.app')

@section('title', 'Accueil')
@section('page-title', 'Bonjour, ' . ($ens->prenom ?? 'Enseignant'))
@section('page-subtitle', 'Voici un aperçu de votre activité')

@section('content')

<!-- Statistiques -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#6d28d9,#8b5cf6);"><i class="fa-solid fa-chalkboard-user"></i></div>
        <div><div class="stat-val">{{ $stats['classes'] }}</div><div class="stat-lbl">Classes</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#1a4fa0,#2e6fd8);"><i class="fa-solid fa-book"></i></div>
        <div><div class="stat-val">{{ $stats['matieres'] }}</div><div class="stat-lbl">Matières</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6);"><i class="fa-solid fa-users"></i></div>
        <div><div class="stat-val">{{ $stats['eleves'] }}</div><div class="stat-lbl">Élèves</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#7c5cbf,#1a4fa0);"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
        <div><div class="stat-val">{{ $stats['examens'] }}</div><div class="stat-lbl">Examens IA</div></div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start;">

    <!-- Colonne principale -->
    <div>
        <!-- Cours du jour -->
        <div class="page-card">
            <h3><i class="fa-solid fa-calendar-day" style="color:var(--primary);"></i> Vos cours aujourd'hui</h3>
            @forelse($coursAujourdhui as $c)
                <div style="display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid var(--border);">
                    <div style="background:#f3eeff; color:var(--primary); border-radius:9px; padding:8px 12px; font-weight:700; font-size:13px; min-width:110px; text-align:center;">
                        {{ substr($c->heure_debut,0,5) }} – {{ substr($c->heure_fin,0,5) }}
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:14px;">{{ $c->matiere->nom ?? 'Matière' }}</div>
                        <div style="font-size:12px; color:var(--text-muted);"><i class="fa-solid fa-door-open"></i> {{ $c->classe->nom ?? '—' }}</div>
                    </div>
                </div>
            @empty
                <div class="empty-state"><i class="fa-regular fa-calendar-check"></i> Aucun cours programmé aujourd'hui.</div>
            @endforelse
        </div>

        <!-- Mes matières -->
        <div class="page-card">
            <h3><i class="fa-solid fa-book-open" style="color:var(--primary);"></i> Mes matières</h3>
            @forelse($matieres as $m)
                <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border);">
                    <div>
                        <span style="font-weight:600;">{{ $m->nom }}</span>
                        <span class="badge gray" style="margin-left:8px;">{{ $m->classe->nom ?? 'Sans classe' }}</span>
                    </div>
                    <span style="font-size:12px; color:var(--text-muted);">Coef. {{ $m->coefficient }}</span>
                </div>
            @empty
                <div class="empty-state"><i class="fa-solid fa-book"></i> Aucune matière ne vous est encore assignée.<br><span style="font-size:12px;">Contactez l'administration.</span></div>
            @endforelse
        </div>
    </div>

    <!-- Colonne latérale : annonces + actions -->
    <div>
        <div class="page-card">
            <h3><i class="fa-solid fa-bolt" style="color:var(--warning);"></i> Actions rapides</h3>
            <a href="{{ route('enseignant.examens.create') }}" class="btn-am primary" style="width:100%; justify-content:center; margin-bottom:10px;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Générer un examen IA
            </a>
            <a href="{{ route('enseignant.notes.index') }}" class="btn-am secondary" style="width:100%; justify-content:center;">
                <i class="fa-solid fa-pen-to-square"></i> Saisir des notes
            </a>
        </div>

        <div class="page-card">
            <h3><i class="fa-solid fa-bullhorn" style="color:var(--primary);"></i> Dernières annonces</h3>
            @forelse($annonces as $a)
                <div style="padding:10px 0; border-bottom:1px solid var(--border);">
                    <div style="font-weight:600; font-size:13.5px;">{{ $a->titre }}</div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:3px;">{{ \Illuminate\Support\Str::limit($a->contenu, 90) }}</div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:5px;">{{ $a->created_at?->diffForHumans() }}</div>
                </div>
            @empty
                <div class="empty-state" style="padding:24px 10px;"><i class="fa-regular fa-bell"></i> Aucune annonce.</div>
            @endforelse
            @if($annonces->count())
                <a href="{{ route('enseignant.annonces') }}" style="font-size:12.5px; color:var(--primary); font-weight:600; text-decoration:none; display:inline-block; margin-top:10px;">Voir tout →</a>
            @endif
        </div>
    </div>

</div>

@endsection
