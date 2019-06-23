<?php

return [
    'db' => [
        'driver'   => 'Pdo_Sqlite',
        'database' => __DIR__ . '/../../data/chat.db'
    ],
    'web_socket' => [
        'host' => '0.0.0.0',
        'port' => 8000
    ]
];