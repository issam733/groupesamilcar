@extends('parent.layouts.app')

@section('title', 'Attestations — '.$eleve->prenom)
@section('page-title', 'Attestations')
@section('page-subtitle', $eleve->prenom.' '.$eleve->nom)

@section('extra-css')
<style>
    .att-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
    .att-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px; box-shadow:var(--shadow-sm); transition:all .2s; }
    .att-card:hover { transform:translateY(-2px); box-shadow:var(--shadow); }
    .att-icon { width:46px; height:46px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:19px; margin-bottom:14px; }
    .att-icon.inscription { background:#eef3ff; color:var(--primary); }
    .att-icon.presence    { background:#ecfdf5; color:var(--success); }
    .att-icon.reussite    { background:#f3eeff; color:#7c5cbf; }
    .att-titre { font-size:14px; font-weight:700; color:var(--text); margin-bottom:4px; }
    .att-numero { font-size:11px; color:var(--text-muted); font-family:monospace; margin-bottom:14px; }
    .btn-download { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:10px; border-radius:9px; background:linear-gradient(135deg,#d97706,#f59e0b); color:#fff; text-decoration:none; font-size:13px; font-weight:600; }
    .btn-download:hover { color:#fff; box-shadow:0 6px 20px rgba(217,119,6,.3); }
    .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
    .empty-state i { font-size:48px; opacity:.3; display:block; margin-bottom:16px; }
</style>
@endsection

@section('content')

@if($attestations->count())
<div class="att-grid">
    @foreach($attestations as $att)
    <div class="att-card">
        <div class="att-icon {{ $att->type }}">
            <i class="fa-solid {{ ['inscription'=>'fa-file-signature','presence'=>'fa-calendar-check','reussite'=>'fa-trophy'][$att->type] ?? 'fa-file' }}"></i>
        </div>
        <div class="att-titre">Attestation de {{ $att->type }}</div>
        <div class="att-numero">{{ $att->numero_unique }} · {{ $att->created_at->format('d/m/Y') }}</div>
        <a href="{{ route('admin.attestations.pdf', $att) }}" target="_blank" class="btn-download">
            <i class="fa-solid fa-download"></i> Télécharger
        </a>
    </div>
    @endforeach
</div>
@else
<div class="empty-state">
    <i class="fa-solid fa-file-certificate"></i>
    <h4 style="color:var(--text);margin-bottom:8px;">Aucune attestation disponible</h4>
    <p>Contactez l'administration pour obtenir une attestation.</p>
</div>
@endif

@endsection
