@extends('emails.layout')

@section('content')
<div class="email-greeting">Bonjour {{ $prenom }},</div>

<p style="font-size:14px; color:#1e2d42; line-height:1.6; margin-bottom:20px;">
    Une nouvelle annonce a été publiée par l'administration du Groupe Scolaire Amilcar :
</p>

<div style="background:#f7f9fd; border-left:4px solid #1a4fa0; border-radius:10px; padding:18px 20px; margin-bottom:20px;">
    <div style="font-size:16px; font-weight:700; color:#1a4fa0; margin-bottom:10px;">
        📢 {{ $annonce->titre }}
    </div>
    <div style="font-size:13.5px; color:#1e2d42; line-height:1.6; white-space:pre-line;">
        {{ $annonce->contenu }}
    </div>
</div>

<p style="font-size:12px; color:#6b7f99;">
    Publié le {{ $annonce->created_at->translatedFormat('d F Y \à H:i') }}
</p>
@endsection
