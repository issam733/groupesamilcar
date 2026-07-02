@extends($layout)

@section('title', 'Cahier de texte')
@section('page-title', 'Cahier de texte')
@section('page-subtitle', 'Contenu des cours et devoirs')

@section('extra-css')
@include('cahier._styles')
@endsection

@section('content')
<div class="ct-wrap">

    @if(session('success'))
        <div class="alert-ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <div class="ct-bar">
        <form method="GET" action="{{ route('cahier.index') }}" class="chip-row">
            <select name="matiere_id" onchange="this.form.submit()">
                <option value="">Toutes mes matières</option>
                @foreach($matieres as $m)
                    <option value="{{ $m->id }}" {{ (string)$matiereId === (string)$m->id ? 'selected' : '' }}>
                        {{ $m->nom }} — {{ $m->classe->nom ?? '' }}
                    </option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('cahier.create') }}" class="btn-ct"><i class="fa-solid fa-plus"></i> Nouvelle entrée</a>
    </div>

    @forelse($entrees as $e)
        <div class="ct-card">
            <div class="ct-head">
                <div class="ct-date">
                    <span class="d">{{ $e->date_cours?->format('d') }}</span>
                    <span class="m">{{ $e->date_cours?->translatedFormat('M') }}</span>
                </div>
                <div style="flex:1;">
                    <div class="ct-titre">{{ $e->matiere->nom ?? 'Matière' }} <span class="badge-mat">{{ $e->classe->nom ?? '' }}</span></div>
                    <div class="ct-sub">{{ $e->date_cours?->translatedFormat('l d F Y') }}</div>
                </div>
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('cahier.edit', $e) }}" class="btn-ct ghost sm"><i class="fa-solid fa-pen"></i> Modifier</a>
                    <form method="POST" action="{{ route('cahier.destroy', $e) }}" onsubmit="return confirm('Supprimer cette entrée ?');">
                        @csrf @method('DELETE')
                        <button class="btn-ct danger sm"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>
            <div class="ct-body">
                <div class="ct-label">Contenu du cours</div>
                <div class="ct-contenu">{{ $e->contenu }}</div>

                @if($e->aDesDevoirs())
                    <div class="ct-devoirs">
                        <div class="ct-label"><i class="fa-solid fa-house-laptop"></i> Devoirs à la maison</div>
                        <div class="txt">{{ $e->devoirs }}</div>
                        @if($e->date_remise)
                            <div class="ct-remise"><i class="fa-regular fa-calendar-check"></i> À remettre le {{ $e->date_remise->format('d/m/Y') }}</div>
                        @endif
                    </div>
                @endif

                @if($e->aUnFichier())
                    <a href="{{ asset('storage/'.$e->fichier) }}" target="_blank" class="btn-ct ghost sm" style="margin-top:12px;">
                        <i class="fa-solid fa-paperclip"></i> {{ $e->fichierNom() }}
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="ct-card">
            <div class="empty-state">
                <i class="fa-solid fa-book"></i>
                Aucune entrée pour le moment.<br>
                <a href="{{ route('cahier.create') }}" class="btn-ct" style="margin-top:14px;"><i class="fa-solid fa-plus"></i> Créer la première entrée</a>
            </div>
        </div>
    @endforelse

</div>
@endsection
