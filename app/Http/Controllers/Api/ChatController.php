<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\AkunGame;

class ChatController extends Controller
{
    public function getConversations(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::with(['akunGame', 'buyer:id,nama_lengkap,username', 'seller:id,nama_lengkap,username'])
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    public function startConversation(Request $request)
    {
        $request->validate([
            'akun_game_id' => 'required|exists:akun_games,id'
        ]);

        $buyerId = $request->user()->id;
        $akunGame = AkunGame::findOrFail($request->akun_game_id);
        $sellerId = $akunGame->user_id;

        if ($buyerId === $sellerId) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa chat dengan diri sendiri'], 400);
        }

        // Cek apakah sudah ada conversation
        $conversation = Conversation::firstOrCreate([
            'akun_game_id' => $akunGame->id,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId
        ]);

        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }

    public function getMessages(Request $request, $conversationId)
    {
        $userId = $request->user()->id;
        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $messages = Message::with('sender:id,nama_lengkap,username')
            ->where('conversation_id', $conversationId)
            ->oldest()
            ->get();

        // Tandai read
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'pesan' => 'required|string'
        ]);

        $userId = $request->user()->id;
        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $userId,
            'pesan' => $request->pesan,
            'is_read' => false
        ]);

        // Touch conversation updated_at
        $conversation->touch();

        return response()->json([
            'success' => true,
            'data' => $message->load('sender:id,nama_lengkap,username')
        ]);
    }
}
