@extends($layout)

@section('title', 'Nouveau message')
@section('page-title', 'Nouveau message')
@section('page-subtitle', 'Choisissez un ou plusieurs destinataires')

@section('extra-css')
@include('messagerie._styles')
<style>
    .audience-row { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
    .audience-opt { display:flex; align-items:center; gap:8px; padding:8px 14px; border:1.5px solid var(--border,#e5e7eb); border-radius:9px; font-size:13px; cursor:pointer; background:var(--bg,#f9fafb); }
    .audience-opt input { accent-color:var(--primary,#1a4fa0); }
    .toggle-people { display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:600; color:var(--primary,#1a4fa0); margin-bottom:6px; user-select:none; }
</style>
@endsection

@section('content')
@php
    $roleInfo = [
        'admin'      => ['Administration', '#1a4fa0', 'fa-user-shield'],
        'enseignant' => ['Enseignants', '#6d28d9', 'fa-chalkboard-user'],
        'parent'     => ['Parents', '#b45309', 'fa-user-group'],
        'eleve'      => ['Élèves', '#0d9488', 'fa-graduation-cap'],
    ];
    $ordre = ['admin', 'enseignant', 'parent', 'eleve'];
    $peutDiffuserClasse = in_array($user->role, ['admin','enseignant']) && $classes->count() > 0;
@endphp

<div class="msg-wrap">
    <div class="msg-toolbar">
        <a href="{{ route('messagerie.index') }}" class="btn-msg ghost"><i class="fa-solid fa-arrow-left"></i> Boîte de réception</a>
    </div>

    @if($errors->any())
        <div class="alert-err"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('messagerie.envoyer') }}">
        @csrf

        {{-- ─── Diffusion par classe (admin / enseignant) ─── --}}
        @if($peutDiffuserClasse)
        <div class="compose-card" style="margin-bottom:16px;">
            <div style="font-size:13px; font-weight:700; margin-bottom:12px;"><i class="fa-solid fa-bullhorn" style="color:var(--primary,#1a4fa0);"></i> Diffusion par classe</div>

            <label class="recip-chip" style="margin-bottom:10px; display:inline-flex;">
                <input type="checkbox" name="toutes_classes" value="1" id="toutesClasses" onchange="onToutesClasses(this)">
                <strong>Toutes les classes</strong>
            </label>

            <div class="recip-grid" id="classeGrid">
                @foreach($classes as $c)
                    <label class="recip-chip" data-classe-chip>
                        <input type="checkbox" name="classe_ids[]" value="{{ $c->id }}"
                               onchange="this.closest('.recip-chip').classList.toggle('checked', this.checked)">
                        {{ $c->nom }}
                    </label>
                @endforeach
            </div>

            <div style="font-size:12px; color:var(--text-muted,#6b7280); margin-top:14px; font-weight:600;">Envoyer à :</div>
            <div class="audience-row">
                <label class="audience-opt"><input type="radio" name="audience" value="tous" checked> Élèves + parents</label>
                <label class="audience-opt"><input type="radio" name="audience" value="eleves"> Élèves seulement</label>
                <label class="audience-opt"><input type="radio" name="audience" value="parents"> Parents seulement</label>
            </div>
        </div>
        @endif

        {{-- ─── Sélection individuelle ─── --}}
        <div class="compose-card">
            @if($peutDiffuserClasse)
                <div class="toggle-people" onclick="togglePeople()">
                    <i class="fa-solid fa-chevron-right" id="peopleChevron"></i> Ou choisir des personnes précises (facultatif)
                </div>
                <div id="peopleBlock" style="display:none; margin-top:10px;">
            @else
                <div style="font-size:13px; font-weight:700; margin-bottom:14px;"><i class="fa-solid fa-users"></i> Destinataires <span style="font-weight:400; color:var(--text-muted,#6b7280); font-size:12px;">— cochez une ou plusieurs personnes</span></div>
                <div>
            @endif

                @if($destinataires->isEmpty())
                    <div class="empty-state" style="padding:24px;"><i class="fa-solid fa-user-slash"></i> Aucun destinataire individuel disponible.</div>
                @else
                @foreach($ordre as $role)
                    @if($destinataires->has($role))
                        @php $ri = $roleInfo[$role]; @endphp
                        <div class="recip-group">
                            <div class="recip-group-title">
                                <i class="fa-solid {{ $ri[2] }}" style="color:{{ $ri[1] }};"></i> {{ $ri[0] }}
                                <a onclick="toggleGroup('{{ $role }}', true)">tout cocher</a>
                                <a onclick="toggleGroup('{{ $role }}', false)" style="color:var(--text-muted,#6b7280);">tout décocher</a>
                            </div>
                            <div class="recip-grid">
                                @foreach($destinataires->get($role) as $d)
                                    <label class="recip-chip" data-role="{{ $role }}">
                                        <input type="checkbox" name="destinataires[]" value="{{ $d->id }}"
                                               onchange="this.closest('.recip-chip').classList.toggle('checked', this.checked)">
                                        {{ $d->nomComplet() }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
                @endif
                </div>

            <textarea name="corps" class="compose-textarea" placeholder="Votre message…" required>{{ old('corps') }}</textarea>

            <div style="display:flex; justify-content:flex-end; margin-top:14px;">
                <button type="submit" class="btn-msg"><i class="fa-solid fa-paper-plane"></i> Envoyer</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function toggleGroup(role, state) {
        document.querySelectorAll('.recip-chip[data-role="'+role+'"]').forEach(function(chip){
            var cb = chip.querySelector('input[type=checkbox]');
            cb.checked = state;
            chip.classList.toggle('checked', state);
        });
    }
    function onToutesClasses(cb) {
        // si "toutes les classes" est coché, on désactive la sélection de classes individuelles
        document.querySelectorAll('#classeGrid input[type=checkbox]').forEach(function(c){
            c.disabled = cb.checked;
            if (cb.checked) { c.checked = false; c.closest('.recip-chip').classList.remove('checked'); }
        });
        document.getElementById('classeGrid').style.opacity = cb.checked ? '.5' : '1';
    }
    function togglePeople() {
        var b = document.getElementById('peopleBlock');
        var ch = document.getElementById('peopleChevron');
        var open = b.style.display === 'none';
        b.style.display = open ? 'block' : 'none';
        ch.className = open ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right';
    }
</script>
@endsection
