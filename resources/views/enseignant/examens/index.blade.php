@extends('enseignant.layouts.app')

@section('title', 'Examens IA')
@section('page-title', 'Mes examens IA')
@section('page-subtitle', 'Générés par intelligence artificielle')

@section('content')

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#7c5cbf,#1a4fa0);"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
        <div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6);"><i class="fa-solid fa-paper-plane"></i></div>
        <div><div class="stat-val">{{ $stats['envoyes'] }}</div><div class="stat-lbl">Envoyés aux élèves</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:linear-gradient(135deg,#e8a020,#f0b955);"><i class="fa-solid fa-file-pen"></i></div>
        <div><div class="stat-val">{{ $stats['brouillons'] }}</div><div class="stat-lbl">Non envoyés</div></div>
    </div>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:12px; flex-wrap:wrap;">
    <form method="GET" style="display:flex; gap:8px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un examen…"
               style="padding:9px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; min-width:240px; outline:none;">
        <button class="btn-am secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <a href="{{ route('enseignant.examens.create') }}" class="btn-am primary"><i class="fa-solid fa-plus"></i> Générer un examen</a>
</div>

@if($examens->isEmpty())
    <div class="page-card">
        <div class="empty-state">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            Aucun examen pour le moment.<br>
            <a href="{{ route('enseignant.examens.create') }}" class="btn-am primary" style="margin-top:14px;"><i class="fa-solid fa-plus"></i> Créer mon premier examen</a>
        </div>
    </div>
@else
    <div class="page-card" style="padding:0; overflow:hidden;">
        <table class="data-table">
            <thead>
                <tr><th>Titre</th><th>Classe</th><th>Matière</th><th>Langue</th><th>Statut</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($examens as $ex)
                <tr>
                    <td style="font-weight:600;">
                        <a href="{{ route('enseignant.examens.show', $ex) }}" style="color:var(--text); text-decoration:none;">{{ $ex->titre }}</a>
                        <div style="font-size:11px; color:var(--text-muted); font-weight:400;">{{ $ex->nb_questions }} questions · {{ ucfirst($ex->difficulte) }}</div>
                    </td>
                    <td>{{ $ex->classe->nom ?? '—' }}</td>
                    <td>{{ $ex->matiere->nom ?? '—' }}</td>
                    <td>{{ strtoupper($ex->langue) }}</td>
                    <td>
                        @if($ex->statut === 'envoye')
                            <span class="badge green"><i class="fa-solid fa-paper-plane"></i> Envoyé</span>
                        @else
                            <span class="badge amber"><i class="fa-solid fa-file-pen"></i> Non envoyé</span>
                        @endif
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('enseignant.examens.show', $ex) }}" class="btn-am secondary sm"><i class="fa-solid fa-eye"></i></a>
                        <form method="POST" action="{{ route('enseignant.examens.destroy', $ex) }}" style="display:inline;" onsubmit="return confirm('Supprimer cet examen ?');">
                            @csrf @method('DELETE')
                            <button class="btn-am danger sm"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($examens->hasPages())
        <div style="margin-top:16px;">{{ $examens->links() }}</div>
    @endif
@endif

@endsection
