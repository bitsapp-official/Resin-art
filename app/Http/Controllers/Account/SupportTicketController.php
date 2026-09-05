<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = Auth::user()->supportTickets()
            ->latest()
            ->paginate(10);

        return view('account.support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TCK-' . strtoupper(Str::random(8)),
            'user_id' => Auth::id(),
            'order_id' => $request->order_id,
            'subject' => $request->subject,
            'category' => $request->category,
            'status' => 'OPEN',
            'priority' => 'medium',
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_admin' => false,
            'message' => $request->message,
        ]);

        return redirect()->route('account.support.show', $ticket->id)->with('success', 'Support ticket created.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['messages.user', 'order']);

        return view('account.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorize('reply', $ticket);

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_admin' => false,
            'message' => $request->message,
        ]);

        $ticket->update(['status' => 'OPEN']);

        return back()->with('success', 'Reply posted.');
    }
}
