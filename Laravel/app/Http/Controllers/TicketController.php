<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Event;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($eventId)
    {
        $event = Event::published()->findOrFail($eventId);
        $tickets = $event->tickets()->where('is_active', true)->get();

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $event = Event::findOrFail($request->event_id);

        if (!$request->user()->canEditEvent($event)) {
            return response()->json([
                'message' => 'Geen toestemming om tickets toe te voegen'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity_available' => 'required|integer|min:1',
            'max_per_person' => 'required|integer|min:1',
            'sale_start_date' => 'required|date|after:now',
            'sale_end_date' => 'required|date|after:sale_start_date',
            'event_id' => 'required|exists:events,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = Ticket::create($request->all());

        return response()->json($ticket, 201);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::with('event')->findOrFail($id);

        if (!$request->user()->canEditEvent($ticket->event)) {
            return response()->json([
                'message' => 'Geen toestemming om deze ticket te bewerken'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity_available' => 'sometimes|required|integer|min:1',
            'max_per_person' => 'sometimes|required|integer|min:1',
            'sale_start_date' => 'sometimes|required|date',
            'sale_end_date' => 'sometimes|required|date|after:sale_start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket->update($request->all());

        return response()->json($ticket);
    }

    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::with('event')->findOrFail($id);

        if (!$request->user()->canEditEvent($ticket->event)) {
            return response()->json([
                'message' => 'Geen toestemming om deze ticket te verwijderen'
            ], 403);
        }

        if ($ticket->bookings()->where('status', 'confirmed')->exists()) {
            return response()->json([
                'message' => 'Kan ticket niet verwijderen omdat er al boekingen zijn'
            ], 422);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket succesvol verwijderd'
        ]);
    }

    public function book(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|exists:tickets,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = Ticket::findOrFail($request->ticket_id);

        if (!$ticket->canBook($request->quantity)) {
            return response()->json([
                'message' => 'Niet genoeg tickets beschikbaar of ticket niet beschikbaar'
            ], 422);
        }

        // Check if ticket sales have started
        if (now()->lt($ticket->event->ticket_sale_start)) {
            return response()->json([
                'message' => 'Ticketverkoop is nog niet gestart voor dit evenement'
            ], 422);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'ticket_id' => $ticket->id,
            'quantity' => $request->quantity,
            'total_amount' => $ticket->price * $request->quantity,
            'status' => 'confirmed', // Auto-confirm for simulation
        ]);

        // Update ticket sold count
        $ticket->increment('quantity_sold', $request->quantity);

        return response()->json($booking->load('ticket.event'), 201);
    }
}
