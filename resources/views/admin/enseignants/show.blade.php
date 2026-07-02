@extends('admin.layouts.app')

@section('title', $enseignant->prenom.' '.$enseignant->nom)
@section('page-title', 'Fiche enseignant')
@section('page-subtitle', $enseignant->prenom.' '.$enseignant->nom)

@section('extra-css')
<style>
    .profile-grid { display:grid; grid-template-columns:280px 1fr; gap:20px; align-items:start; }
    @media(max-width:900px) { .profile-grid { grid-template-columns:1fr; } }

    .profile-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .profile-header { background:linear-gradient(135deg,var(--success),#0d9488); padding:28px 20px 20px; text-align:center; }
    .profile-avatar { width:90px; height:90px; border-radius:50%; border:4px solid rgba(255,255,255,.3); margin:0 auto 14px; object-fit:cover; display:block; }
    .profile-avatar-initials { width:90px; height:90px; border-radius:50%; border:4px solid rgba(255,255,255,.3); margin:0 auto 14px; background:rgba(255,255,255,.2); color:#fff; font-size:32px; font-weight:800; display:flex; align-items:center; justify-content:center; }
    .profile-name { font-size:18px; font-weight:700; color:#fff; margin-bottom:4px; }
    .profile-role { font-size:12px; color:rgba(255,255,255,.75); }
    .profile-matiere { display:inline-block; margin-top:10px; background:rgba(255,255,255,.2); color:#fff; font-size:12px; font-weight:600; padding:4px 12px; border-radius:20px; }

    .profile-body { padding:20px; }
    .info-row { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #f0f4fa; }
    .info-row:last-child { border-bottom:none; }
    .info-icon { width:32px; height:32px; border-radius:8px; background:var(--bg); display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--text-muted); flex-shrink:0; }
    .info-label { font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px; }
    .info-value { font-size:13px; color:var(--text); font-weight:500; }

    .profile-actions { padding:16px 20px; border-top:1px solid var(--border); display:flex; flex-direction:column; gap:8px; }
    .btn-action-full { display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; transition:all .2s; border:1.5px solid var(--border); background:var(--bg); color:var(--text); cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-action-full:hover { border-color:var(--primary); color:var(--primary); background:#eef3ff; }
    .btn-action-full.danger:hover { border-color:var(--danger); color:var(--danger); background:#fef2f2; }

    .right-col { display:flex; flex-direction:column; gap:18px; }
    .info-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .info-card-header { padding:14px 20px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; justify-content:space-between; }
    .info-card-header h5 { font-size:14px; font-weight:700; color:var(--text); margin:0; display:flex; align-items:center; gap:8px; }
    .info-card-body { padding:18px 20px; }

    .kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
    @media(max-width:700px) { .kpi-row { grid-template-columns:1fr 1fr; } }
    .kpi-mini { text-align:center; padding:16px 10px; background:var(--bg); border-radius:10px; }
    .kpi-mini-val { font-size:28px; font-weight:800; color:var(--primary); line-height:1; }
    .kpi-mini-lbl { font-size:11px; color:var(--text-muted); margin-top:4px; }

    .classe-item { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--bg); border-radius:10px; margin-bottom:8px; }
    .classe-item:last-child { margin-bottom:0; }
    .classe-nom { font-size:13px; font-weight:600; color:var(--text); }
    .classe-niveau { font-size:11px; color:var(--text-muted); margin-top:2px; }
    .classe-effectif { font-size:12px; color:var(--primary); font-weight:600; background:#eef3ff; padding:3px 10px; border-radius:20px; }

    .matiere-tag { display:inline-block; font-size:12px; font-weight:500; padding:5px 12px; border-radius:8px; background:#f3eeff; color:#7c5cbf; margin:4px; }
</style>
@endsection

@section('content')
<div class="profile-grid">

    <!-- Colonne gauche -->
    <div>
        <div class="profile-card">
            <div class="profile-header">
                @if($enseignant->photo)
                    <img src="{{ asset('storage/'.$enseignant->photo) }}" class="profile-avatar" alt="">
                @else
                    <div class="profile-avatar-initials">{{ $enseignant->initiales }}</div>
                @endif
                <div class="profile-name">{{ $enseignant->prenom }} {{ $enseignant->nom }}</div>
                <div class="profile-role">Enseignant</div>
                @if($enseignant->matiere)
                    <div class="profile-matiere"><i class="fa-solid fa-book" style="margin-right:5px;"></i>{{ $enseignant->matiere }}</div>
                @endif
            </div>

            <div class="profile-body">
                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                    <div><div class="info-label">Téléphone</div><div class="info-value">{{ $enseignant->telephone ?? '—' }}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fa-regular fa-envelope"></i></div>
                    <div><div class="info-label">Email</div><div class="info-value">{{ $enseignant->email ?? '—' }}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div><div class="info-label">Diplôme</div><div class="info-value">{{ $enseignant->diplome ?? '—' }}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <div class="info-label">Compte connexion</div>
                        <div class="info-value">
                            @if($enseignant->user_id)
                                <span style="color:var(--success);font-weight:600;"><i class="fa-solid fa-circle-check" style="margin-right:4px;"></i>Actif</span>
                            @else
                                <span style="color:var(--text-muted);">Aucun compte</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-calendar"></i></div>
                    <div><div class="info-label">Membre depuis</div><div class="info-value">{{ $enseignant->created_at->format('d/m/Y') }}</div></div>
                </div>
            </div>

            <div class="profile-actions">
                <a href="{{ route('admin.enseignants.edit', $enseignant) }}" class="btn-action-full">
                    <i class="fa-solid fa-pen"></i> Modifier la fiche
                </a>
                <button type="button" class="btn-action-full danger"
                    onclick="if(confirm('Désactiver cet enseignant ?')) { document.getElementById('deleteForm').submit(); }">
                    <i class="fa-solid fa-ban"></i> Désactiver
                </button>
                <form id="deleteForm" method="POST" action="{{ route('admin.enseignants.destroy', $enseignant) }}">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
    </div>

    <!-- Colonne droite -->
    <div class="right-col">

        <!-- KPIs -->
        <div class="info-card">
            <div class="info-card-header">
                <h5><i class="fa-solid fa-chart-bar" style="color:var(--success);"></i>Statistiques</h5>
            </div>
            <div class="info-card-body">
                <div class="kpi-row">
                    <div class="kpi-mini">
                        <div class="kpi-mini-val">{{ $stats['classes'] }}</div>
                        <div class="kpi-mini-lbl">Classes</div>
                    </div>
                    <div class="kpi-mini">
                        <div class="kpi-mini-val">{{ $stats['eleves'] }}</div>
                        <div class="kpi-mini-lbl">Élèves</div>
                    </div>
                    <div class="kpi-mini">
                        <div class="kpi-mini-val">{{ $stats['matieres'] }}</div>
                        <div class="kpi-mini-lbl">Matières</div>
                    </div>
                    <div class="kpi-mini">
                        <div class="kpi-mini-val">{{ $stats['examens'] }}</div>
                        <div class="kpi-mini-lbl">Examens IA</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes -->
        <div class="info-card">
            <div class="info-card-header">
                <h5><i class="fa-solid fa-door-open" style="color:var(--primary);"></i>Classes enseignées</h5>
                <span style="font-size:12px;color:var(--text-muted);">{{ $enseignant->classes->count() }} classe(s)</span>
            </div>
            <div class="info-card-body">
                @forelse($enseignant->classes as $classe)
                <div class="classe-item">
                    <div>
                        <div class="classe-nom">{{ $classe->nom }}</div>
                        <div class="classe-niveau">{{ $classe->niveau }}</div>
                    </div>
                    <span class="classe-effectif">{{ $classe->eleves->count() }} élèves</span>
                </div>
                @empty
                <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px 0;">
                    <i class="fa-solid fa-door-closed" style="display:block;font-size:28px;margin-bottom:8px;opacity:.3;"></i>
                    Aucune classe assignée
                </div>
                @endforelse
            </div>
        </div>

        <!-- Matières -->
        @if($enseignant->matieres->count())
        <div class="info-card">
            <div class="info-card-header">
                <h5><i class="fa-solid fa-book-open" style="color:#7c5cbf;"></i>Matières enseignées</h5>
            </div>
            <div class="info-card-body">
                @foreach($enseignant->matieres->groupBy('classe_id') as $classeId => $matieres)
                    @php $classeNom = $matieres->first()->classe->nom ?? '—'; @endphp
                    <div style="margin-bottom:12px;">
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;margin-bottom:6px;">{{ $classeNom }}</div>
                        @foreach($matieres as $m)
                            <span class="matiere-tag">{{ $m->nom }} <span style="opacity:.6;">coef {{ $m->coefficient }}</span></span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
