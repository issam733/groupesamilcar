@extends($layout)

@section('title', 'Nouveau message')
@section('page-title', 'Nouveau message')
@section('page-subtitle', 'Envoyer un message interne')

@section('extra-css')
    <style>
        .message-form { max-width:860px; }
        .form-card { padding:24px; }
        .form-label { font-size:13px; font-weight:800; color:var(--text); }
        .form-control, .form-select { border-color:var(--border); border-radius:9px; font-size:14px; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:18px; }
        .btn-main { background:var(--primary, #1a4fa0); color:#fff; border:none; border-radius:9px; padding:10px 16px; font-size:13px; font-weight:800; display:inline-flex; align-items:center; gap:8px; }
        .btn-soft { background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:9px; padding:10px 16px; font-size:13px; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
    </style>
@endsection

@section('content')
    <div class="card form-card message-form">
        <form method="POST" action="{{ route('messagerie.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="recipient_id">Destinataire</label>
                <select name="recipient_id" id="recipient_id" class="form-select" required>
                    <option value="">Choisir un destinataire</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('recipient_id', $replyTo?->sender_id) == $user->id)>
                            {{ $user->prenom }} {{ $user->nom }} - {{ ucfirst($user->role) }}
                        </option>
                    @endforeach
                </select>
                @error('recipient_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="subject">Objet</label>
                <input
                    type="text"
                    name="subject"
                    id="subject"
                    class="form-control"
                    value="{{ old('subject', $replyTo ? 'Re: '.$replyTo->subject : '') }}"
                    required
                    maxlength="255"
                >
                @error('subject')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="body">Message</label>
                <textarea name="body" id="body" rows="9" class="form-control" required>{{ old('body') }}</textarea>
                @error('body')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn-main">
                    <i class="fa-solid fa-paper-plane"></i> Envoyer
                </button>

                <a href="{{ route('messagerie.index') }}" class="btn-soft">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
            </div>
        </form>
    </div>
@endsection
