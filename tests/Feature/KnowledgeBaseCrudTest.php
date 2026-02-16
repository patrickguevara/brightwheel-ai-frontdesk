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
