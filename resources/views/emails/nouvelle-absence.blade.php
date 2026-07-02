@extends('emails.layout')

@section('content')
<div class="email-greeting">Bonjour {{ $prenom }},</div>

<p style="font-size:14px; color:#1e2d42; line-height:1.6; margin-bottom:20px;">
    Nous vous informons qu'une absence a été enregistrée pour <strong>{{ $eleve->prenom }} {{ $eleve->nom }}</strong> :
</p>

<div style="background:#fef2f2; border-left:4px solid #dc2626; border-radius:10px; padding:18px 20px; margin-bottom:20px;">
    <div style="font-size:13px; color:#6b7f99; margin-bottom:6px;">Date de l'absence</div>
    <div style="font-size:16px; font-weight:700; color:#dc2626; margin-bottom:14px;">
        {{ $absence->date->translatedFormat('l d F Y') }}
    </div>
    @if($absence->motif)
    <div style="font-size:13px; color:#6b7f99; margin-bottom:4px;">Motif indiqué</div>
    <div style="font-size:13.5px; color:#1e2d42;">{{ $absence->motif }}</div>
    @endif
</div>

<p style="font-size:13px; color:#1e2d42; line-height:1.6;">
    Si cette absence est justifiée, merci de transmettre un justificatif à
    l'administration de l'établissement dans les meilleurs délais.
</p>
@endsection
