@extends('eleve.layouts.app')

@section('title', 'Mes résultats')
@section('page-title', 'Mes résultats')
@section('page-subtitle', $eleve->classe->nom ?? '')

@section('extra-css')
<style>
    .trim-toggle { display:flex; gap:8px; margin-bottom:20px; }
    .trim-btn { padding:9px 18px; border-radius:9px; border:1.5px solid var(--border); background:#fafbff; font-size:13px; font-weight:600; color:var(--text-muted); cursor:pointer; transition:all .2s; text-decoration:none; display:inline-block; }
    .trim-btn.active { background:#0d9488; color:#fff; border-color:#0d9488; }

    .moyenne-hero { background:linear-gradient(135deg,#0d9488,#14b8a6); border-radius:16px; padding:28px; color:#fff; text-align:center; margin-bottom:24px; }
    .moyenne-hero-val { font-size:42px; font-weight:800; }
    .moyenne-hero-lbl { font-size:13px; opacity:.85; margin-top:4px; }

    .table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .notes-table { width:100%; border-collapse:collapse; }
    .notes-table th { padding:11px 16px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; text-align:left; border-bottom:1px solid var(--border); background:#f7f9fd; }
    .notes-table td { padding:12px 16px; font-size:13px; color:var(--text); border-bottom:1px solid #f0f4fa; }
    .notes-table tr:last-child td { border-bottom:none; }

    .note-pill { font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px; }
    .note-pill.good { background:#ecfdf5; color:var(--success); }
    .note-pill.mid  { background:#fffbeb; color:var(--warning); }
    .note-pill.low  { background:#fef2f2; color:var(--danger); }
    .note-pill.none { background:#f1f5f9; color:var(--text-muted); }
</style>
@endsection

@section('content')

<div class="trim-toggle">
    @for($t=1; $t<=3; $t++)
    <a href="{{ route('eleve.resultats') }}?trimestre={{ $t }}" class="trim-btn {{ $trimestre==$t?'active':'' }}">Trimestre {{ $t }}</a>
    @endfor
</div>

<div class="moyenne-hero">
    <div class="moyenne-hero-val">{{ $moyenneGenerale !== null ? number_format($moyenneGenerale,2) : '—' }}</div>
    <div class="moyenne-hero-lbl">Moyenne générale /20 — Trimestre {{ $trimestre }}</div>
</div>

<div class="table-card">
    <table class="notes-table">
        <thead><tr><th>Matière</th><th>Coefficient</th><th>Ma moyenne</th></tr></thead>
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

@endsection
