<?php

return [
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
        'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 1024),
    ],

    'confidence' => [
        'high_threshold' => 0.8,
        'medium_threshold' => 0.5,
        'no_knowledge' => 0.3,
        'single_knowledge' => 0.7,
        'multiple_knowledge' => 0.9,
    ],

    'sensitive_keywords' => [
        'specific child', 'billing dispute', 'complaint', 'custody',
        'abuse', 'neglect', 'staff issue', 'tour scheduling',
        'schedule a tour', 'visit', 'door code', 'security code',
    ],
];
