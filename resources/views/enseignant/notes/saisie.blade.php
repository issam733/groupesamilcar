@extends('enseignant.layouts.app')

@section('title', 'Saisie — ' . $matiere->nom)
@section('page-title', 'Saisie des notes')
@section('page-subtitle', $matiere->nom . ' · ' . $classe->nom . ' · Trimestre ' . $trimestre)

@section('extra-css')
<style>
    .note-input { width:64px; padding:7px 8px; border:1.5px solid var(--border); border-radius:8px; text-align:center; font-size:13.5px; font-family:'Inter',sans-serif; outline:none; }
    .note-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(109,40,217,.12); }
    .note-input.saved { border-color:var(--success); background:#f0fdf8; }
    .trim-tabs { display:flex; gap:8px; }
    .save-bar { position:sticky; bottom:0; background:var(--card); border-top:1px solid var(--border); padding:14px 24px; margin:24px -28px -28px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 -4px 20px rgba(0,0,0,.05); }
</style>
@endsection

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
    <a href="{{ route('enseignant.notes.index', ['trimestre' => $trimestre]) }}" class="btn-am secondary"><i class="fa-solid fa-arrow-left"></i> Retour</a>
    <div class="trim-tabs">
        @for($t = 1; $t <= 3; $t++)
            <a href="{{ route('enseignant.notes.saisie', [$classe->id, $matiere->id, $t]) }}"
               class="btn-am {{ $trimestre == $t ? 'primary' : 'secondary' }} sm">T{{ $t }}</a>
        @endfor
    </div>
</div>

@if($classe->eleves->isEmpty())
    <div class="page-card"><div class="empty-state"><i class="fa-solid fa-users"></i> Aucun élève actif dans cette classe.</div></div>
@else
<div class="page-card" style="padding:0; overflow:hidden;">
    <div style="padding:18px 24px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;"><i class="fa-solid fa-pen-to-square" style="color:var(--primary);"></i> {{ $matiere->nom }} — {{ $classe->nom }}</h3>
        <span class="badge violet">{{ $classe->eleves->count() }} élèves</span>
    </div>

    <div style="overflow-x:auto;">
        <table class="data-table" id="notesTable">
            <thead>
                <tr>
                    <th style="min-width:200px;">Élève</th>
                    @foreach($types as $key => $label)
                        <th style="text-align:center;">{{ $label }}<br><span style="font-weight:400; text-transform:none;">/ 20</span></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($classe->eleves as $eleve)
                <tr>
                    <td style="font-weight:600;">
                        {{ $eleve->nom }} {{ $eleve->prenom }}
                        <div style="font-size:11px; color:var(--text-muted); font-weight:400;">{{ $eleve->matricule }}</div>
                    </td>
                    @foreach($types as $key => $label)
                        @php $note = $notesExistantes->get("{$eleve->id}_{$key}"); @endphp
                        <td style="text-align:center;">
                            <input type="number" class="note-input" min="0" max="20" step="0.25"
                                   data-eleve="{{ $eleve->id }}" data-type="{{ $key }}"
                                   value="{{ $note->valeur ?? '' }}" placeholder="–">
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="save-bar">
        <span id="saveStatus" style="font-size:13px; color:var(--text-muted);"><i class="fa-solid fa-circle-info"></i> Saisissez les notes, puis enregistrez.</span>
        <button class="btn-am primary" id="saveBtn"><i class="fa-solid fa-floppy-disk"></i> Enregistrer toutes les notes</button>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
const SAVE_URL  = "{{ route('enseignant.notes.sauvegarder') }}";
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const CLASSE_ID = {{ $classe->id }};
const MATIERE_ID= {{ $matiere->id }};
const TRIMESTRE = {{ $trimestre }};
const TYPES     = @json(array_keys($types));

document.getElementById('saveBtn').addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    const status = document.getElementById('saveStatus');
    status.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement…';

    let totalSaved = 0;
    let hadError = false;

    // On envoie une requête par type de note (devoir / contrôle / examen)
    for (const type of TYPES) {
        const inputs = document.querySelectorAll(`.note-input[data-type="${type}"]`);
        const notes = [];
        inputs.forEach(inp => {
            notes.push({ eleve_id: parseInt(inp.dataset.eleve), valeur: inp.value === '' ? null : inp.value });
        });

        try {
            const res = await fetch(SAVE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ classe_id: CLASSE_ID, matiere_id: MATIERE_ID, trimestre: TRIMESTRE, type, notes }),
            });
            const data = await res.json();
            if (data.success) {
                totalSaved += data.count;
                inputs.forEach(inp => { if (inp.value !== '') inp.classList.add('saved'); });
            } else {
                hadError = true;
                status.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:var(--danger)"></i> ' + (data.message || 'Erreur');
            }
        } catch (e) {
            hadError = true;
            status.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:var(--danger)"></i> Erreur de connexion.';
        }
    }

    btn.disabled = false;
    if (!hadError) {
        status.innerHTML = `<i class="fa-solid fa-circle-check" style="color:var(--success)"></i> ${totalSaved} note(s) enregistrée(s).`;
    }
});
</script>
@endsection
