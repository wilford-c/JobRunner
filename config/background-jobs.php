<?php

return [

    // Allow specific jobs and methods
    'allowed_jobs' => [
        // Format: 'ClassName' => ['allowed_methods' => ['method1', 'method2']]
        'App\Jobs\ExampleJob' => [
            'allowed_methods' => ['handle', 'process'],
        ],
        'App\BackgroundJobs\ProcessOrder' => [
            'allowed_methods' => ['handle'],
        ],
    ],

    // Allow entire namespaces (new addition)
    'allowed_namespaces' => [
        'App\\Jobs',
        'App\\BackgroundJobs',
    ],

    // Retry logic (default fallback)
    'retry' => [
        'attempts' => 3,
        'delay' => 5, // seconds between retries
    ],

    'retry_exceptions' => [
        Illuminate\Database\QueryException::class => [
            'attempts' => 5,
            'delay' => 10,
        ],
        RedisException::class => [
            'attempts' => 3,
            'delay' => 2,
        ],
    ],
    

    // Logging
    'log_path' => storage_path('logs/background_jobs.log'),
    'error_log_path' => storage_path('logs/background_jobs_errors.log'),
];
