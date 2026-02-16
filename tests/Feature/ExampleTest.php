<?php

test('home redirects to chat', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('chat'));
});

test('chat page loads successfully', function () {
    $response = $this->get(route('chat'));

    $response->assertOk();
});
