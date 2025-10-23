<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CoHostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index($eventId)
    {
        $event = Event::findOrFail($eventId);
        $coHosts = $event->coHosts;

        return response()->json($coHosts);
    }

    public function store(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Alleen de eigenaar kan co-hosts toevoegen'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $coHost = User::where('email', $request->email)->first();

        if ($event->coHosts()->where('user_id', $coHost->id)->exists()) {
            return response()->json([
                'message' => 'Deze gebruiker is al co-host voor dit evenement'
            ], 422);
        }

        $event->coHosts()->attach($coHost->id, ['role' => 'co-host']);

        return response()->json([
            'message' => 'Co-host succesvol toegevoegd',
            'co_host' => $coHost
        ], 201);
    }

    public function destroy(Request $request, $eventId, $userId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Alleen de eigenaar kan co-hosts verwijderen'
            ], 403);
        }

        $event->coHosts()->detach($userId);

        return response()->json([
            'message' => 'Co-host succesvol verwijderd'
        ]);
    }
}
