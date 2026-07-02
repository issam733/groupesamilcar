@extends('parent.layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Vos alertes et messages')

@section('extra-css')
<style>
    .notif-list { display:flex; flex-direction:column; gap:10px; }
    .notif-item { display:flex; gap:14px; align-items:flex-start; background:var(--card); border:1px solid var(--border); border-radius:13px; padding:16px 18px; box-shadow:var(--shadow-sm); text-decoration:none; color:inherit; transition:all .15s; }
    .notif-item:hover { transform:translateY(-1px); box-shadow:var(--shadow); }
    .notif-item.unread { border-left:4px solid var(--primary); background:#fbfcff; }
    .notif-ic { width:42px; height:42px; border-radius:11px; background:#f3eeff; color:#7c5cbf; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
    .notif-title { font-size:14px; font-weight:700; color:var(--text); display:flex; align-items:center; gap:8px; }
    .dot { width:8px; height:8px; border-radius:50%; background:var(--primary); display:inline-block; }
    .notif-msg { font-size:13px; color:var(--text-muted); margin-top:3px; line-height:1.5; }
    .notif-time { font-size:11.5px; color:var(--text-muted); margin-top:6px; }
    .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
    .empty-state i { font-size:48px; opacity:.3; display:block; margin-bottom:16px; }
</style>
@endsection

@section('content')

@forelse($notifications as $notif)
    @php
        $d = $notif->data;
        $unread = is_null($notif->read_at);
        $url = isset($d['eleve_id']) ? route('parent.enfant.rapports', $d['eleve_id']) : route('parent.dashboard');
    @endphp
    <a href="{{ $url }}" class="notif-item {{ $unread ? 'unread' : '' }}" style="margin-bottom:10px;">
        <div class="notif-ic"><i class="fa-solid fa-robot"></i></div>
        <div style="flex:1;">
            <div class="notif-title">
                {{ $d['titre'] ?? 'Notification' }}
                @if($unread)<span class="dot" title="Non lu"></span>@endif
            </div>
            <div class="notif-msg">{{ $d['message'] ?? '' }}</div>
            <div class="notif-time"><i class="fa-regular fa-clock"></i> {{ $notif->created_at?->diffForHumans() }}</div>
        </div>
        <i class="fa-solid fa-chevron-right" style="color:var(--text-muted); align-self:center;"></i>
    </a>
@empty
    <div class="notif-item" style="cursor:default;">
        <div style="width:100%;" class="empty-state">
            <i class="fa-regular fa-bell"></i>
            Vous n'avez aucune notification pour le moment.<br>
            <span style="font-size:12.5px;">Vous serez prévenu ici lorsqu'un enseignant partagera un rapport.</span>
        </div>
    </div>
@endforelse

@endsection
