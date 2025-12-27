<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CoHostController;

// ============================================
// API ROUTES - MET /api/ PREFIX
// ============================================

Route::prefix('api')->group(function () {

    // EERST: Een simpele test route om te checken of API werkt
    Route::get('/test', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'API is working!',
            'timestamp' => now()->toDateTimeString(),
            'test_data' => [
                ['id' => 1, 'name' => 'Test Event 1'],
                ['id' => 2, 'name' => 'Test Event 2']
            ]
        ]);
    });

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
        Route::get('/user/events', function (\Illuminate\Http\Request $request) {
            return response()->json(['message' => 'User events endpoint']);
        });

        Route::get('/user/favorites', function (\Illuminate\Http\Request $request) {
            return response()->json(['message' => 'User favorites endpoint']);
        });

        Route::get('/user/tickets', function (\Illuminate\Http\Request $request) {
            return response()->json(['message' => 'User tickets endpoint']);
        });

        // Co-hosts
        Route::get('/events/{eventId}/co-hosts', [CoHostController::class, 'index']);
        Route::post('/events/{eventId}/co-hosts', [CoHostController::class, 'store']);
        Route::delete('/events/{eventId}/co-hosts/{userId}', [CoHostController::class, 'destroy']);
    });
});

// ============================================
// FRONTEND ROUTES (Vue.js)
// ============================================

Route::get('/', function () {
    return view('app');
});

// Catch-all voor Vue Router - maar EXCLUDE /api/ routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|storage).*$');
