@extends('enseignant.layouts.app')

@section('title', 'Annonces')
@section('page-title', 'Annonces de l\'école')
@section('page-subtitle', 'Communications de l\'administration')

@section('content')

@forelse($annonces as $a)
    <div class="page-card" style="margin-bottom:14px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div>
                <div style="font-weight:700; font-size:15px;">{{ $a->titre }}</div>
                <div style="font-size:11.5px; color:var(--text-muted); margin-top:4px;">
                    <i class="fa-solid fa-user"></i> {{ $a->auteur?->prenom ?? 'Administration' }} {{ $a->auteur?->nom ?? '' }}
                    · <i class="fa-regular fa-clock"></i> {{ $a->created_at?->format('d/m/Y H:i') }}
                </div>
            </div>
            <span class="badge {{ $a->cible === 'enseignants' ? 'violet' : 'gray' }}">
                {{ \App\Models\Annonce::cibles()[$a->cible] ?? $a->cible }}
            </span>
        </div>
        <div style="margin-top:12px; font-size:13.5px; line-height:1.6; color:var(--text);">
            {!! nl2br(e($a->contenu)) !!}
        </div>
    </div>
@empty
    <div class="page-card"><div class="empty-state"><i class="fa-regular fa-bell"></i> Aucune annonce pour le moment.</div></div>
@endforelse

@if($annonces->hasPages())
    <div style="margin-top:16px;">{{ $annonces->links() }}</div>
@endif

@endsection
