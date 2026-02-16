<?php

return [
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
        'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 1024),
    ],

    'confidence' => [
        'high_threshold' => 0.8,
        'medium_threshold' => 0.5,
    ],

    'sensitive_keywords' => [
        'specific child', 'billing dispute', 'complaint', 'custody',
        'abuse', 'neglect', 'staff issue', 'tour scheduling',
        'schedule a tour', 'visit', 'door code', 'security code',
    ],
];
