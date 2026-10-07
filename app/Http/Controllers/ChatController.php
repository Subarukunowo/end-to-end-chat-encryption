<?php
namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function storeMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'encrypted_body' => 'required|string',
        ]);

        // 1. Simpan pesan terenkripsi ke PostgreSQL
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'encrypted_body' => $request->encrypted_body,
        ]);

        // 2. Trigger WebSocket agar pesan langsung muncul di layar penerima
        broadcast(new MessageSent($message));

        return response()->json(['status' => 'Pesan terkirim', 'message' => $message]);
    }

    public function getConversationWith($contactId)
    {
        $userId = auth()->id();
        
        // Find if a conversation already exists between these two users
        $conversation = Conversation::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereHas('users', function ($query) use ($contactId) {
            $query->where('user_id', $contactId);
        })->first();

        // If not, create a new conversation
        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$userId, $contactId]);
        }

        return response()->json($conversation);
    }

    public function getMessages(Conversation $conversation)
    {
        // Pastikan user adalah bagian dari percakapan ini
        if (!$conversation->users()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }

        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();
        return response()->json($messages);
    }
}