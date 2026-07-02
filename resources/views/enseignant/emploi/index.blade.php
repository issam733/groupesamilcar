@extends('enseignant.layouts.app')

@section('title', 'Mon emploi du temps')
@section('page-title', 'Mon emploi du temps')
@section('page-subtitle', 'Vue hebdomadaire de vos cours')

@section('extra-css')
<style>
    .edt-table { width:100%; border-collapse:collapse; background:var(--card); }
    .edt-table th, .edt-table td { border:1px solid var(--border); padding:8px; text-align:center; font-size:12.5px; vertical-align:top; }
    .edt-table th { background:#f3eeff; color:var(--primary); font-weight:700; font-size:12px; }
    .edt-table .heure-col { background:var(--bg); font-weight:600; color:var(--text-muted); white-space:nowrap; }
    .creneau { background:linear-gradient(135deg,#6d28d9,#8b5cf6); color:#fff; border-radius:8px; padding:8px 6px; }
    .creneau .mat { font-weight:700; font-size:12.5px; }
    .creneau .cls { font-size:11px; opacity:.9; margin-top:3px; }
</style>
@endsection

@section('content')

@if(empty($heures))
    <div class="page-card"><div class="empty-state"><i class="fa-regular fa-calendar"></i> Aucun cours n'est encore programmé dans votre emploi du temps.</div></div>
@else
<div class="page-card" style="padding:0; overflow:hidden;">
    <div style="overflow-x:auto; padding:18px;">
        <table class="edt-table">
            <thead>
                <tr>
                    <th class="heure-col">Horaire</th>
                    @foreach($jours as $jour)
                        <th>{{ $jour }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($heures as $h)
                <tr>
                    <td class="heure-col">{{ $h }}</td>
                    @foreach($jours as $jour)
                        <td>
                            @php $c = $grille[$jour][$h] ?? null; @endphp
                            @if($c)
                                <div class="creneau">
                                    <div class="mat">{{ $c->matiere->nom ?? 'Cours' }}</div>
                                    <div class="cls"><i class="fa-solid fa-door-open"></i> {{ $c->classe->nom ?? '—' }}</div>
                                    <div class="cls">{{ substr($c->heure_debut,0,5) }}–{{ substr($c->heure_fin,0,5) }}</div>
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
@endif

@endsection
