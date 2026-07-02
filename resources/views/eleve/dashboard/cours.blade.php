@extends('eleve.layouts.app')

@section('title', 'Mes cours')
@section('page-title', 'Mes cours')
@section('page-subtitle', $eleve->classe->nom ?? '')

@section('extra-css')
<style>
    .timetable-wrapper { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); margin-bottom:24px; }
    .timetable-scroll { overflow-x:auto; }
    .timetable { width:100%; border-collapse:collapse; min-width:800px; }
    .timetable th { padding:12px 8px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; text-align:center; background:#f7f9fd; border-bottom:2px solid var(--border); }
    .timetable th:first-child { width:80px; }
    .timetable td { border:1px solid #f0f4fa; padding:10px; height:60px; vertical-align:middle; text-align:center; }
    .timetable td.heure-col { background:#fafbff; font-size:11px; font-weight:600; color:var(--text-muted); }
    .cours-block { background:#f0fdfa; border-left:3px solid #0d9488; border-radius:8px; padding:8px 10px; text-align:left; }
    .cours-matiere { font-size:12px; font-weight:700; color:#0d9488; }
    .cours-ens { font-size:10.5px; color:var(--text-muted); margin-top:2px; }

    .matieres-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .matieres-header { padding:16px 20px; border-bottom:1px solid var(--border); background:#f7f9fd; }
    .matieres-header h5 { font-size:14px; font-weight:700; color:var(--text); margin:0; }
    .matiere-row { display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-bottom:1px solid #f0f4fa; }
    .matiere-row:last-child { border-bottom:none; }
    .matiere-nom { font-size:13px; font-weight:600; color:var(--text); }
    .matiere-ens { font-size:11.5px; color:var(--text-muted); margin-top:2px; }
    .coef-tag { font-size:11px; font-weight:700; background:#f0fdfa; color:#0d9488; padding:3px 10px; border-radius:20px; }
</style>
@endsection

@section('content')

@if($eleve->classe)
<div class="timetable-wrapper">
    <div class="timetable-scroll">
        <table class="timetable">
            <thead><tr><th>Heure</th>@foreach($jours as $j)<th>{{ $j }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach(['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00'] as $heure)
                <tr>
                    <td class="heure-col">{{ $heure }}</td>
                    @foreach($jours as $jour)
                    @php $c = $grille[$jour][$heure] ?? null; @endphp
                    <td>
                        @if($c)
                        <div class="cours-block">
                            <div class="cours-matiere">{{ $c->matiere->nom ?? '' }}</div>
                            <div class="cours-ens">{{ $c->enseignant->prenom ?? '' }} {{ $c->enseignant->nom ?? '' }}</div>
                        </div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="matieres-card">
    <div class="matieres-header"><h5><i class="fa-solid fa-book" style="color:#0d9488;margin-right:8px;"></i>Mes matières</h5></div>
    @foreach($eleve->classe->matieres as $m)
    <div class="matiere-row">
        <div>
            <div class="matiere-nom">{{ $m->nom }}</div>
            <div class="matiere-ens">{{ $m->enseignant ? $m->enseignant->prenom.' '.$m->enseignant->nom : 'Non assigné' }}</div>
        </div>
        <span class="coef-tag">coef {{ $m->coefficient }}</span>
    </div>
    @endforeach
</div>
@else
<div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
    <i class="fa-solid fa-calendar-xmark" style="font-size:48px;opacity:.3;display:block;margin-bottom:16px;"></i>
    Aucune classe assignée pour le moment.
</div>
@endif

@endsection
