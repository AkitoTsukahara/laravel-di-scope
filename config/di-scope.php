<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable/Disable DI Scope
    |--------------------------------------------------------------------------
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Dependency Rules
    |--------------------------------------------------------------------------
    |
    | Define which dependencies are allowed or denied for each layer/namespace.
    |
    | Example:
    | 'App\\Domain\\*' => [
    |     'deny' => ['App\\Infrastructure\\*'],
    |     'allow' => ['App\\Domain\\*', 'App\\Application\\*'],
    | ],
    |
    */
    'rules' => [],

    /*
    |--------------------------------------------------------------------------
    | Ignore Patterns
    |--------------------------------------------------------------------------
    |
    | Classes matching these patterns will be excluded from analysis.
    |
    */
    'ignore' => [
        'Illuminate\\*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Output Settings
    |--------------------------------------------------------------------------
    */
    'output' => [
        'format' => 'mermaid',
        'colors' => [
            'valid' => '#22c55e',
            'violation' => '#ef4444',
            'warning' => '#f59e0b',
        ],
    ],
];
