<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Event;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request, ?Conversation $conversation = null)
    {
        $me = Auth::user();
        $filter = $request->get('filter', 'all'); // all, group, community
        $search = trim((string) $request->get('q', ''));

        $conversationsQuery = $me->conversations()
            ->with(['participants', 'latestMessage.sender'])
            ->orderByDesc('last_message_at');

        if ($filter === 'group') {
            $conversationsQuery->where('type', 'group');
        } elseif ($filter === 'community') {
            $conversationsQuery->where(function ($q) use ($me) {
                $q->where('type', 'community')
                    ->orWhereHas('participants', function ($pq) use ($me) {
                        $pq->where('users.id', '!=', $me->id)
                            ->where('users.role', User::ROLE_INSTITUTION);
                    });
            });
        }

        if ($search !== '') {
            $conversationsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('participants', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
            });
        }

        $conversations = $conversationsQuery->get();

        // Pick active conversation
        $activeConversation = null;
        if ($conversation) {
            abort_unless($conversation->participants()->where('users.id', $me->id)->exists(), 403);
            $activeConversation = $conversation;
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
        }

        $messages = collect();
        if ($activeConversation) {
            // Mark incoming messages as read
            $activeConversation->messages()
                ->where('sender_id', '!=', $me->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $activeConversation->participants()->updateExistingPivot($me->id, [
                'last_read_at' => now(),
            ]);

            $activeConversation->load('participants');
            $messages = $activeConversation->messages()
                ->with(['sender', 'sharedEvent.organizer'])
                ->get();
        }

        // Users available to start a new chat (exclude self and pending institutions)
        $contacts = User::where('id', '!=', $me->id)
            ->where(function ($q) {
                $q->where('role', '!=', User::ROLE_INSTITUTION)
                    ->orWhere('is_verified', true);
            })
            ->orderByDesc('is_verified')
            ->orderBy('name')
            ->limit(30)
            ->get();

        // Upcoming events to optionally embed in chat (matching Figma Chat frame!)
        $shareableEvents = Event::orderBy('event_date')->limit(10)->get();

        return view('messages.index', compact(
            'conversations',
            'activeConversation',
            'messages',
            'filter',
            'search',
            'contacts',
            'shareableEvents'
        ));
    }

    public function startWithUser(User $user)
    {
        $me = Auth::user();
        if ($me->id === $user->id) {
            return redirect()->route('messages.index')->with('error', 'Anda tidak dapat mengirim pesan ke diri sendiri.');
        }

        $conversation = Conversation::findOrCreateDirect($me, $user);

        return redirect()->route('messages.show', $conversation);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $me = Auth::user();
        abort_unless($conversation->participants()->where('users.id', $me->id)->exists(), 403);

        $data = $request->validate([
            'body' => 'required_without_all:attachment,shared_event_id|nullable|string|max:4000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'shared_event_id' => 'nullable|exists:events,id',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('messages', 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $me->id,
            'body' => trim((string) ($data['body'] ?? '')) ?: 'Membagikan lampiran',
            'attachment_path' => $attachmentPath,
            'shared_event_id' => $data['shared_event_id'] ?? null,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->participants()->updateExistingPivot($me->id, ['last_read_at' => now()]);

        if ($request->expectsJson()) {
            $message->load(['sender', 'sharedEvent']);
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('messages.show', $conversation);
    }
}
