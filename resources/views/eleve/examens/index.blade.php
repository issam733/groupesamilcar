@extends('eleve.layouts.app')

@section('title', 'Mes examens')
@section('page-title', 'Mes examens')
@section('page-subtitle', 'Examens envoyés par vos enseignants')

@section('extra-css')
<style>
    .ex-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
    .ex-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px; box-shadow:var(--shadow-sm); display:flex; flex-direction:column; gap:10px; }
    .ex-card .ex-title { font-size:15px; font-weight:700; color:var(--text); }
    .ex-meta { font-size:12px; color:var(--text-muted); display:flex; flex-wrap:wrap; gap:10px; }
    .ex-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; background:#ccfbf1; color:#0f766e; align-self:flex-start; }
    .ex-btn { margin-top:auto; display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px; border-radius:9px; background:linear-gradient(135deg,#0d9488,#14b8a6); color:#fff; text-decoration:none; font-size:13px; font-weight:600; }
    .empty-state { text-align:center; padding:48px 20px; color:var(--text-muted); }
    .empty-state i { font-size:42px; opacity:.4; margin-bottom:14px; display:block; }
</style>
@endsection

@section('content')

@if($examens->isEmpty())
    <div class="card" style="padding:30px;">
        <div class="empty-state">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            Aucun examen ne vous a été envoyé pour le moment.<br>
            <span style="font-size:12.5px;">Vos enseignants pourront en publier ici.</span>
        </div>
    </div>
@else
    <div class="ex-grid">
        @foreach($examens as $ex)
        <div class="ex-card">
            <span class="ex-badge"><i class="fa-solid fa-wand-magic-sparkles"></i> {{ strtoupper($ex->langue) }}</span>
            <div class="ex-title">{{ $ex->titre }}</div>
            <div class="ex-meta">
                <span><i class="fa-solid fa-book"></i> {{ $ex->matiere->nom ?? 'Matière' }}</span>
                <span><i class="fa-solid fa-list-ol"></i> {{ $ex->nb_questions }} questions</span>
            </div>
            <div class="ex-meta">
                <span><i class="fa-solid fa-chalkboard-user"></i> {{ $ex->enseignant?->prenom }} {{ $ex->enseignant?->nom }}</span>
                <span><i class="fa-regular fa-clock"></i> {{ $ex->created_at?->format('d/m/Y') }}</span>
            </div>
            <a href="{{ route('eleve.examens.show', $ex) }}" class="ex-btn"><i class="fa-solid fa-eye"></i> Consulter l'examen</a>
        </div>
        @endforeach
    </div>
@endif

@endsection
