@extends('enseignant.layouts.app')

@section('title', 'Saisie des notes')
@section('page-title', 'Saisie des notes')
@section('page-subtitle', 'Choisissez une matière et un trimestre')

@section('content')

<!-- Sélecteur de trimestre -->
<div class="page-card" style="display:flex; align-items:center; gap:14px;">
    <span style="font-weight:600; font-size:13.5px;"><i class="fa-solid fa-calendar"></i> Trimestre :</span>
    @for($t = 1; $t <= 3; $t++)
        <a href="{{ route('enseignant.notes.index', ['trimestre' => $t]) }}"
           class="btn-am {{ $trimestre == $t ? 'primary' : 'secondary' }} sm">Trimestre {{ $t }}</a>
    @endfor
</div>

<div class="page-card">
    <h3><i class="fa-solid fa-book" style="color:var(--primary);"></i> Mes matières — saisie T{{ $trimestre }}</h3>

    @if($matieres->isEmpty())
        <div class="empty-state">
            <i class="fa-solid fa-book"></i>
            Aucune matière ne vous est assignée.<br>
            <span style="font-size:12.5px;">Contactez l'administration pour être associé à des matières.</span>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr><th>Matière</th><th>Classe</th><th>Niveau</th><th style="text-align:right;">Action</th></tr>
            </thead>
            <tbody>
                @foreach($matieres as $m)
                <tr>
                    <td style="font-weight:600;">{{ $m->nom }}</td>
                    <td><span class="badge violet">{{ $m->classe->nom }}</span></td>
                    <td style="color:var(--text-muted); font-size:12.5px;">{{ $m->classe->niveau ?? '—' }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('enseignant.notes.saisie', [$m->classe_id, $m->id, $trimestre]) }}" class="btn-am primary sm">
                            <i class="fa-solid fa-pen-to-square"></i> Saisir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
