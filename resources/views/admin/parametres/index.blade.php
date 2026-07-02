@extends('admin.layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')
@section('page-subtitle', 'Configuration de l\'établissement')

@section('content')
<div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:40px; text-align:center; max-width:600px;">
    <i class="fa-solid fa-gear" style="font-size:48px; color:var(--primary); opacity:.3; display:block; margin-bottom:16px;"></i>
    <h4 style="color:var(--text); margin-bottom:8px;">Page Paramètres</h4>
    <p style="color:var(--text-muted); font-size:13px;">
        Cette page est un espace réservé. Le module de paramètres système
        (nom établissement, année scolaire active, clé Groq, etc.) n'a pas
        encore été développé dans le détail.
    </p>
</div>
@endsection
