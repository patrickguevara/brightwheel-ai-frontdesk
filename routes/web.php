<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/api/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/api/chat/{sessionId}', [ChatController::class, 'getConversation'])->name('chat.show');

require __DIR__.'/settings.php';
