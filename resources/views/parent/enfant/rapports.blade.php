@extends('parent.layouts.app')

@section('title', 'Rapports — '.$eleve->prenom)
@section('page-title', 'Rapports d\'examen')
@section('page-subtitle', $eleve->prenom.' '.$eleve->nom)

@section('extra-css')
<style>
    .rap-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm); margin-bottom:18px; overflow:hidden; }
    .rap-head { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .rap-head .ic { width:42px; height:42px; border-radius:11px; background:#f3eeff; color:#7c5cbf; display:flex; align-items:center; justify-content:center; font-size:18px; }
    .rap-title { font-size:15px; font-weight:700; }
    .rap-meta { font-size:11.5px; color:var(--text-muted); margin-top:2px; }
    .rap-note { margin-left:auto; background:#ecfdf5; color:#0f766e; border-radius:20px; padding:6px 14px; font-size:13px; font-weight:700; }
    .rap-body { padding:20px 22px; }
    .rap-appr { font-size:13.5px; line-height:1.6; margin-bottom:16px; }
    .rap-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:12px; }
    .rap-bloc { border-radius:11px; padding:14px 16px; }
    .rap-bloc .t { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:8px; }
    .rap-bloc ul { margin:0; padding-left:17px; font-size:13px; line-height:1.55; }
    .rap-parent { margin-top:16px; background:#eef2ff; border:1px solid #c7d2fe; border-radius:11px; padding:15px 17px; }
    .rap-parent .t { font-size:11px; font-weight:700; color:#3730a3; text-transform:uppercase; letter-spacing:.4px; margin-bottom:7px; }
    .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
    .empty-state i { font-size:48px; opacity:.3; display:block; margin-bottom:16px; }
</style>
@endsection

@section('content')

@forelse($copies as $copie)
    @php $r = $copie->rapport; $bareme = json_decode($copie->examen->contenu ?? '{}', true)['bareme_total'] ?? 20; @endphp
    <div class="rap-card">
        <div class="rap-head">
            <div class="ic"><i class="fa-solid fa-robot"></i></div>
            <div>
                <div class="rap-title">{{ $copie->examen->titre ?? 'Examen' }}</div>
                <div class="rap-meta">
                    <i class="fa-solid fa-book"></i> {{ $copie->examen->matiere->nom ?? '—' }}
                    · <i class="fa-regular fa-clock"></i> {{ $copie->created_at?->format('d/m/Y') }}
                </div>
            </div>
            @if($copie->note_finale !== null)
                <div class="rap-note">{{ rtrim(rtrim(number_format($copie->note_finale,2,',',''),'0'),',') }} / {{ $bareme }}</div>
            @endif
        </div>
        <div class="rap-body">
            @if(!empty($r['appreciation']))
                <div class="rap-appr">{{ $r['appreciation'] }}</div>
            @endif

            <div class="rap-grid">
                @php
                    $blocs = [
                        ['Points forts', $r['points_forts'] ?? [], '#0f766e', '#ecfdf5', 'fa-thumbs-up'],
                        ['Lacunes', $r['lacunes'] ?? [], '#b45309', '#fffbeb', 'fa-circle-exclamation'],
                        ['Difficultés', $r['difficultes'] ?? [], '#b91c1c', '#fef2f2', 'fa-triangle-exclamation'],
                        ['Recommandations', $r['recommandations'] ?? [], '#5b21b6', '#f5f3ff', 'fa-lightbulb'],
                    ];
                @endphp
                @foreach($blocs as [$titre, $items, $col, $bg, $ic])
                    @if(!empty($items))
                    <div class="rap-bloc" style="background:{{ $bg }};">
                        <div class="t" style="color:{{ $col }};"><i class="fa-solid {{ $ic }}"></i> {{ $titre }}</div>
                        <ul>@foreach($items as $it)<li>{{ $it }}</li>@endforeach</ul>
                    </div>
                    @endif
                @endforeach
            </div>

            @if(!empty($r['message_parent']))
                <div class="rap-parent">
                    <div class="t"><i class="fa-solid fa-envelope-open-text"></i> Message de l'enseignant</div>
                    <p style="font-size:13px; line-height:1.6; margin:0;">{{ $r['message_parent'] }}</p>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="rap-card">
        <div class="empty-state">
            <i class="fa-solid fa-robot"></i>
            Aucun rapport d'examen n'a encore été transmis pour {{ $eleve->prenom }}.<br>
            <span style="font-size:12.5px;">Les enseignants peuvent partager ici un bilan détaillé après les examens.</span>
        </div>
    </div>
@endforelse

@endsection
