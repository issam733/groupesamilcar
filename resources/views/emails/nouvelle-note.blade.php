@extends('emails.layout')

@section('content')
<div class="email-greeting">Bonjour {{ $prenom }},</div>

<p style="font-size:14px; color:#1e2d42; line-height:1.6; margin-bottom:20px;">
    Une nouvelle note a été enregistrée pour <strong>{{ $eleve->prenom }} {{ $eleve->nom }}</strong> :
</p>

<table style="width:100%; background:#f7f9fd; border-radius:10px; padding:4px; border-collapse:collapse; margin-bottom:20px;">
    <tr>
        <td style="padding:14px 18px; font-size:13px; color:#6b7f99;">Matière</td>
        <td style="padding:14px 18px; font-size:13px; font-weight:700; color:#1e2d42; text-align:right;">{{ $matiere->nom ?? '—' }}</td>
    </tr>
    <tr>
        <td style="padding:0 18px 14px; font-size:13px; color:#6b7f99;">Type</td>
        <td style="padding:0 18px 14px; font-size:13px; font-weight:600; color:#1e2d42; text-align:right;">{{ ucfirst($note->type) }}</td>
    </tr>
    <tr>
        <td style="padding:0 18px 14px; font-size:13px; color:#6b7f99;">Note obtenue</td>
        <td style="padding:0 18px 14px; text-align:right;">
            <span style="display:inline-block; font-size:18px; font-weight:800; color:{{ $note->valeur >= 10 ? '#0d9488' : '#dc2626' }};">
                {{ number_format($note->valeur, 2) }}/20
            </span>
        </td>
    </tr>
</table>

<p style="font-size:12px; color:#6b7f99;">
    Trimestre {{ $note->trimestre }} — Vous pouvez consulter l'ensemble des résultats depuis votre espace parent.
</p>
@endsection
