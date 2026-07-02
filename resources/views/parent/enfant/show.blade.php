@extends('parent.layouts.app')

@section('title', $eleve->prenom.' '.$eleve->nom)
@section('page-title', $eleve->prenom.' '.$eleve->nom)
@section('page-subtitle', $eleve->classe->nom ?? 'Aucune classe')

@section('extra-css')
<style>
    .trim-toggle { display:flex; gap:8px; margin-bottom:20px; }
    .trim-btn { padding:9px 18px; border-radius:9px; border:1.5px solid var(--border); background:#fafbff; font-size:13px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all .2s; }
    .trim-btn.active { background:#d97706; color:#fff; border-color:#d97706; }

    .summary-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px; }
    @media(max-width:700px) { .summary-grid { grid-template-columns:1fr; } }
    .summary-box { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px; text-align:center; box-shadow:var(--shadow-sm); }
    .summary-val { font-size:30px; font-weight:800; color:#d97706; }
    .summary-lbl { font-size:12px; color:var(--text-muted); margin-top:4px; }

    .table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); margin-bottom:20px; }
    .table-card-header { padding:16px 20px; border-bottom:1px solid var(--border); background:#f7f9fd; }
    .table-card-header h5 { font-size:14px; font-weight:700; color:var(--text); margin:0; }

    .notes-table { width:100%; border-collapse:collapse; }
    .notes-table th { padding:11px 16px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; text-align:left; border-bottom:1px solid var(--border); }
    .notes-table td { padding:12px 16px; font-size:13px; color:var(--text); border-bottom:1px solid #f0f4fa; }
    .notes-table tr:last-child td { border-bottom:none; }

    .note-pill { font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px; }
    .note-pill.good { background:#ecfdf5; color:var(--success); }
    .note-pill.mid  { background:#fffbeb; color:var(--warning); }
    .note-pill.low  { background:#fef2f2; color:var(--danger); }
    .note-pill.none { background:#f1f5f9; color:var(--text-muted); }

    .absence-badge { font-size:10.5px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .absence-badge.oui { background:#ecfdf5; color:var(--success); }
    .absence-badge.non { background:#fef2f2; color:var(--danger); }
</style>
@endsection

@section('content')

<div class="trim-toggle">
    @for($t=1; $t<=3; $t++)
    <a href="{{ route('parent.enfant.show', $eleve) }}?trimestre={{ $t }}" class="trim-btn {{ $trimestreActuel==$t?'active':'' }}">
        Trimestre {{ $t }}
        @if($moyennes[$t] !== null) — {{ number_format($moyennes[$t],1) }}/20 @endif
    </a>
    @endfor
</div>

<div class="summary-grid">
    <div class="summary-box">
        <div class="summary-val">{{ $moyennes[$trimestreActuel] !== null ? number_format($moyennes[$trimestreActuel],2) : '—' }}</div>
        <div class="summary-lbl">Moyenne générale /20</div>
    </div>
    <div class="summary-box">
        <div class="summary-val">{{ $eleve->absences->count() }}</div>
        <div class="summary-lbl">Total absences</div>
    </div>
    <div class="summary-box">
        <div class="summary-val">{{ $eleve->absencesNonJustifiees() }}</div>
        <div class="summary-lbl">Absences non justifiées</div>
    </div>
</div>

<div class="table-card">
    <div class="table-card-header"><h5><i class="fa-solid fa-star-half-stroke" style="color:#d97706;margin-right:8px;"></i>Notes par matière — Trimestre {{ $trimestreActuel }}</h5></div>
    <table class="notes-table">
        <thead><tr><th>Matière</th><th>Coefficient</th><th>Moyenne</th></tr></thead>
        <tbody>
            @forelse($notesParMatiere as $ligne)
            @php
                $moy = $ligne['moyenne'];
                $pillClass = $moy === null ? 'none' : ($moy >= 14 ? 'good' : ($moy >= 10 ? 'mid' : 'low'));
            @endphp
            <tr>
                <td style="font-weight:600;">{{ $ligne['matiere'] }}</td>
                <td>{{ $ligne['coefficient'] }}</td>
                <td><span class="note-pill {{ $pillClass }}">{{ $moy !== null ? number_format($moy,2) : '—' }}</span></td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:24px;">Aucune note disponible pour ce trimestre.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-card">
    <div class="table-card-header"><h5><i class="fa-solid fa-calendar-xmark" style="color:var(--danger);margin-right:8px;"></i>Absences récentes</h5></div>
    <table class="notes-table">
        <thead><tr><th>Date</th><th>Justifiée</th><th>Motif</th></tr></thead>
        <tbody>
            @forelse($absencesRecentes as $abs)
            <tr>
                <td>{{ $abs->date->format('d/m/Y') }}</td>
                <td><span class="absence-badge {{ $abs->justifie?'oui':'non' }}">{{ $abs->justifie?'Oui':'Non' }}</span></td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $abs->motif ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:24px;">
                <i class="fa-solid fa-circle-check" style="color:var(--success);margin-right:6px;"></i>Aucune absence enregistrée.
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
