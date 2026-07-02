@extends('parent.layouts.app')

@section('title', 'Annonces')
@section('page-title', 'Annonces')
@section('page-subtitle', 'Messages publiés par l’administration')

@section('content')
    <div class="card p-4">
        @forelse($annonces as $annonce)
            <div class="border-bottom py-3">
                <h5>{{ $annonce->titre }}</h5>

                <div class="text-muted small mb-2">
                    {{ $annonce->created_at?->format('d/m/Y H:i') }}
                </div>

                <p class="mb-0">{{ $annonce->contenu }}</p>
            </div>
        @empty
            <p class="text-muted mb-0">Aucune annonce disponible.</p>
        @endforelse
    </div>
@endsection
