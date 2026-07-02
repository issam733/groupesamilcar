@extends('eleve.layouts.app')

@section('title', 'Bibliothèque')
@section('page-title', 'Bibliothèque')
@section('page-subtitle', 'Ressources de ma classe')

@section('extra-css')
<style>
    .matiere-group { margin-bottom:22px; }
    .matiere-title { font-size:13px; font-weight:700; color:#0d9488; margin-bottom:10px; display:flex; align-items:center; gap:8px; }
    .ressources-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:10px; }
    .ressource-card { background:var(--card); border:1px solid var(--border); border-radius:10px; padding:12px 14px; display:flex; align-items:center; gap:10px; transition:all .2s; text-decoration:none; }
    .ressource-card:hover { border-color:#0d9488; box-shadow:var(--shadow-sm); }
    .ressource-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
    .ressource-icon.pdf   { background:#fef2f2; color:var(--danger); }
    .ressource-icon.video { background:#f3eeff; color:#7c5cbf; }
    .ressource-icon.lien  { background:#eef3ff; color:var(--primary); }
    .ressource-icon.autre { background:#fffbeb; color:var(--warning); }
    .ressource-titre { font-size:12.5px; font-weight:600; color:var(--text); }
    .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
    .empty-state i { font-size:48px; opacity:.3; display:block; margin-bottom:16px; }
</style>
@endsection

@section('content')

@forelse($parMatiere as $matiere => $ressources)
<div class="matiere-group">
    <div class="matiere-title"><i class="fa-solid fa-bookmark"></i> {{ $matiere }}</div>
    <div class="ressources-grid">
        @foreach($ressources as $r)
        @php $icons = ['pdf'=>'fa-file-pdf','video'=>'fa-video','lien'=>'fa-link','autre'=>'fa-file']; @endphp
        <a href="{{ $r->fichier ? asset('storage/'.$r->fichier) : $r->lien_externe }}" target="_blank" class="ressource-card">
            <div class="ressource-icon {{ $r->type }}"><i class="fa-solid {{ $icons[$r->type] ?? 'fa-file' }}"></i></div>
            <div class="ressource-titre">{{ $r->titre }}</div>
        </a>
        @endforeach
    </div>
</div>
@empty
<div class="empty-state">
    <i class="fa-solid fa-book-open"></i>
    <h4 style="color:var(--text);margin-bottom:8px;">Aucune ressource disponible</h4>
    <p>Votre enseignant n'a pas encore ajouté de ressources pour votre classe.</p>
</div>
@endforelse

@endsection
