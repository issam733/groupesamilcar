@extends('admin.layouts.app')

@section('title', 'Emploi du temps — '.$classe->nom)
@section('page-title', 'Emploi du temps')
@section('page-subtitle', $classe->nom.' — '.$classe->niveau)

@section('extra-css')
<style>
    .timetable-wrapper { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .timetable-toolbar { padding:16px 20px; border-bottom:1px solid var(--border); background:#f7f9fd; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
    .legend { display:flex; gap:10px; flex-wrap:wrap; }
    .legend-item { display:flex; align-items:center; gap:6px; font-size:11px; color:var(--text-muted); }
    .legend-dot { width:10px; height:10px; border-radius:3px; }

    .timetable-scroll { overflow-x:auto; }
    .timetable { width:100%; border-collapse:collapse; min-width:900px; }
    .timetable th { padding:12px 8px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; text-align:center; background:#f7f9fd; border-bottom:2px solid var(--border); border-right:1px solid var(--border); }
    .timetable th:first-child { width:90px; }
    .timetable td { border:1px solid #f0f4fa; padding:0; height:64px; vertical-align:top; position:relative; }
    .timetable td.heure-col { background:#fafbff; text-align:center; font-size:11.5px; font-weight:600; color:var(--text-muted); vertical-align:middle; border-right:2px solid var(--border); }
    .timetable tr.pause-row td { background:repeating-linear-gradient(45deg,#fafbff,#fafbff 8px,#f0f4fa 8px,#f0f4fa 16px); height:32px; }
    .timetable tr.pause-row td.heure-col { background:#f0f4fa; font-style:italic; }

    .creneau-cell { width:100%; height:100%; cursor:pointer; padding:6px 8px; display:flex; flex-direction:column; justify-content:center; transition:background .15s; position:relative; }
    .creneau-cell:hover { background:#f7f9fd; }
    .creneau-cell.filled { border-left:3px solid var(--primary); }
    .creneau-matiere { font-size:11.5px; font-weight:700; color:var(--text); line-height:1.2; }
    .creneau-enseignant { font-size:10px; color:var(--text-muted); margin-top:2px; }
    .creneau-empty-icon { color:var(--border); font-size:14px; opacity:0; transition:opacity .15s; }
    .creneau-cell:hover .creneau-empty-icon { opacity:1; }
    .creneau-remove { position:absolute; top:3px; right:3px; width:18px; height:18px; border-radius:5px; background:#fef2f2; color:var(--danger); font-size:10px; display:flex; align-items:center; justify-content:center; cursor:pointer; opacity:0; transition:opacity .15s; }
    .creneau-cell:hover .creneau-remove { opacity:1; }

    /* Couleurs par matière (cycle) */
    .mat-color-0 { border-left-color:#1a4fa0 !important; } .mat-color-0 .creneau-matiere { color:#1a4fa0; }
    .mat-color-1 { border-left-color:#0d9488 !important; } .mat-color-1 .creneau-matiere { color:#0d9488; }
    .mat-color-2 { border-left-color:#d97706 !important; } .mat-color-2 .creneau-matiere { color:#d97706; }
    .mat-color-3 { border-left-color:#7c5cbf !important; } .mat-color-3 .creneau-matiere { color:#7c5cbf; }
    .mat-color-4 { border-left-color:#d63384 !important; } .mat-color-4 .creneau-matiere { color:#d63384; }
    .mat-color-5 { border-left-color:#0891b2 !important; } .mat-color-5 .creneau-matiere { color:#0891b2; }
    .mat-color-6 { border-left-color:#dc2626 !important; } .mat-color-6 .creneau-matiere { color:#dc2626; }

    /* Modal */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
    .modal-overlay.show { display:flex; }
    .modal-box { background:#fff; border-radius:16px; padding:0; max-width:420px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden; }
    .modal-header { padding:18px 24px; background:#f7f9fd; border-bottom:1px solid var(--border); }
    .modal-header h4 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
    .modal-header p { font-size:12px; color:var(--text-muted); margin-top:3px; }
    .modal-body { padding:24px; }
    .modal-field { margin-bottom:16px; }
    .modal-field label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .modal-field select { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; }
    .modal-field select:focus { border-color:var(--primary); }
    .modal-footer { padding:16px 24px; border-top:1px solid var(--border); display:flex; justify-content:space-between; gap:10px; background:#f7f9fd; }
    .btn-am { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all .2s; text-decoration:none; }
    .btn-am.primary { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-am.secondary { background:var(--bg); color:var(--text); border:1.5px solid var(--border); }
    .btn-am.danger { background:#fef2f2; color:var(--danger); border:1px solid #fecaca; }
</style>
@endsection

@section('content')

<div class="timetable-wrapper">
    <div class="timetable-toolbar">
        <div class="legend" id="legend"></div>
        <a href="{{ route('admin.emplois.index') }}" class="btn-am secondary">
            <i class="fa-solid fa-arrow-left"></i> Autres classes
        </a>
    </div>

    <div class="timetable-scroll">
        <table class="timetable">
            <thead>
                <tr>
                    <th>Heure</th>
                    @foreach($jours as $jour)
                    <th>{{ $jour }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($creneaux as $c)
                <tr class="{{ !empty($c['pause']) ? 'pause-row' : '' }}">
                    <td class="heure-col">
                        @if(!empty($c['pause']))
                            🍽️
                        @else
                            {{ $c['debut'] }}<br><span style="opacity:.6;">{{ $c['fin'] }}</span>
                        @endif
                    </td>
                    @if(!empty($c['pause']))
                        <td colspan="{{ count($jours) }}" style="text-align:center; font-size:11px; color:var(--text-muted); font-style:italic;">Pause déjeuner</td>
                    @else
                        @foreach($jours as $jour)
                        @php $creneauExistant = $grille[$jour][$c['debut']] ?? null; @endphp
                        <td>
                            <div class="creneau-cell {{ $creneauExistant ? 'filled' : '' }}"
                                 data-jour="{{ $jour }}"
                                 data-debut="{{ $c['debut'] }}"
                                 data-fin="{{ $c['fin'] }}"
                                 data-id="{{ $creneauExistant->id ?? '' }}"
                                 data-matiere-id="{{ $creneauExistant->matiere_id ?? '' }}"
                                 data-enseignant-id="{{ $creneauExistant->enseignant_id ?? '' }}"
                                 onclick="openCreneauModal(this)">
                                @if($creneauExistant)
                                    <div class="creneau-matiere">{{ $creneauExistant->matiere->nom ?? '—' }}</div>
                                    <div class="creneau-enseignant">{{ $creneauExistant->enseignant->prenom ?? '' }} {{ $creneauExistant->enseignant->nom ?? '' }}</div>
                                    <div class="creneau-remove" onclick="event.stopPropagation(); removeCreneau({{ $creneauExistant->id }}, this)">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                @else
                                    <i class="fa-solid fa-plus creneau-empty-icon"></i>
                                @endif
                            </div>
                        </td>
                        @endforeach
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal ajout/modif créneau -->
<div class="modal-overlay" id="creneauModal">
    <div class="modal-box">
        <div class="modal-header">
            <h4 id="modalTitle">Ajouter un cours</h4>
            <p id="modalSubtitle"></p>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label>Matière</label>
                <select id="modalMatiere" onchange="updateEnseignantSuggestion()">
                    <option value="">— Choisir une matière —</option>
                    @foreach($classe->matieres as $m)
                    <option value="{{ $m->id }}" data-enseignant="{{ $m->enseignant_id }}">{{ $m->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-field">
                <label>Enseignant</label>
                <select id="modalEnseignant">
                    <option value="">— Aucun —</option>
                    @foreach($classe->matieres->pluck('enseignant')->filter()->unique('id') as $ens)
                    <option value="{{ $ens->id }}">{{ $ens->prenom }} {{ $ens->nom }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-am secondary" onclick="closeCreneauModal()">Annuler</button>
            <button class="btn-am primary" onclick="saveCreneau()">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const classeId = {{ $classe->id }};
let currentCell = null;

// Légende des matières avec couleurs
const matieresColors = {};
let colorIdx = 0;
@foreach($classe->matieres as $m)
matieresColors[{{ $m->id }}] = colorIdx++ % 7;
@endforeach

function renderLegend() {
    const legend = document.getElementById('legend');
    let html = '';
    @foreach($classe->matieres->take(8) as $m)
    html += `<div class="legend-item"><div class="legend-dot mat-color-${matieresColors[{{ $m->id }}]}" style="background:currentColor;"></div>{{ $m->nom }}</div>`;
    @endforeach
    legend.innerHTML = html;
}
renderLegend();

function openCreneauModal(cell) {
    currentCell = cell;
    const jour   = cell.dataset.jour;
    const debut  = cell.dataset.debut;
    const fin    = cell.dataset.fin;
    const matId  = cell.dataset.matiereId;
    const ensId  = cell.dataset.enseignantId;

    document.getElementById('modalTitle').textContent = matId ? 'Modifier le cours' : 'Ajouter un cours';
    document.getElementById('modalSubtitle').textContent = `${jour} · ${debut} - ${fin}`;
    document.getElementById('modalMatiere').value = matId || '';
    document.getElementById('modalEnseignant').value = ensId || '';

    document.getElementById('creneauModal').classList.add('show');
}

function closeCreneauModal() {
    document.getElementById('creneauModal').classList.remove('show');
    currentCell = null;
}

function updateEnseignantSuggestion() {
    const select = document.getElementById('modalMatiere');
    const opt = select.options[select.selectedIndex];
    const ensId = opt?.dataset?.enseignant;
    if (ensId) document.getElementById('modalEnseignant').value = ensId;
}

function saveCreneau() {
    if (!currentCell) return;
    const matiereId = document.getElementById('modalMatiere').value;
    if (!matiereId) { alert('Veuillez choisir une matière.'); return; }

    const enseignantId = document.getElementById('modalEnseignant').value;

    fetch('{{ route("admin.emplois.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            classe_id: classeId,
            matiere_id: matiereId,
            enseignant_id: enseignantId || null,
            jour: currentCell.dataset.jour,
            heure_debut: currentCell.dataset.debut,
            heure_fin: currentCell.dataset.fin,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(() => alert('Erreur lors de l\'enregistrement.'));
}

function removeCreneau(id, el) {
    if (!confirm('Supprimer ce cours de l\'emploi du temps ?')) return;

    fetch(`{{ url('admin/emplois') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}
</script>
@endsection
