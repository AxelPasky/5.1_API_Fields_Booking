<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'docs/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // In produzione, specifica i domini permessi!
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
