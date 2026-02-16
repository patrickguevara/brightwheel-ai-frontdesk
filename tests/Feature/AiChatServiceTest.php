<?php

use App\Models\KnowledgeBase;
use App\Services\AiChatService;

beforeEach(function () {
    $this->service = app(AiChatService::class);
});

test('it retrieves relevant knowledge for a question', function () {
    KnowledgeBase::factory()->create([
        'category' => 'hours',
        'title' => 'Hours of Operation',
        'content' => 'Open Monday-Friday 6:30 AM to 6:30 PM',
        'keywords' => ['hours', 'open', 'close'],
    ]);

    $knowledge = $this->service->retrieveRelevantKnowledge('What are your hours?');

    expect($knowledge)->toHaveCount(1);
    expect($knowledge->first()->title)->toBe('Hours of Operation');
});

test('it detects sensitive topics requiring escalation', function () {
    $result = $this->service->shouldEscalate('I want to schedule a tour');

    expect($result)->toBeTrue();
});

test('it does not escalate general questions', function () {
    $result = $this->service->shouldEscalate('What are your hours?');

    expect($result)->toBeFalse();
});
