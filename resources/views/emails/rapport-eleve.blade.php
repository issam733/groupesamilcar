@extends('emails.layout')

@section('content')
@php $r = $copie->rapport ?? []; $bareme = json_decode($copie->examen->contenu ?? '{}', true)['bareme_total'] ?? 20; @endphp

<div class="email-greeting">Bonjour {{ $prenom }},</div>

<p style="font-size:14px; color:#1e2d42; line-height:1.6; margin-bottom:20px;">
    L'enseignant de <strong>{{ $eleveNom }}</strong> a partagé un bilan personnalisé suite à un examen.
</p>

<div style="background:#f7f9fd; border-left:4px solid #1a4fa0; border-radius:10px; padding:18px 20px; margin-bottom:20px;">
    <div style="font-size:16px; font-weight:700; color:#1a4fa0; margin-bottom:6px;">
        📄 {{ $copie->examen->titre ?? 'Examen' }}
    </div>
    <div style="font-size:12.5px; color:#6b7f99;">
        {{ $copie->examen->matiere->nom ?? '' }}
        @if($copie->note_finale !== null)
            &nbsp;•&nbsp; Note : <strong style="color:#0f766e;">{{ rtrim(rtrim(number_format($copie->note_finale,2,',',''),'0'),',') }} / {{ $bareme }}</strong>
        @endif
    </div>
</div>

@if(!empty($r['appreciation']))
<p style="font-size:13.5px; color:#1e2d42; line-height:1.6; margin-bottom:18px;">
    {{ $r['appreciation'] }}
</p>
@endif

@if(!empty($r['message_parent']))
<div style="background:#eef2ff; border:1px solid #c7d2fe; border-radius:10px; padding:16px 18px; margin-bottom:22px;">
    <div style="font-size:11px; font-weight:700; color:#3730a3; text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px;">Message de l'enseignant</div>
    <div style="font-size:13px; color:#1e2d42; line-height:1.6;">{{ $r['message_parent'] }}</div>
</div>
@endif

<div style="text-align:center;">
    <a href="{{ route('parent.enfant.rapports', $copie->eleve_id) }}" class="btn-cta">Voir le rapport complet</a>
</div>

<p style="font-size:11.5px; color:#9ca8ba; margin-top:18px; text-align:center;">
    Le rapport détaillé (points forts, lacunes, recommandations) est disponible dans votre espace parent.
</p>
@endsection
