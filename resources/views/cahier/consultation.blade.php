@extends($layout)

@section('title', 'Cahier de texte')
@section('page-title', 'Cahier de texte')
@section('page-subtitle', 'Contenu des cours et devoirs')

@section('extra-css')
@include('cahier._styles')
@endsection

@section('content')
<div class="ct-wrap">

    {{-- Sélecteur enfant (parent avec plusieurs enfants) --}}
    @if($role === 'parent' && $enfants->count() > 1)
        <div class="ct-bar">
            <form method="GET" action="{{ route('cahier.index') }}" class="chip-row">
                <select name="eleve_id" onchange="this.form.submit()">
                    @foreach($enfants as $enf)
                        <option value="{{ $enf->id }}" {{ $eleveSel && $eleveSel->id === $enf->id ? 'selected' : '' }}>{{ $enf->prenom }} {{ $enf->nom }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    {{-- Filtre classe (admin) --}}
    @if($role === 'admin')
        <div class="ct-bar">
            <form method="GET" action="{{ route('cahier.index') }}" class="chip-row">
                <select name="classe_id" onchange="this.form.submit()">
                    <option value="">Toutes les classes</option>
                    @foreach($classesAdmin as $c)
                        <option value="{{ $c->id }}" {{ (string)$classeId === (string)$c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    @forelse($entrees as $e)
        <div class="ct-card">
            <div class="ct-head">
                <div class="ct-date">
                    <span class="d">{{ $e->date_cours?->format('d') }}</span>
                    <span class="m">{{ $e->date_cours?->translatedFormat('M') }}</span>
                </div>
                <div style="flex:1;">
                    <div class="ct-titre">{{ $e->matiere->nom ?? 'Matière' }} <span class="badge-mat">{{ $e->classe->nom ?? '' }}</span></div>
                    <div class="ct-sub">
                        {{ $e->date_cours?->translatedFormat('l d F Y') }}
                        @if($e->enseignant) · {{ $e->enseignant->prenom }} {{ $e->enseignant->nom }} @endif
                    </div>
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
                        <i class="fa-solid fa-paperclip"></i> Télécharger le support — {{ $e->fichierNom() }}
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="ct-card">
            <div class="empty-state">
                <i class="fa-solid fa-book"></i>
                Aucune entrée dans le cahier de texte pour le moment.
            </div>
        </div>
    @endforelse

</div>
@endsection
