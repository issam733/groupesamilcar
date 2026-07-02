@extends($layout)

@section('title', 'Conversation')
@section('page-title', 'Messagerie')
@section('page-subtitle', $autre?->nomComplet() ?? 'Conversation')

@section('extra-css')
@include('messagerie._styles')
@endsection

@section('content')
@php
    $roleInfo = [
        'admin'      => ['Administration', '#1a4fa0'],
        'enseignant' => ['Enseignant', '#6d28d9'],
        'parent'     => ['Parent', '#b45309'],
        'eleve'      => ['Élève', '#0d9488'],
    ];
    $ri = $roleInfo[$autre->role ?? 'eleve'] ?? ['Utilisateur', '#64748b'];
@endphp

<div class="msg-wrap">
    <div class="msg-toolbar">
        <a href="{{ route('messagerie.index') }}" class="btn-msg ghost"><i class="fa-solid fa-arrow-left"></i> Boîte de réception</a>
    </div>

    <div style="display:flex; align-items:center; gap:13px; padding:14px 16px; background:var(--card,#fff); border:1px solid var(--border,#e5e7eb); border-radius:13px; margin-bottom:14px;">
        <div class="avatar" style="background:{{ $ri[1] }};">{{ strtoupper(mb_substr($autre->prenom ?? '?',0,1).mb_substr($autre->nom ?? '',0,1)) }}</div>
        <div>
            <div style="font-weight:700; font-size:15px;">{{ $autre?->nomComplet() ?? 'Utilisateur' }}</div>
            <span class="role-badge" style="background:{{ $ri[1] }};">{{ $ri[0] }}</span>
        </div>
    </div>

    <div class="thread" id="thread">
        @forelse($conversation->messages as $m)
            @php $mine = $m->expediteur_id === $user->id; @endphp
            <div class="bubble-row {{ $mine ? 'me' : 'them' }}">
                <div class="bubble {{ $mine ? 'me' : 'them' }}">
                    {{ $m->corps }}
                    <div class="bubble-time">{{ $m->created_at?->format('d/m H:i') }}@if($mine && $m->lu_at) · <i class="fa-solid fa-check-double"></i>@endif</div>
                </div>
            </div>
        @empty
            <div class="empty-state"><i class="fa-regular fa-comment-dots"></i> Démarrez la conversation ci-dessous.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('messagerie.repondre', $conversation) }}" class="reply-bar" id="replyForm">
        @csrf
        <textarea name="corps" rows="1" placeholder="Écrire un message…" required
                  oninput="this.style.height='auto'; this.style.height=Math.min(this.scrollHeight,140)+'px';"
                  onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault(); document.getElementById('replyForm').submit();}"></textarea>
        <button type="submit" class="btn-msg"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // Défile en bas du fil au chargement
    (function(){ var t=document.getElementById('thread'); if(t){ window.scrollTo(0, document.body.scrollHeight); } })();
</script>
@endsection
