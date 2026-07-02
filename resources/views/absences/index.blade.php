@extends($layout)

@section('title', 'Absences')
@section('page-title', 'Absences — Appel')
@section('page-subtitle', 'Sélectionnez une classe et une date, puis cochez les absents')

@section('extra-css')
<style>
    .abs-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; margin-bottom:18px; }
    .abs-card { background:var(--card,#fff); border:1px solid var(--border,#e5e7eb); border-radius:14px; padding:18px 20px; box-shadow:var(--shadow-sm,0 2px 10px rgba(0,0,0,.04)); }
    .abs-field label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted,#6b7280); margin-bottom:5px; }
    .abs-field select, .abs-field input[type=date] { padding:9px 12px; border:1.5px solid var(--border,#e5e7eb); border-radius:9px; font-family:inherit; font-size:13.5px; background:var(--bg,#fafafa); color:var(--text,#1e2238); min-width:180px; }
    .btn-abs { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px; background:var(--primary,#1a4fa0); color:#fff !important; text-decoration:none; border:none; font-size:13.5px; font-weight:600; cursor:pointer; font-family:inherit; }
    .btn-abs.ghost { background:var(--bg,#f4f6fb); color:var(--text,#1e2238) !important; border:1.5px solid var(--border,#e5e7eb); }
    .abs-table { width:100%; border-collapse:collapse; }
    .abs-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted,#6b7280); padding:10px 12px; border-bottom:2px solid var(--border,#e5e7eb); }
    .abs-table td { padding:10px 12px; border-bottom:1px solid var(--border,#eef1f5); font-size:13.5px; vertical-align:middle; }
    .abs-row.is-absent { background:#fef2f2; }
    .abs-el-nom { font-weight:600; color:var(--text,#1e2238); }
    .abs-el-mat { font-size:11.5px; color:var(--text-muted,#6b7280); }
    .switch { display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:600; }
    .switch input { width:18px; height:18px; accent-color:#ef4444; }
    .motif-input { width:100%; max-width:280px; padding:7px 10px; border:1.5px solid var(--border,#e5e7eb); border-radius:8px; font-family:inherit; font-size:13px; background:var(--bg,#fafafa); }
    .motif-input:disabled { opacity:.4; }
    .just-check { accent-color:#0f766e; width:16px; height:16px; }
    .alert-ok { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; }
    .empty-state { text-align:center; padding:50px 20px; color:var(--text-muted,#6b7280); }
    .empty-state i { font-size:44px; opacity:.3; display:block; margin-bottom:14px; }
</style>
@endsection

@section('content')

@if(session('success'))
    <div class="alert-ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

{{-- Sélecteur classe + date --}}
<div class="abs-card" style="margin-bottom:18px;">
    <form method="GET" action="{{ route('absences.index') }}" class="abs-bar" style="margin:0;">
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <div class="abs-field">
                <label>Classe</label>
                <select name="classe_id" required>
                    <option value="">— Choisir —</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string)$classeId === (string)$c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="abs-field">
                <label>Date</label>
                <input type="date" name="date" value="{{ $date }}" required>
            </div>
            <button class="btn-abs" type="submit"><i class="fa-solid fa-clipboard-check"></i> Charger l'appel</button>
        </div>
        <a href="{{ route('absences.historique') }}" class="btn-abs ghost"><i class="fa-solid fa-clock-rotate-left"></i> Historique</a>
    </form>
</div>

{{-- Feuille d'appel --}}
@if($classeSel)
    <div class="abs-card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
            <h3 style="margin:0;"><i class="fa-solid fa-users" style="color:var(--primary,#1a4fa0);"></i> {{ $classeSel->nom }} — {{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }}</h3>
            <span style="font-size:12.5px; color:var(--text-muted,#6b7280);">{{ $eleves->count() }} élève(s)</span>
        </div>

        @if($eleves->isEmpty())
            <div class="empty-state"><i class="fa-solid fa-user-slash"></i> Aucun élève actif dans cette classe.</div>
        @else
        <form method="POST" action="{{ route('absences.enregistrer') }}">
            @csrf
            <input type="hidden" name="classe_id" value="{{ $classeSel->id }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <table class="abs-table">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th style="width:130px;">Absent</th>
                        <th style="width:110px;">Justifié</th>
                        <th>Motif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eleves as $el)
                        @php $abs = $absencesJour->get($el->id); $estAbsent = (bool) $abs; @endphp
                        <tr class="abs-row {{ $estAbsent ? 'is-absent' : '' }}" data-row>
                            <td>
                                <div class="abs-el-nom">{{ $el->nom }} {{ $el->prenom }}</div>
                                <div class="abs-el-mat">{{ $el->matricule }}</div>
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="absents[{{ $el->id }}]" value="1" {{ $estAbsent ? 'checked' : '' }} onchange="toggleRow(this)">
                                    <span>Absent</span>
                                </label>
                            </td>
                            <td>
                                <input type="checkbox" class="just-check" name="justifie[{{ $el->id }}]" value="1" {{ $abs && $abs->justifie ? 'checked' : '' }} {{ $estAbsent ? '' : 'disabled' }}>
                            </td>
                            <td>
                                <input type="text" class="motif-input" name="motif[{{ $el->id }}]" value="{{ $abs->motif ?? '' }}" placeholder="Motif (facultatif)" {{ $estAbsent ? '' : 'disabled' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                <button type="submit" class="btn-abs"><i class="fa-solid fa-floppy-disk"></i> Enregistrer l'appel</button>
            </div>
        </form>
        @endif
    </div>
@else
    <div class="abs-card">
        <div class="empty-state"><i class="fa-solid fa-clipboard-list"></i> Choisissez une classe et une date, puis cliquez sur « Charger l'appel ».</div>
    </div>
@endif

@endsection

@section('scripts')
<script>
    function toggleRow(cb) {
        var row = cb.closest('[data-row]');
        row.classList.toggle('is-absent', cb.checked);
        row.querySelector('.just-check').disabled = !cb.checked;
        row.querySelector('.motif-input').disabled = !cb.checked;
        if (!cb.checked) { row.querySelector('.just-check').checked = false; row.querySelector('.motif-input').value = ''; }
    }
</script>
@endsection
