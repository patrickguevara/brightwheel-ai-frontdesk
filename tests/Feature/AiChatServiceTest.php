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

test('it generates a response with AI', function () {
    $knowledge = collect([
        KnowledgeBase::factory()->create([
            'category' => 'hours',
            'title' => 'Hours of Operation',
            'content' => 'Open Monday-Friday 6:30 AM to 6:30 PM',
            'keywords' => ['hours', 'open', 'close'],
        ]),
    ]);

    $response = $this->service->generateResponse('What are your hours?', $knowledge);

    expect($response)->toHaveKeys(['content', 'confidence', 'escalated']);
    expect($response['content'])->toBeString();
    expect($response['confidence'])->toBeFloat();
    expect($response['escalated'])->toBeBool();
});

test('it escalates sensitive questions in generateResponse', function () {
    $knowledge = collect();

    $response = $this->service->generateResponse('I want to schedule a tour', $knowledge);

    expect($response['escalated'])->toBeTrue();
    expect($response['confidence'])->toBe(1.0);
    expect($response['content'])->toContain('operator');
});

test('it calculates confidence based on knowledge count', function () {
    $emptyKnowledge = collect();
    $singleKnowledge = collect([
        KnowledgeBase::factory()->create(),
    ]);
    $multipleKnowledge = collect([
        KnowledgeBase::factory()->create(),
        KnowledgeBase::factory()->create(),
    ]);

    expect($this->service->calculateConfidence($emptyKnowledge))->toBe(0.3);
    expect($this->service->calculateConfidence($singleKnowledge))->toBe(0.7);
    expect($this->service->calculateConfidence($multipleKnowledge))->toBe(0.9);
});
