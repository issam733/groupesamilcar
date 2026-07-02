@extends($layout)

@section('title', 'Historique des absences')
@section('page-title', 'Absences — Historique')
@section('page-subtitle', 'Les absences enregistrées')

@section('extra-css')
<style>
    .abs-card { background:var(--card,#fff); border:1px solid var(--border,#e5e7eb); border-radius:14px; padding:18px 20px; box-shadow:var(--shadow-sm,0 2px 10px rgba(0,0,0,.04)); }
    .abs-field label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted,#6b7280); margin-bottom:5px; }
    .abs-field select { padding:9px 12px; border:1.5px solid var(--border,#e5e7eb); border-radius:9px; font-family:inherit; font-size:13.5px; background:var(--bg,#fafafa); color:var(--text,#1e2238); min-width:170px; }
    .btn-abs { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px; background:var(--primary,#1a4fa0); color:#fff !important; text-decoration:none; border:none; font-size:13.5px; font-weight:600; cursor:pointer; font-family:inherit; }
    .btn-abs.ghost { background:var(--bg,#f4f6fb); color:var(--text,#1e2238) !important; border:1.5px solid var(--border,#e5e7eb); }
    .abs-table { width:100%; border-collapse:collapse; }
    .abs-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted,#6b7280); padding:10px 12px; border-bottom:2px solid var(--border,#e5e7eb); }
    .abs-table td { padding:10px 12px; border-bottom:1px solid var(--border,#eef1f5); font-size:13.5px; }
    .badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; }
    .badge.just { background:#ecfdf5; color:#0f766e; }
    .badge.nonjust { background:#fef2f2; color:#b91c1c; }
    .empty-state { text-align:center; padding:50px 20px; color:var(--text-muted,#6b7280); }
    .empty-state i { font-size:44px; opacity:.3; display:block; margin-bottom:14px; }
</style>
@endsection

@section('content')

<div class="abs-card" style="margin-bottom:18px;">
    <form method="GET" action="{{ route('absences.historique') }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; justify-content:space-between;">
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <div class="abs-field">
                <label>Classe</label>
                <select name="classe_id">
                    <option value="">Toutes mes classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string)$classeId === (string)$c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="abs-field">
                <label>Statut</label>
                <select name="statut">
                    <option value="">Tous</option>
                    <option value="justifie" {{ $statut === 'justifie' ? 'selected' : '' }}>Justifiées</option>
                    <option value="non" {{ $statut === 'non' ? 'selected' : '' }}>Non justifiées</option>
                </select>
            </div>
            <button class="btn-abs" type="submit"><i class="fa-solid fa-filter"></i> Filtrer</button>
        </div>
        <a href="{{ route('absences.index') }}" class="btn-abs ghost"><i class="fa-solid fa-clipboard-check"></i> Faire l'appel</a>
    </form>
</div>

<div class="abs-card">
    @if($absences->isEmpty())
        <div class="empty-state"><i class="fa-regular fa-calendar-check"></i> Aucune absence enregistrée pour ce filtre.</div>
    @else
    <table class="abs-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Élève</th>
                <th>Classe</th>
                <th>Statut</th>
                <th>Motif</th>
                <th>Saisi par</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absences as $a)
                <tr>
                    <td>{{ $a->date?->format('d/m/Y') }}</td>
                    <td style="font-weight:600;">{{ $a->eleve?->nom }} {{ $a->eleve?->prenom }}</td>
                    <td>{{ $a->eleve?->classe?->nom ?? '—' }}</td>
                    <td>
                        @if($a->justifie)<span class="badge just">Justifiée</span>
                        @else<span class="badge nonjust">Non justifiée</span>@endif
                    </td>
                    <td style="color:var(--text-muted,#6b7280);">{{ $a->motif ?: '—' }}</td>
                    <td style="color:var(--text-muted,#6b7280); font-size:12.5px;">{{ $a->saiseur?->prenom }} {{ $a->saiseur?->nom }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
