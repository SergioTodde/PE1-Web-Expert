<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CoHostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Password reset routes (public)
Route::post('/auth/password/reset-request', [AuthController::class, 'resetPasswordRequest']);
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
Route::post('/auth/password/verify-token', [AuthController::class, 'verifyResetToken']);

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password-reset', [AuthController::class, 'resetPassword']);

// Event routes (public)
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::get('/events/search', [EventController::class, 'search']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // User profile
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/change-password', [UserController::class, 'changePassword']);

    // Events
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::post('/events/{id}/favorite', [EventController::class, 'toggleFavorite']);

    // Tickets
    Route::get('/events/{eventId}/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);
    Route::post('/tickets/book', [TicketController::class, 'book']);

    // User specific data
    Route::get('/user/events', function (Request $request) {
        $events = $request->user()->events()->with('images')->get();
        return response()->json($events);
    });

    Route::get('/user/favorites', function (Request $request) {
        $favorites = $request->user()->favorites()->with('images')->get();
        return response()->json($favorites);
    });

    Route::get('/user/tickets', function (Request $request) {
        $tickets = $request->user()->bookings()->with('ticket.event.images')->get();
        return response()->json($tickets);
    });

    // Co-hosts
    Route::get('/events/{eventId}/co-hosts', [CoHostController::class, 'index']);
    Route::post('/events/{eventId}/co-hosts', [CoHostController::class, 'store']);
    Route::delete('/events/{eventId}/co-hosts/{userId}', [CoHostController::class, 'destroy']);
});
