<?php

use App\Models\Conversation;
use App\Models\KnowledgeBase;
use App\Models\Message;
use App\Services\AiChatService;

test('it creates new conversation when session_id not provided', function () {
    $knowledge = collect([
        KnowledgeBase::factory()->create([
            'category' => 'hours',
            'title' => 'Hours of Operation',
            'content' => 'Open Monday-Friday 6:30 AM to 6:30 PM',
            'keywords' => ['hours', 'open', 'close'],
        ]),
    ]);

    $mockService = Mockery::mock(AiChatService::class);
    $mockService->shouldReceive('retrieveRelevantKnowledge')
        ->once()
        ->with('What are your hours?')
        ->andReturn($knowledge);

    $mockService->shouldReceive('generateResponse')
        ->once()
        ->with('What are your hours?', $knowledge)
        ->andReturn([
            'content' => 'We are open Monday-Friday 6:30 AM to 6:30 PM.',
            'confidence' => 0.9,
            'escalated' => false,
        ]);

    $this->app->instance(AiChatService::class, $mockService);

    $response = $this->postJson('/api/chat', [
        'message' => 'What are your hours?',
        'parent_name' => 'John Doe',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'session_id',
            'message' => [
                'id',
                'role',
                'content',
                'confidence_score',
                'should_escalate',
                'created_at',
            ],
        ]);

    expect(Conversation::count())->toBe(1);
    expect(Message::count())->toBe(2); // parent message + assistant message

    $conversation = Conversation::first();
    expect($conversation->parent_name)->toBe('John Doe');
    expect($conversation->status)->toBe('active');
});

test('it continues existing conversation when session_id provided', function () {
    $conversation = Conversation::factory()->create();

    $knowledge = collect([
        KnowledgeBase::factory()->create([
            'category' => 'hours',
            'title' => 'Hours of Operation',
            'content' => 'Open Monday-Friday 6:30 AM to 6:30 PM',
            'keywords' => ['hours', 'open', 'close'],
        ]),
    ]);

    $mockService = Mockery::mock(AiChatService::class);
    $mockService->shouldReceive('retrieveRelevantKnowledge')
        ->once()
        ->andReturn($knowledge);

    $mockService->shouldReceive('generateResponse')
        ->once()
        ->andReturn([
            'content' => 'We are open Monday-Friday 6:30 AM to 6:30 PM.',
            'confidence' => 0.9,
            'escalated' => false,
        ]);

    $this->app->instance(AiChatService::class, $mockService);

    $response = $this->postJson('/api/chat', [
        'message' => 'What are your hours?',
        'session_id' => $conversation->session_id,
    ]);

    $response->assertStatus(200);

    expect(Conversation::count())->toBe(1);
    expect($conversation->fresh()->messages()->count())->toBe(2);
});

test('it escalates sensitive questions', function () {
    $knowledge = collect();

    $mockService = Mockery::mock(AiChatService::class);
    $mockService->shouldReceive('retrieveRelevantKnowledge')
        ->once()
        ->andReturn($knowledge);

    $mockService->shouldReceive('generateResponse')
        ->once()
        ->andReturn([
            'content' => 'This question requires personal attention from our staff. An operator will assist you shortly.',
            'confidence' => 1.0,
            'escalated' => true,
        ]);

    $this->app->instance(AiChatService::class, $mockService);

    $response = $this->postJson('/api/chat', [
        'message' => 'I want to schedule a tour',
        'parent_name' => 'Jane Smith',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => [
                'should_escalate' => true,
            ],
        ]);

    $conversation = Conversation::first();
    expect($conversation->status)->toBe('escalated');
});
