<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Send a message in a conversation
    public function store(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $data = $request->validate([
            'body' => ['required', 'string']
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => $data['body'],
        ]);

        // Touch conversation to bump ordering by latest activity
        $conversation->touch();

        return response()->json($message->load('user:id,name'));
    }
}

