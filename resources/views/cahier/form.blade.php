@extends($layout)

@section('title', $entree ? 'Modifier une entrée' : 'Nouvelle entrée')
@section('page-title', 'Cahier de texte')
@section('page-subtitle', $entree ? 'Modifier une entrée' : 'Nouvelle entrée de cours')

@section('extra-css')
@include('cahier._styles')
@endsection

@section('content')
@php
    $matieresParClasse = $matieres->groupBy(fn($m) => $m->classe->nom ?? 'Sans classe');
    $val = fn($champ, $def = '') => old($champ, $entree->$champ ?? $def);
@endphp

<div class="ct-wrap" style="max-width:720px;">
    <div class="ct-bar">
        <a href="{{ route('cahier.index') }}" class="btn-ct ghost"><i class="fa-solid fa-arrow-left"></i> Retour</a>
    </div>

    @if($errors->any())
        <div class="alert-err"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    @if($matieres->isEmpty())
        <div class="ct-card"><div class="empty-state"><i class="fa-solid fa-book-open"></i> Aucune matière ne vous est attribuée. Contactez l'administration.</div></div>
    @else
    <form method="POST" action="{{ $entree ? route('cahier.update', $entree) : route('cahier.store') }}" enctype="multipart/form-data">
        @csrf
        @if($entree) @method('PUT') @endif

        <div class="ct-card">
            <div class="ct-body">
                <div class="ct-grid2">
                    <div class="ct-field">
                        <label>Matière &amp; classe</label>
                        <select name="matiere_id" required>
                            <option value="">— Choisir —</option>
                            @foreach($matieresParClasse as $nomClasse => $liste)
                                <optgroup label="{{ $nomClasse }}">
                                    @foreach($liste as $m)
                                        <option value="{{ $m->id }}" {{ (string)$val('matiere_id') === (string)$m->id ? 'selected' : '' }}>{{ $m->nom }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="ct-field">
                        <label>Date du cours</label>
                        <input type="date" name="date_cours" value="{{ $val('date_cours') ? \Carbon\Carbon::parse($val('date_cours'))->format('Y-m-d') : now()->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="ct-field">
                    <label>Contenu du cours</label>
                    <textarea name="contenu" placeholder="Décrivez le contenu détaillé de la séance : notions abordées, activités, chapitre…" required>{{ $val('contenu') }}</textarea>
                </div>

                <div class="ct-field">
                    <label>Devoirs à la maison <span style="font-weight:400; color:var(--text-muted,#6b7280);">(facultatif)</span></label>
                    <textarea name="devoirs" placeholder="Exercices, lectures, préparation…">{{ $val('devoirs') }}</textarea>
                </div>

                <div class="ct-field" style="max-width:260px;">
                    <label>Date de remise des devoirs <span style="font-weight:400; color:var(--text-muted,#6b7280);">(facultatif)</span></label>
                    <input type="date" name="date_remise" value="{{ $val('date_remise') ? \Carbon\Carbon::parse($val('date_remise'))->format('Y-m-d') : '' }}">
                </div>

                <div class="ct-field">
                    <label>Support de cours / pièce jointe <span style="font-weight:400; color:var(--text-muted,#6b7280);">(facultatif — PDF, Word, PPT, Excel, image, ZIP · max 20 Mo)</span></label>
                    @if($entree && $entree->aUnFichier())
                        <div style="display:flex; align-items:center; gap:12px; background:var(--bg,#f9fafb); border:1px solid var(--border,#e5e7eb); border-radius:10px; padding:10px 14px; margin-bottom:8px; flex-wrap:wrap;">
                            <a href="{{ asset('storage/'.$entree->fichier) }}" target="_blank" style="color:var(--primary,#1a4fa0); font-weight:600; text-decoration:none; font-size:13px;">
                                <i class="fa-solid fa-paperclip"></i> {{ $entree->fichierNom() }}
                            </a>
                            <label style="display:inline-flex; align-items:center; gap:6px; font-size:12.5px; color:#b91c1c; margin-left:auto; cursor:pointer;">
                                <input type="checkbox" name="supprimer_fichier" value="1"> Supprimer ce fichier
                            </label>
                        </div>
                        <div style="font-size:12px; color:var(--text-muted,#6b7280); margin-bottom:6px;">Choisir un nouveau fichier le remplacera :</div>
                    @endif
                    <input type="file" name="fichier" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:6px;">
                    <a href="{{ route('cahier.index') }}" class="btn-ct ghost">Annuler</a>
                    <button type="submit" class="btn-ct"><i class="fa-solid fa-floppy-disk"></i> {{ $entree ? 'Enregistrer' : 'Ajouter l\'entrée' }}</button>
                </div>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection
