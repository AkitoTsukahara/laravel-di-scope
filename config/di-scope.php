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
    | Scan Settings
    |--------------------------------------------------------------------------
    |
    | Configure which directories to scan for classes.
    |
    */
    'scan' => [
        /*
        |----------------------------------------------------------------------
        | Scan Paths
        |----------------------------------------------------------------------
        |
        | Directories to scan for classes. Paths are relative to base_path().
        |
        */
        'paths' => [
            'app/',
        ],

        /*
        |----------------------------------------------------------------------
        | Exclude Paths
        |----------------------------------------------------------------------
        |
        | Directories to exclude from scanning. Paths are relative to base_path().
        |
        */
        'exclude_paths' => [
            'app/Providers/',
            'app/Console/Kernel.php',
        ],

        /*
        |----------------------------------------------------------------------
        | Exclude Patterns
        |----------------------------------------------------------------------
        |
        | Class namespace patterns to exclude. Supports wildcards (*).
        |
        */
        'exclude_patterns' => [
            'App\\Providers\\*',
            'App\\Console\\*',
            'App\\Exceptions\\Handler',
        ],
    ],

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
    | Ignore Patterns (for dependency resolution)
    |--------------------------------------------------------------------------
    |
    | Dependencies matching these patterns will be excluded from analysis.
    | This affects the dependency tree, not the initial class scanning.
    |
    */
    'ignore' => [
        'Illuminate\\*',
        'Psr\\*',
        'Symfony\\*',
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
