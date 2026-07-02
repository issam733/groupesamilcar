@extends('parent.layouts.app')

@section('title', 'Accueil')
@section('page-title', 'Bonjour ' . (auth()->user()->prenom ?? '') . ' 👋')
@section('page-subtitle', 'Voici un aperçu de la scolarité de vos enfants')

@section('extra-css')
<style>
    .enfants-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:18px; margin-bottom:28px; }
    .enfant-card { background:var(--card); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow-sm); transition:all .2s; }
    .enfant-card:hover { transform:translateY(-3px); box-shadow:var(--shadow); }

    .enfant-card-header { background:linear-gradient(135deg,#d97706,#f59e0b); padding:20px; display:flex; align-items:center; gap:14px; }
    .enfant-avatar { width:56px; height:56px; border-radius:50%; background:rgba(255,255,255,.25); border:2px solid rgba(255,255,255,.4); display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:800; color:#fff; flex-shrink:0; }
    .enfant-nom { font-size:16px; font-weight:700; color:#fff; }
    .enfant-classe { font-size:12px; color:rgba(255,255,255,.85); margin-top:2px; }

    .enfant-card-body { padding:18px 20px; }
    .mini-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
    .mini-stat { text-align:center; padding:12px 6px; background:var(--bg); border-radius:10px; }
    .mini-stat-val { font-size:20px; font-weight:800; color:var(--text); }
    .mini-stat-lbl { font-size:10px; color:var(--text-muted); margin-top:3px; }
    .mini-stat.alert .mini-stat-val { color:var(--danger); }
    .mini-stat.good .mini-stat-val { color:var(--success); }

    .btn-voir { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:11px; border-radius:10px; background:linear-gradient(135deg,#d97706,#f59e0b); color:#fff; text-decoration:none; font-size:13px; font-weight:600; transition:all .2s; }
    .btn-voir:hover { box-shadow:0 6px 20px rgba(217,119,6,.3); color:#fff; }

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

<div class="enfants-grid">
    @forelse($enfants as $enfant)
    @php
        $derniereMoyenne = $enfant->moyenne_t3 ?? $enfant->moyenne_t2 ?? $enfant->moyenne_t1;
    @endphp
    <div class="enfant-card">
        <div class="enfant-card-header">
            <div class="enfant-avatar">{{ strtoupper(substr($enfant->prenom,0,1).substr($enfant->nom,0,1)) }}</div>
            <div>
                <div class="enfant-nom">{{ $enfant->prenom }} {{ $enfant->nom }}</div>
                <div class="enfant-classe">{{ $enfant->classe->nom ?? 'Aucune classe' }}</div>
            </div>
        </div>
        <div class="enfant-card-body">
            <div class="mini-stats">
                <div class="mini-stat {{ $derniereMoyenne && $derniereMoyenne >= 12 ? 'good' : '' }}">
                    <div class="mini-stat-val">{{ $derniereMoyenne !== null ? number_format($derniereMoyenne, 1) : '—' }}</div>
                    <div class="mini-stat-lbl">Moyenne /20</div>
                </div>
                <div class="mini-stat {{ $enfant->absences_non_justifiees > 0 ? 'alert' : '' }}">
                    <div class="mini-stat-val">{{ $enfant->absences_count }}</div>
                    <div class="mini-stat-lbl">Absences</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-val">{{ $enfant->matricule }}</div>
                    <div class="mini-stat-lbl" style="font-size:9px;">Matricule</div>
                </div>
            </div>
            @if($enfant->devoirs_a_venir && $enfant->devoirs_a_venir->count())
                <div style="margin-top:14px; background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:12px 14px;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#b45309; margin-bottom:8px;"><i class="fa-solid fa-house-laptop"></i> Devoirs à rendre</div>
                    @foreach($enfant->devoirs_a_venir as $d)
                        <div style="display:flex; justify-content:space-between; gap:8px; align-items:center; padding:4px 0; font-size:12.5px;">
                            <span style="color:#1e2238;">{{ $d->matiere->nom ?? 'Matière' }}</span>
                            <span style="font-weight:700; color:#b45309; white-space:nowrap;"><i class="fa-regular fa-calendar-check"></i> {{ $d->date_remise->format('d/m') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            <a href="{{ route('parent.enfant.show', $enfant) }}" class="btn-voir">
                <i class="fa-solid fa-eye"></i> Voir le dossier complet
            </a>
        </div>
    </div>
    @empty
    <div style="text-align:center; padding:60px 20px; color:var(--text-muted); grid-column:1/-1;">
        <i class="fa-solid fa-child" style="font-size:48px; opacity:.3; display:block; margin-bottom:16px;"></i>
        Aucun enfant rattaché à votre compte. Contactez l'administration.
    </div>
    @endforelse
</div>

<div class="annonces-card">
    <div class="annonces-header"><h5><i class="fa-solid fa-bullhorn" style="color:#d97706;margin-right:8px;"></i>Annonces récentes</h5></div>
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
