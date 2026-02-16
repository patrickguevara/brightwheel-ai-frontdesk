<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\Operator\DashboardController;
use App\Http\Controllers\Operator\KnowledgeBaseController;
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

Route::middleware(['auth'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/conversations', [DashboardController::class, 'conversations'])->name('conversations');
    Route::get('/knowledge-base', [DashboardController::class, 'knowledgeBase'])->name('knowledge-base');
    Route::post('/knowledge-base', [KnowledgeBaseController::class, 'store'])->name('knowledge-base.store');
});

require __DIR__.'/settings.php';
