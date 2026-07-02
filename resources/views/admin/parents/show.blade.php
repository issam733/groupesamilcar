@extends('admin.layouts.app')

@section('title', $parent->prenom.' '.$parent->nom)
@section('page-title', 'Fiche parent')
@section('page-subtitle', $parent->prenom.' '.$parent->nom)

@section('extra-css')
<style>
    .profile-grid { display:grid; grid-template-columns:280px 1fr; gap:20px; align-items:start; }
    @media(max-width:900px) { .profile-grid { grid-template-columns:1fr; } }
    .profile-card { background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .profile-header { background:linear-gradient(135deg,var(--warning),#f59e0b); padding:28px 20px 20px; text-align:center; }
    .profile-avatar-initials { width:90px; height:90px; border-radius:50%; border:4px solid rgba(255,255,255,.3); margin:0 auto 14px; background:rgba(255,255,255,.2); color:#fff; font-size:32px; font-weight:800; display:flex; align-items:center; justify-content:center; }
    .profile-name { font-size:18px; font-weight:700; color:#fff; margin-bottom:4px; }
    .profile-role { font-size:12px; color:rgba(255,255,255,.75); }
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

    .info-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .info-card-header { padding:14px 20px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; justify-content:space-between; }
    .info-card-header h5 { font-size:14px; font-weight:700; color:var(--text); margin:0; display:flex; align-items:center; gap:8px; }
    .info-card-body { padding:18px 20px; }

    .enfant-card { display:flex; align-items:center; gap:14px; padding:14px; background:var(--bg); border-radius:12px; margin-bottom:10px; }
    .enfant-card:last-child { margin-bottom:0; }
    .enfant-avatar { width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; font-size:15px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .enfant-nom { font-size:14px; font-weight:600; color:var(--text); }
    .enfant-classe { font-size:12px; color:var(--text-muted); margin-top:2px; }
    .enfant-link { margin-left:auto; }
</style>
@endsection

@section('content')
<div class="profile-grid">
    <div>
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar-initials">{{ strtoupper(substr($parent->prenom,0,1).substr($parent->nom,0,1)) }}</div>
                <div class="profile-name">{{ $parent->prenom }} {{ $parent->nom }}</div>
                <div class="profile-role">Parent / Tuteur</div>
            </div>
            <div class="profile-body">
                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                    <div><div class="info-label">Téléphone</div><div class="info-value">{{ $parent->telephone ?? '—' }}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fa-regular fa-envelope"></i></div>
                    <div><div class="info-label">Email</div><div class="info-value">{{ $parent->email ?? '—' }}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-briefcase"></i></div>
                    <div><div class="info-label">Profession</div><div class="info-value">{{ $parent->profession ?? '—' }}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div><div class="info-label">Compte</div><div class="info-value">{{ $parent->user_id ? 'Actif' : 'Aucun compte' }}</div></div>
                </div>
            </div>
            <div class="profile-actions">
                <a href="{{ route('admin.parents.edit', $parent) }}" class="btn-action-full"><i class="fa-solid fa-pen"></i> Modifier la fiche</a>
                <button type="button" class="btn-action-full danger" onclick="if(confirm('Désactiver ce parent ?')) document.getElementById('deleteForm').submit();">
                    <i class="fa-solid fa-ban"></i> Désactiver
                </button>
                <form id="deleteForm" method="POST" action="{{ route('admin.parents.destroy', $parent) }}">@csrf @method('DELETE')</form>
            </div>
        </div>
    </div>

    <div class="info-card">
        <div class="info-card-header">
            <h5><i class="fa-solid fa-children" style="color:var(--primary);"></i>Enfants inscrits</h5>
            <span style="font-size:12px;color:var(--text-muted);">{{ $parent->eleves->count() }} enfant(s)</span>
        </div>
        <div class="info-card-body">
            @forelse($parent->eleves as $eleve)
            <div class="enfant-card">
                <div class="enfant-avatar">{{ strtoupper(substr($eleve->prenom,0,1).substr($eleve->nom,0,1)) }}</div>
                <div>
                    <div class="enfant-nom">{{ $eleve->prenom }} {{ $eleve->nom }}</div>
                    <div class="enfant-classe">{{ $eleve->classe->nom ?? 'Aucune classe' }} · {{ $eleve->matricule }}</div>
                </div>
                <a href="{{ route('admin.eleves.show', $eleve) }}" class="enfant-link" style="color:var(--primary);font-size:13px;font-weight:600;text-decoration:none;">
                    Voir <i class="fa-solid fa-arrow-right" style="margin-left:4px;"></i>
                </a>
            </div>
            @empty
            <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:30px 0;">
                <i class="fa-solid fa-child" style="display:block;font-size:32px;margin-bottom:10px;opacity:.3;"></i>
                Aucun enfant rattaché à ce parent.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
