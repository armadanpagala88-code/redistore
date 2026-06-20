<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;

class ChatController extends Controller
{
    public function getConversations()
    {
        $conversations = Conversation::with([
                'akunGame:id,judul_akun,kategori_game_id', 
                'akunGame.kategori:id,nama_game',
                'buyer:id,nama_lengkap,username', 
                'seller:id,nama_lengkap,username'
            ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    public function getMessages($conversationId)
    {
        $messages = Message::with('sender:id,nama_lengkap,username,role')
            ->where('conversation_id', $conversationId)
            ->oldest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
}
