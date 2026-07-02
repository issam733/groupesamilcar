<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $box = $request->query('box', 'inbox');

        $messages = Message::with(['sender', 'recipient'])
            ->when($box === 'sent', fn ($q) => $q->where('sender_id', $user->id))
            ->when($box !== 'sent', fn ($q) => $q->where('recipient_id', $user->id))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('messagerie.index', [
            'layout' => $this->layoutFor($user->role),
            'messages' => $messages,
            'box' => $box,
            'unreadCount' => Message::where('recipient_id', $user->id)->whereNull('read_at')->count(),
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $replyTo = null;

        if ($request->filled('reply_to')) {
            $replyTo = Message::forUser($user->id)->findOrFail($request->integer('reply_to'));
        }

        return view('messagerie.create', [
            'layout' => $this->layoutFor($user->role),
            'users' => User::where('id', '!=', $user->id)
                ->where('actif', true)
                ->orderBy('role')
                ->orderBy('nom')
                ->get(),
            'replyTo' => $replyTo,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        if ((int) $data['recipient_id'] === $user->id) {
            return back()->withErrors(['recipient_id' => 'Vous ne pouvez pas vous envoyer un message.']);
        }

        Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $data['recipient_id'],
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);

        return redirect()->route('messagerie.index', ['box' => 'sent'])
            ->with('success', 'Message envoye avec succes.');
    }

    public function show(Message $message)
    {
        $user = Auth::user();

        abort_unless($message->sender_id === $user->id || $message->recipient_id === $user->id, 403);

        if ($message->isUnreadFor($user)) {
            $message->update(['read_at' => now()]);
        }

        return view('messagerie.show', [
            'layout' => $this->layoutFor($user->role),
            'message' => $message->load(['sender', 'recipient']),
        ]);
    }

    private function layoutFor(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.layouts.app',
            'enseignant' => 'enseignant.layouts.app',
            'parent' => 'parent.layouts.app',
            'eleve' => 'eleve.layouts.app',
            default => 'admin.layouts.app',
        };
    }
}
