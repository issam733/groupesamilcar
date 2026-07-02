@extends('admin.layouts.app')

@section('title', 'Journal des actions')
@section('page-title', 'Journal des actions')
@section('page-subtitle', 'Historique de toutes les actions sur la plateforme')

@section('extra-css')
<style>
    .table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; box-shadow:var(--shadow-sm); }
    .log-table { width:100%; border-collapse:collapse; }
    .log-table th { padding:12px 16px; font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; text-align:left; background:#f7f9fd; border-bottom:1px solid var(--border); }
    .log-table td { padding:12px 16px; font-size:13px; color:var(--text); border-bottom:1px solid #f0f4fa; }
    .type-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:8px; }
    .type-dot.creation { background:var(--success); }
    .type-dot.modification { background:var(--warning); }
    .type-dot.suppression { background:var(--danger); }
    .type-dot.connexion, .type-dot.deconnexion { background:var(--primary); }
    .type-dot.export { background:#7c5cbf; }
    .pagination-wrapper { padding:16px 20px; border-top:1px solid var(--border); display:flex; justify-content:center; }
</style>
@endsection

@section('content')
<div class="table-card">
    <table class="log-table">
        <thead>
            <tr><th>Utilisateur</th><th>Action</th><th>Type</th><th>Date</th></tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="font-weight:600;">{{ $log->user->prenom ?? 'Système' }} {{ $log->user->nom ?? '' }}</td>
                <td>{{ $log->action }}</td>
                <td><span class="type-dot {{ $log->type }}"></span>{{ ucfirst($log->type) }}</td>
                <td style="font-size:12px; color:var(--text-muted);">{{ $log->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:30px;">Aucune action enregistrée pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($logs->hasPages())
    <div class="pagination-wrapper">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
