<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'search']);
    }

    public function index(Request $request)
    {
        $query = Event::with(['images', 'user', 'tickets'])
            ->published()
            ->withCount('favorites');

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filtering
        if ($request->has('category')) {
            $query->category($request->category);
        }

        if ($request->has('location')) {
            $query->location($request->location);
        }

        if ($request->has('date')) {
            $query->date($request->date);
        }

        $events = $query->orderBy('start_date')->get();

        // Separate favorites and other events
        $favoriteEvents = $events->where('is_favorite', true);
        $otherEvents = $events->where('is_favorite', false);

        return response()->json([
            'favorite_events' => $favoriteEvents,
            'other_events' => $otherEvents
        ]);
    }

    public function show($id)
    {
        $event = Event::with(['images', 'user', 'tickets', 'coHosts'])
            ->published()
            ->findOrFail($id);

        return response()->json($event);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'max_attendees' => 'nullable|integer|min:1',
            'ticket_sale_start' => 'required|date|after:now',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'location' => $request->location,
            'category' => $request->category,
            'max_attendees' => $request->max_attendees,
            'ticket_sale_start' => $request->ticket_sale_start,
            'user_id' => $request->user()->id,
            'is_published' => $request->user()->isAdmin(),
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('events', 'public');

                EventImage::create([
                    'event_id' => $event->id,
                    'image_path' => $path,
                    'is_primary' => $index === 0, // First image is primary
                ]);
            }
        }

        return response()->json($event->load('images'), 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if (!$request->user()->canEditEvent($event)) {
            return response()->json([
                'message' => 'Geen toestemming om dit evenement te bewerken'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date|after:now',
            'end_date' => 'sometimes|required|date|after:start_date',
            'location' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:100',
            'max_attendees' => 'nullable|integer|min:1',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $event->update($request->only([
            'title', 'description', 'start_date', 'end_date',
            'location', 'category', 'max_attendees'
        ]));

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('events', 'public');

                EventImage::create([
                    'event_id' => $event->id,
                    'image_path' => $path,
                    'is_primary' => false,
                ]);
            }
        }

        return response()->json($event->load('images'));
    }

    public function destroy(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if ($event->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Geen toestemming om dit evenement te verwijderen'
            ], 403);
        }

        // Delete associated images
        foreach ($event->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $event->delete();

        return response()->json([
            'message' => 'Evenement succesvol verwijderd'
        ]);
    }

    public function toggleFavorite(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = $request->user();

        if ($user->favorites()->where('event_id', $event->id)->exists()) {
            $user->favorites()->detach($event->id);
            $isFavorite = false;
        } else {
            $user->favorites()->attach($event->id);
            $isFavorite = true;
        }

        return response()->json([
            'is_favorite' => $isFavorite
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json([]);
        }

        $events = Event::with(['images', 'user'])
            ->published()
            ->search($query)
            ->limit(10)
            ->get();

        return response()->json($events);
    }
}
