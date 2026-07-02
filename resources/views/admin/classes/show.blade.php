@extends('admin.layouts.app')

@section('title', $classe->nom)
@section('page-title', $classe->nom)
@section('page-subtitle', $classe->niveau.' — '.$classe->annee_scolaire)

@section('extra-css')
<style>
    .top-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:20px; }
    @media(max-width:800px) { .top-grid { grid-template-columns:1fr; } }
    .info-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .info-card-header { padding:14px 20px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; justify-content:space-between; }
    .info-card-header h5 { font-size:14px; font-weight:700; color:var(--text); margin:0; display:flex; align-items:center; gap:8px; }
    .info-card-body { padding:18px 20px; }

    .kpi-box { text-align:center; padding:20px; }
    .kpi-box-val { font-size:32px; font-weight:800; color:var(--primary); }
    .kpi-box-lbl { font-size:12px; color:var(--text-muted); margin-top:4px; }

    .matiere-row-show { display:flex; align-items:center; justify-content:space-between; padding:11px 0; border-bottom:1px solid #f0f4fa; }
    .matiere-row-show:last-child { border-bottom:none; }
    .matiere-nom-show { font-size:13.5px; font-weight:600; color:var(--text); }
    .matiere-meta-show { font-size:11px; color:var(--text-muted); margin-top:2px; }
    .coef-badge { background:#eef3ff; color:var(--primary); font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; }

    .eleve-row { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; transition:background .15s; }
    .eleve-row:hover { background:var(--bg); }
    .eleve-avatar-sm { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .eleve-nom-sm { font-size:13px; font-weight:600; color:var(--text); }
    .eleve-mat-sm { font-size:11px; color:var(--text-muted); }

    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
</style>
@endsection

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div></div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.classes.index') }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        <a href="{{ route('admin.classes.edit', $classe) }}" class="btn-am primary"><i class="fa-solid fa-pen"></i> Modifier</a>
    </div>
</div>

<div class="top-grid">
    <div class="info-card"><div class="kpi-box"><div class="kpi-box-val">{{ $classe->eleves->count() }}</div><div class="kpi-box-lbl">Élèves inscrits</div></div></div>
    <div class="info-card"><div class="kpi-box"><div class="kpi-box-val">{{ $classe->matieres->count() }}</div><div class="kpi-box-lbl">Matières</div></div></div>
    <div class="info-card"><div class="kpi-box"><div class="kpi-box-val">{{ $classe->effectif_max - $classe->eleves->count() }}</div><div class="kpi-box-lbl">Places disponibles</div></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" id="mainGrid">

    <div class="info-card">
        <div class="info-card-header"><h5><i class="fa-solid fa-book-open" style="color:var(--primary);"></i>Matières & enseignants</h5></div>
        <div class="info-card-body">
            @forelse($classe->matieres as $matiere)
            <div class="matiere-row-show">
                <div>
                    <div class="matiere-nom-show">{{ $matiere->nom }}</div>
                    <div class="matiere-meta-show">
                        {{ $matiere->enseignant ? $matiere->enseignant->prenom.' '.$matiere->enseignant->nom : 'Non assigné' }}
                        · {{ $matiere->heures_semaine }}h/semaine
                    </div>
                </div>
                <span class="coef-badge">coef {{ $matiere->coefficient }}</span>
            </div>
            @empty
            <p style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px 0;">Aucune matière définie.</p>
            @endforelse
        </div>
    </div>

    <div class="info-card">
        <div class="info-card-header">
            <h5><i class="fa-solid fa-users" style="color:var(--success);"></i>Liste des élèves</h5>
            <span style="font-size:12px;color:var(--text-muted);">{{ $classe->eleves->count() }}</span>
        </div>
        <div class="info-card-body" style="max-height:400px;overflow-y:auto;">
            @forelse($classe->eleves as $eleve)
            <a href="{{ route('admin.eleves.show', $eleve) }}" style="text-decoration:none;">
                <div class="eleve-row">
                    <div class="eleve-avatar-sm">{{ strtoupper(substr($eleve->prenom,0,1).substr($eleve->nom,0,1)) }}</div>
                    <div>
                        <div class="eleve-nom-sm">{{ $eleve->prenom }} {{ $eleve->nom }}</div>
                        <div class="eleve-mat-sm">{{ $eleve->matricule }}</div>
                    </div>
                </div>
            </a>
            @empty
            <p style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px 0;">Aucun élève dans cette classe.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
