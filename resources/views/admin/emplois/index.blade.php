@extends('admin.layouts.app')

@section('title', 'Emplois du temps')
@section('page-title', 'Emplois du temps')
@section('page-subtitle', 'Sélectionnez une classe pour gérer son emploi du temps')

@section('extra-css')
<style>
    .classes-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:16px; }
    .classe-link-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px; text-decoration:none; display:block; transition:all .2s; box-shadow:var(--shadow-sm); position:relative; overflow:hidden; }
    .classe-link-card:hover { transform:translateY(-3px); box-shadow:var(--shadow); border-color:var(--primary); }
    .classe-link-card::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; background:var(--primary); }
    .classe-link-nom { font-size:16px; font-weight:700; color:var(--text); margin-bottom:4px; }
    .classe-link-niveau { font-size:12px; color:var(--text-muted); margin-bottom:12px; }
    .classe-link-stats { display:flex; align-items:center; gap:8px; font-size:12px; }
    .classe-link-badge { background:#eef3ff; color:var(--primary); font-weight:600; padding:4px 10px; border-radius:20px; }
    .classe-link-icon { width:40px; height:40px; background:#eef3ff; color:var(--primary); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; margin-bottom:12px; }
    .niveau-section { margin-bottom:28px; }
    .niveau-title { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid var(--border); }
</style>
@endsection

@section('content')

@foreach($classes->groupBy('niveau') as $niveau => $classesNiveau)
<div class="niveau-section">
    <div class="niveau-title">{{ $niveau }}</div>
    <div class="classes-grid">
        @foreach($classesNiveau as $classe)
        <a href="{{ route('admin.emplois.show', $classe) }}" class="classe-link-card">
            <div class="classe-link-icon"><i class="fa-solid fa-calendar-days"></i></div>
            <div class="classe-link-nom">{{ $classe->nom }}</div>
            <div class="classe-link-niveau">{{ $classe->annee_scolaire }}</div>
            <div class="classe-link-stats">
                <span class="classe-link-badge">{{ $classe->emplois_count }} créneau(x)</span>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endforeach

@if($classes->isEmpty())
<div style="text-align:center; padding:60px 20px; color:var(--text-muted);">
    <i class="fa-solid fa-calendar-xmark" style="font-size:48px; opacity:.3; display:block; margin-bottom:16px;"></i>
    <h4 style="color:var(--text); margin-bottom:8px;">Aucune classe disponible</h4>
    <p>Créez d'abord une classe pour gérer son emploi du temps.</p>
</div>
@endif

@endsection
