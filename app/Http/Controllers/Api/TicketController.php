<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketReply;

class TicketController extends Controller
{
    // === USER ENDPOINTS ===

    public function myTickets(Request $request)
    {
        $userId = $request->user()->id;

        $tickets = Ticket::where('user_id', $userId)
            ->withCount('replies')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    public function createTicket(Request $request)
    {
        $request->validate([
            'subjek' => 'required|string|max:255',
            'pesan' => 'required|string',
            'prioritas' => 'nullable|in:Low,Medium,High',
            'transaksi_id' => 'nullable|string|exists:transaksis,id'
        ]);

        $userId = $request->user()->id;
        
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . time() . rand(100, 999),
            'user_id' => $userId,
            'transaksi_id' => $request->transaksi_id,
            'subjek' => $request->subjek,
            'prioritas' => $request->prioritas ?? 'Medium',
            'status' => 'Open'
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'pesan' => $request->pesan
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tiket bantuan berhasil dibuat',
            'data' => $ticket
        ]);
    }

    public function showTicket(Request $request, $id)
    {
        $userId = $request->user()->id;
        
        $ticket = Ticket::with('replies.user:id,nama_lengkap,role')->findOrFail($id);

        if ($ticket->user_id !== $userId && $request->user()->role !== 'Admin' && $request->user()->role !== 'Owner') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    public function replyTicket(Request $request, $id)
    {
        $request->validate([
            'pesan' => 'required|string'
        ]);

        $userId = $request->user()->id;
        $ticket = Ticket::findOrFail($id);

        if ($ticket->user_id !== $userId && $request->user()->role !== 'Admin' && $request->user()->role !== 'Owner') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'pesan' => $request->pesan
        ]);

        // Touch updated_at
        $ticket->touch();

        // Auto change status if admin replies
        if ($request->user()->role === 'Admin' || $request->user()->role === 'Owner') {
            if ($ticket->status === 'Open') {
                $ticket->status = 'In Progress';
                $ticket->save();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $reply->load('user:id,nama_lengkap,role')
        ]);
    }

    // === ADMIN ENDPOINTS ===

    public function adminTickets(Request $request)
    {
        $tickets = Ticket::with('user:id,nama_lengkap,email')
            ->withCount('replies')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    public function closeTicket(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->status = 'Closed';
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil ditutup'
        ]);
    }
}
