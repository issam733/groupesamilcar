@extends($layout)

@section('title', 'Messagerie')
@section('page-title', 'Messagerie')
@section('page-subtitle', 'Vos conversations')

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
@endphp

<div class="msg-wrap">
    <div class="msg-toolbar">
        <div></div>
        <a href="{{ route('messagerie.nouveau') }}" class="btn-msg"><i class="fa-solid fa-pen-to-square"></i> Nouveau message</a>
    </div>

    @if(session('success'))
        <div class="alert-ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <div class="conv-list">
        @forelse($conversations as $conv)
            @php
                $autre = $conv->autre($user->id);
                $ri = $roleInfo[$autre->role ?? 'eleve'] ?? ['Utilisateur', '#64748b'];
                $dernier = $conv->dernierMessage;
                $unread = $conv->non_lus_count > 0;
            @endphp
            <a href="{{ route('messagerie.show', $conv) }}" class="conv-item {{ $unread ? 'unread' : '' }}">
                <div class="avatar" style="background:{{ $ri[1] }};">
                    {{ strtoupper(mb_substr($autre->prenom ?? '?',0,1) . mb_substr($autre->nom ?? '',0,1)) }}
                </div>
                <div class="conv-main">
                    <div class="conv-name">
                        {{ $autre?->nomComplet() ?? 'Utilisateur supprimé' }}
                        <span class="role-badge" style="background:{{ $ri[1] }};">{{ $ri[0] }}</span>
                    </div>
                    <div class="conv-snippet">
                        @if($dernier)
                            @if($dernier->expediteur_id === $user->id)<span style="opacity:.6;">Vous : </span>@endif
                            {{ \Illuminate\Support\Str::limit($dernier->corps, 70) }}
                        @else
                            <em>Aucun message</em>
                        @endif
                    </div>
                </div>
                <div class="conv-meta">
                    <span class="conv-time">{{ ($conv->dernier_message_at ?? $conv->updated_at)?->diffForHumans(null, true) }}</span>
                    @if($unread)<span class="badge-unread">{{ $conv->non_lus_count }}</span>@endif
                </div>
            </a>
        @empty
            <div class="conv-item" style="cursor:default;">
                <div style="width:100%;" class="empty-state">
                    <i class="fa-regular fa-comments"></i>
                    Aucune conversation pour le moment.<br>
                    <a href="{{ route('messagerie.nouveau') }}" class="btn-msg" style="margin-top:14px;"><i class="fa-solid fa-pen-to-square"></i> Écrire un message</a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
