<?php

use App\Models\User;

test('create requires category title and content', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/operator/knowledge-base', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category', 'title', 'content']);
});

test('category must be valid enum value', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/operator/knowledge-base', [
        'category' => 'invalid-category',
        'title' => 'Test Title',
        'content' => 'Test content',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category']);
});

test('authenticated user can create knowledge base entry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/operator/knowledge-base', [
        'category' => 'hours',
        'title' => 'Test Entry',
        'content' => 'Test content here',
        'keywords' => ['test', 'keyword'],
        'is_active' => true,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('knowledge_base', [
        'category' => 'hours',
        'title' => 'Test Entry',
        'content' => 'Test content here',
        'updated_by' => $user->id,
    ]);
});

test('keywords are stored as array', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/operator/knowledge-base', [
        'category' => 'general',
        'title' => 'Keywords Test',
        'content' => 'Testing keywords',
        'keywords' => ['key1', 'key2', 'key3'],
    ]);

    $entry = \App\Models\KnowledgeBase::where('title', 'Keywords Test')->first();

    expect($entry->keywords)->toBe(['key1', 'key2', 'key3']);
});

test('authenticated user can update knowledge base entry', function () {
    $user = User::factory()->create();
    $entry = \App\Models\KnowledgeBase::factory()->create([
        'title' => 'Original Title',
        'content' => 'Original content',
    ]);

    $response = $this->actingAs($user)->putJson("/operator/knowledge-base/{$entry->id}", [
        'category' => 'general',
        'title' => 'Updated Title',
        'content' => 'Updated content',
        'keywords' => ['updated'],
        'is_active' => false,
    ]);

    $response->assertStatus(200);

    $entry->refresh();

    expect($entry->title)->toBe('Updated Title')
        ->and($entry->content)->toBe('Updated content')
        ->and($entry->updated_by)->toBe($user->id)
        ->and($entry->is_active)->toBeFalse();
});

test('authenticated user can delete knowledge base entry', function () {
    $user = User::factory()->create();
    $entry = \App\Models\KnowledgeBase::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/operator/knowledge-base/{$entry->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('knowledge_base', [
        'id' => $entry->id,
    ]);
});
