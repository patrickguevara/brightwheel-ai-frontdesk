<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/chat', function () {
    return Inertia::render('Chat');
})->name('chat');

Route::get('/', function () {
    return redirect()->route('chat');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/api/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/api/chat/{sessionId}', [ChatController::class, 'getConversation'])->name('chat.show');

require __DIR__.'/settings.php';
