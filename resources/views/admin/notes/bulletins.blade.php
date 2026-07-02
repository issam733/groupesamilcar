@extends('admin.layouts.app')

@section('title', 'Bulletins')
@section('page-title', 'Bulletins')
@section('page-subtitle', 'Générer les bulletins trimestriels')

@section('extra-css')
<style>
    .toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px 20px; margin-bottom:18px; display:flex; gap:14px; align-items:center; flex-wrap:wrap; box-shadow:var(--shadow-sm); }
    .toolbar-select { padding:9px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; cursor:pointer; min-width:200px; }
    .trim-toggle { display:flex; gap:6px; }
    .trim-btn { padding:8px 16px; border-radius:9px; border:1.5px solid var(--border); background:#fafbff; font-size:13px; font-weight:600; color:var(--text-muted); cursor:pointer; text-decoration:none; transition:all .2s; }
    .trim-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }

    .table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .bul-table { width:100%; border-collapse:collapse; }
    .bul-table thead tr { background:#f7f9fd; border-bottom:1px solid var(--border); }
    .bul-table th { padding:12px 16px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; }
    .bul-table td { padding:13px 16px; font-size:13.5px; color:var(--text); border-bottom:1px solid #f0f4fa; vertical-align:middle; }
    .bul-table tr:last-child td { border-bottom:none; }
    .bul-table tr:hover td { background:#fafbff; }

    .eleve-cell { display:flex; align-items:center; gap:11px; }
    .eleve-avatar-sm { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

    .moyenne-badge { font-size:14px; font-weight:800; padding:4px 12px; border-radius:20px; }
    .moyenne-badge.excellent { background:#ecfdf5; color:#0d9488; }
    .moyenne-badge.bien      { background:#eef3ff; color:var(--primary); }
    .moyenne-badge.passable  { background:#fffbeb; color:var(--warning); }
    .moyenne-badge.insuff    { background:#fef2f2; color:var(--danger); }
    .moyenne-badge.none      { background:#f1f5f9; color:var(--text-muted); }

    .btn-print { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:8px; font-size:12.5px; font-weight:600; text-decoration:none; background:var(--primary); color:#fff; transition:all .2s; }
    .btn-print:hover { box-shadow:0 4px 15px rgba(26,79,160,.3); color:#fff; }

    .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
</style>
@endsection

@section('content')

<div class="toolbar">
    <form method="GET" action="{{ route('admin.bulletins.index') }}" id="filterForm" style="display:contents;">
        <select class="toolbar-select" name="classe_id" onchange="this.form.submit()">
            <option value="">— Sélectionner une classe —</option>
            @foreach($classes->groupBy('niveau') as $niveau => $cls)
            <optgroup label="{{ $niveau }}">
                @foreach($cls as $c)
                <option value="{{ $c->id }}" {{ $classeId==$c->id?'selected':'' }}>{{ $c->nom }}</option>
                @endforeach
            </optgroup>
            @endforeach
        </select>
        <input type="hidden" name="trimestre" id="trimestreHidden" value="{{ $trimestre }}">
    </form>

    <div class="trim-toggle">
        @for($t=1; $t<=3; $t++)
        <a href="{{ route('admin.bulletins.index', ['classe_id'=>$classeId, 'trimestre'=>$t]) }}"
           class="trim-btn {{ $trimestre==$t?'active':'' }}">T{{ $t }}</a>
        @endfor
    </div>
</div>

<div class="table-card">
    @if($classeId && $eleves->count())
    <table class="bul-table">
        <thead>
            <tr>
                <th>Élève</th>
                <th>Matricule</th>
                <th>Moyenne T{{ $trimestre }}</th>
                <th>Mention</th>
                <th>Bulletin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($eleves as $eleve)
            @php
                $moy = $eleve->moyenne_calc;
                $badgeClass = match(true) {
                    $moy === null => 'none',
                    $moy >= 16    => 'excellent',
                    $moy >= 12    => 'bien',
                    $moy >= 10    => 'passable',
                    default       => 'insuff',
                };
            @endphp
            <tr>
                <td>
                    <div class="eleve-cell">
                        <div class="eleve-avatar-sm">{{ strtoupper(substr($eleve->prenom,0,1).substr($eleve->nom,0,1)) }}</div>
                        {{ $eleve->prenom }} {{ $eleve->nom }}
                    </div>
                </td>
                <td><code style="font-size:11px; background:#f0f4fa; padding:2px 6px; border-radius:5px;">{{ $eleve->matricule }}</code></td>
                <td>
                    <span class="moyenne-badge {{ $badgeClass }}">
                        {{ $moy !== null ? number_format($moy, 2) : '—' }}{{ $moy !== null ? '/20' : '' }}
                    </span>
                </td>
                <td style="font-size:12px; color:var(--text-muted);">
                    {{ $moy !== null ? \App\Models\Eleve::mention($moy) : '—' }}
                </td>
                <td>
                    @if($moy !== null)
                    <a href="{{ route('admin.bulletins.pdf', [$eleve, $trimestre]) }}" target="_blank" class="btn-print">
                        <i class="fa-solid fa-print"></i> Imprimer
                    </a>
                    @else
                    <span style="font-size:12px; color:var(--text-muted);">Pas de notes</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @elseif($classeId)
    <div class="empty-state">
        <i class="fa-solid fa-users-slash" style="font-size:40px; opacity:.3; display:block; margin-bottom:14px;"></i>
        Aucun élève dans cette classe.
    </div>
    @else
    <div class="empty-state">
        <i class="fa-solid fa-file-lines" style="font-size:40px; opacity:.3; display:block; margin-bottom:14px;"></i>
        Sélectionnez une classe pour afficher les bulletins.
    </div>
    @endif
</div>

@endsection
