@extends('admin.layouts.app')

@section('title', 'Saisie des notes — '.$classe->nom)
@section('page-title', 'Saisie des notes')
@section('page-subtitle', $classe->nom.' — Trimestre '.$trimestre)

@section('extra-css')
<style>
    .saisie-toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px 20px; margin-bottom:18px; display:flex; align-items:center; gap:16px; flex-wrap:wrap; box-shadow:var(--shadow-sm); }
    .toolbar-select { padding:9px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; cursor:pointer; min-width:180px; }
    .toolbar-select:focus { border-color:var(--primary); }

    .save-status { margin-left:auto; font-size:12px; color:var(--text-muted); display:flex; align-items:center; gap:6px; }
    .save-status.saving { color:var(--warning); }
    .save-status.saved { color:var(--success); }

    .table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .notes-table { width:100%; border-collapse:collapse; }
    .notes-table thead tr { background:#f7f9fd; border-bottom:1px solid var(--border); }
    .notes-table th { padding:12px 16px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; }
    .notes-table th.note-col { text-align:center; width:110px; }
    .notes-table td { padding:10px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #f0f4fa; vertical-align:middle; }
    .notes-table tr:last-child td { border-bottom:none; }
    .notes-table tr:hover td { background:#fafbff; }

    .eleve-cell { display:flex; align-items:center; gap:10px; }
    .eleve-avatar-sm { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .eleve-nom-sm { font-weight:600; }
    .eleve-mat-sm { font-size:11px; color:var(--text-muted); }

    .note-input { width:70px; padding:7px 10px; border:1.5px solid var(--border); border-radius:8px; font-size:14px; font-weight:600; text-align:center; color:var(--text); background:#fafbff; outline:none; transition:all .15s; font-family:'Inter',sans-serif; }
    .note-input:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(26,79,160,.08); }
    .note-input.modified { border-color:var(--warning); background:#fffbeb; }
    .note-input.saved-flash { border-color:var(--success); background:#ecfdf5; }
    .note-input.low { color:var(--danger); }
    .note-input.high { color:var(--success); }

    .moyenne-cell { font-weight:700; text-align:center; }

    .empty-classe { text-align:center; padding:60px 20px; color:var(--text-muted); }

    .bottom-bar { padding:16px 20px; border-top:1px solid var(--border); background:#f7f9fd; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:9px; font-size:13.5px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
</style>
@endsection

@section('content')

<div class="saisie-toolbar">
    <select class="toolbar-select" id="matiereSelect" onchange="changeMatiere()">
        @foreach($classe->matieres as $matiere)
        <option value="{{ $matiere->id }}">{{ $matiere->nom }} (coef {{ $matiere->coefficient }})</option>
        @endforeach
    </select>

    <select class="toolbar-select" id="typeSelect" onchange="reloadGrid()">
        @foreach($types as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </select>

    <div style="font-size:12px; color:var(--text-muted);">
        <i class="fa-solid fa-graduation-cap" style="margin-right:5px;"></i> Trimestre {{ $trimestre }}
    </div>

    <div class="save-status" id="saveStatus"></div>
</div>

<div class="table-card">
    @if($classe->eleves->count())
    <table class="notes-table">
        <thead>
            <tr>
                <th>Élève</th>
                <th class="note-col">Note / 20</th>
            </tr>
        </thead>
        <tbody id="notesBody">
            @foreach($classe->eleves as $eleve)
            <tr>
                <td>
                    <div class="eleve-cell">
                        <div class="eleve-avatar-sm">{{ strtoupper(substr($eleve->prenom,0,1).substr($eleve->nom,0,1)) }}</div>
                        <div>
                            <div class="eleve-nom-sm">{{ $eleve->prenom }} {{ $eleve->nom }}</div>
                            <div class="eleve-mat-sm">{{ $eleve->matricule }}</div>
                        </div>
                    </div>
                </td>
                <td class="note-col">
                    <input type="number" class="note-input" min="0" max="20" step="0.25"
                           data-eleve-id="{{ $eleve->id }}"
                           id="note_{{ $eleve->id }}"
                           oninput="markModified(this)"
                           onkeydown="if(event.key==='Enter') focusNext({{ $eleve->id }})">
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-classe">
        <i class="fa-solid fa-users-slash" style="font-size:40px; opacity:.3; display:block; margin-bottom:14px;"></i>
        Aucun élève dans cette classe.
    </div>
    @endif

    <div class="bottom-bar">
        <a href="{{ route('admin.notes.index') }}" class="btn-am secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
        <button class="btn-am primary" onclick="saveAll()">
            <i class="fa-solid fa-floppy-disk"></i> Enregistrer toutes les notes
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const classeId    = {{ $classe->id }};
const trimestre   = {{ $trimestre }};
const elevesIds   = @json($classe->eleves->pluck('id'));
const notesExistantes = @json($notesExistantes->map(fn($g) => $g->first()->valeur ?? null));

let currentMatiereId = document.getElementById('matiereSelect').value;
let currentType = document.getElementById('typeSelect').value;

function loadNotesForCurrent() {
    elevesIds.forEach(id => {
        const key = `${id}_${currentMatiereId}_${currentType}`;
        const input = document.getElementById('note_' + id);
        if (input) {
            const val = notesExistantes[key] ?? '';
            input.value = val;
            input.classList.remove('modified', 'saved-flash');
            updateInputColor(input);
        }
    });
}

function updateInputColor(input) {
    input.classList.remove('low', 'high');
    const val = parseFloat(input.value);
    if (!isNaN(val)) {
        if (val < 10) input.classList.add('low');
        else if (val >= 16) input.classList.add('high');
    }
}

function markModified(input) {
    input.classList.add('modified');
    input.classList.remove('saved-flash');
    updateInputColor(input);
}

function focusNext(currentId) {
    const idx = elevesIds.indexOf(currentId);
    if (idx >= 0 && idx < elevesIds.length - 1) {
        document.getElementById('note_' + elevesIds[idx + 1])?.focus();
    }
}

function changeMatiere() {
    currentMatiereId = document.getElementById('matiereSelect').value;
    loadNotesForCurrent();
}

function reloadGrid() {
    currentType = document.getElementById('typeSelect').value;
    loadNotesForCurrent();
}

function saveAll() {
    const notes = elevesIds.map(id => {
        const input = document.getElementById('note_' + id);
        return { eleve_id: id, valeur: input.value !== '' ? parseFloat(input.value) : null };
    });

    const status = document.getElementById('saveStatus');
    status.className = 'save-status saving';
    status.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement…';

    fetch('{{ route("admin.notes.sauvegarder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            classe_id: classeId,
            matiere_id: currentMatiereId,
            trimestre: trimestre,
            type: currentType,
            notes: notes,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            status.className = 'save-status saved';
            status.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${data.count} note(s) enregistrée(s)`;
            document.querySelectorAll('.note-input.modified').forEach(i => {
                i.classList.remove('modified');
                i.classList.add('saved-flash');
            });
            setTimeout(() => { status.innerHTML = ''; }, 3000);
        }
    })
    .catch(() => {
        status.className = 'save-status';
        status.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="color:var(--danger);"></i> Erreur';
    });
}

window.addEventListener('DOMContentLoaded', loadNotesForCurrent);
</script>
@endsection
