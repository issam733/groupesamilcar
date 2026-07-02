@extends('parent.layouts.app')

@section('title', 'Emploi du temps — '.$eleve->prenom)
@section('page-title', 'Emploi du temps')
@section('page-subtitle', $eleve->prenom.' '.$eleve->nom.' — '.($eleve->classe->nom ?? ''))

@section('extra-css')
<style>
    .timetable-wrapper { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .timetable-scroll { overflow-x:auto; }
    .timetable { width:100%; border-collapse:collapse; min-width:800px; }
    .timetable th { padding:12px 8px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; text-align:center; background:#f7f9fd; border-bottom:2px solid var(--border); }
    .timetable th:first-child { width:80px; }
    .timetable td { border:1px solid #f0f4fa; padding:10px; height:60px; vertical-align:middle; text-align:center; }
    .timetable td.heure-col { background:#fafbff; font-size:11px; font-weight:600; color:var(--text-muted); }
    .cours-block { background:#fff8ec; border-left:3px solid #d97706; border-radius:8px; padding:8px 10px; text-align:left; }
    .cours-matiere { font-size:12px; font-weight:700; color:#92400e; }
    .cours-ens { font-size:10.5px; color:var(--text-muted); margin-top:2px; }
</style>
@endsection

@section('content')

@if($eleve->classe)
<div class="timetable-wrapper">
    <div class="timetable-scroll">
        <table class="timetable">
            <thead>
                <tr><th>Heure</th>@foreach($jours as $j)<th>{{ $j }}</th>@endforeach</tr>
            </thead>
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
@else
<div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
    <i class="fa-solid fa-calendar-xmark" style="font-size:48px;opacity:.3;display:block;margin-bottom:16px;"></i>
    Aucune classe assignée pour le moment.
</div>
@endif

@endsection
