<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load(['favorites.images', 'bookings.ticket.event.images']);

        $attendedEvents = $user->bookings()
            ->with('ticket.event')
            ->whereHas('ticket.event', function ($query) {
                $query->where('end_date', '<', now());
            })
            ->get()
            ->pluck('ticket.event')
            ->unique();

        $upcomingEvents = $user->bookings()
            ->with('ticket.event')
            ->whereHas('ticket.event', function ($query) {
                $query->where('end_date', '>=', now());
            })
            ->get()
            ->pluck('ticket.event')
            ->unique();

        return response()->json([
            'user' => $user,
            'favorite_events' => $user->favorites,
            'attended_events' => $attendedEvents,
            'upcoming_events' => $upcomingEvents,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'phone']));

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return response()->json($user);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Huidig wachtwoord is incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Wachtwoord succesvol gewijzigd'
        ]);
    }
}
