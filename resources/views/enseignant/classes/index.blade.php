@extends('enseignant.layouts.app')

@section('title', 'Mes classes & matières')
@section('page-title', 'Mes classes & matières')
@section('page-subtitle', 'Les classes dans lesquelles vous intervenez')

@section('content')

@forelse($matieres as $classeNom => $liste)
    <div class="page-card">
        <h3>
            <i class="fa-solid fa-chalkboard-user" style="color:var(--primary);"></i> {{ $classeNom }}
            @php $cl = $liste->first()->classe ?? null; @endphp
            @if($cl)
                <span class="badge violet" style="margin-left:auto;">{{ $cl->niveau }}</span>
                <span class="badge gray"><i class="fa-solid fa-users"></i> {{ $cl->eleves_count ?? 0 }} élèves</span>
            @endif
        </h3>

        <table class="data-table">
            <thead>
                <tr><th>Matière</th><th>Coefficient</th><th>Heures / semaine</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($liste as $m)
                <tr>
                    <td style="font-weight:600;">{{ $m->nom }}</td>
                    <td>{{ $m->coefficient }}</td>
                    <td>{{ $m->heures_semaine ?? '—' }}</td>
                    <td style="text-align:right;">
                        @if($m->classe)
                        <a href="{{ route('enseignant.notes.saisie', [$m->classe_id, $m->id, 1]) }}" class="btn-am secondary sm">
                            <i class="fa-solid fa-pen-to-square"></i> Saisir les notes
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@empty
    <div class="page-card">
        <div class="empty-state">
            <i class="fa-solid fa-chalkboard"></i>
            Aucune classe ne vous est assignée pour le moment.<br>
            <span style="font-size:12.5px;">L'administration doit vous associer à des matières dans les classes.</span>
        </div>
    </div>
@endforelse

@endsection
