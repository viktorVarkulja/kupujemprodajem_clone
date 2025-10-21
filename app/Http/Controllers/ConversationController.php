<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    // List authenticated user's conversations
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->with([
                'ad:id,title,slug,user_id',
                'latestMessage.user:id,name',
                'participants.user:id,name',
            ])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return response()->json($conversations);
    }

    // Create or get a 1:1 conversation (optionally tied to an ad)
    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'recipient_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNot('id', $user->id)],
            'ad_id' => ['nullable', 'integer', Rule::exists('ads', 'id')],
            'initial_message' => ['nullable', 'string'],
        ]);

        $recipientId = (int) $data['recipient_id'];
        $adId = $data['ad_id'] ?? null;

        if ($adId) {
            // Ensure recipient is the ad seller (optional, but safer for marketplace flow)
            $ad = Ad::query()->select(['id','user_id'])->findOrFail($adId);
            if ($ad->user_id !== $recipientId && $ad->user_id !== $user->id) {
                abort(422, 'Recipient does not match ad owner');
            }
        }

        // Try to find existing conversation for this pair and ad
        $conversation = Conversation::query()
            ->when($adId, fn($q) => $q->where('ad_id', $adId), fn($q) => $q->whereNull('ad_id'))
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $recipientId))
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['ad_id' => $adId]);
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
            ]);
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $recipientId,
            ]);
        }

        // Optionally create the initial message
        if (!empty($data['initial_message'])) {
            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'body' => $data['initial_message'],
            ]);
            $conversation->touch();
        }

        return response()->json($conversation->load(['participants.user:id,name', 'ad:id,title,slug,user_id']));
    }

    // Show a conversation with messages (auth must be a participant)
    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $messages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->with('user:id,name')
            ->orderByDesc('id')
            ->paginate(30);

        return response()->json([
            'conversation' => $conversation->load(['ad:id,title,slug', 'participants.user:id,name']),
            'messages' => $messages,
        ]);
    }

    // Mark the conversation as read for current user
    public function markRead(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        $participant = $conversation->participants()->where('user_id', $user->id)->first();
        if (!$participant) abort(403);
        $participant->last_read_at = Carbon::now();
        $participant->save();
        return response()->json(['status' => 'ok']);
    }
}

