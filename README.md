<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Laravel Custom Background Job Runner

A platform-independent background job processing system for Laravel that doesn't use Laravel's built-in queue system.

## Features

- 🚀 Execute PHP classes as background jobs
- 💻 Cross-platform support (Windows & Unix)
- 🔄 Configurable retry logic with exception-specific rules
- 🔒 Security whitelisting for classes/methods
- 📝 Detailed execution logging
- ⏱ Optional delayed execution
- 🏷 Optional job priorities

## Installation

1. Require the package:
```bash
composer require yourpackage/job-runner

Publish the config file:
php artisan vendor:publish --tag=job-runner-config

Configuration

Edit config/background-jobs.php:

return [
    // Allowed specific classes and methods
    'allowed_jobs' => [
        'App\Jobs\ProcessOrder' => [
            'allowed_methods' => ['handle']
        ],
    ],
    
    // Allowed entire namespaces
    'allowed_namespaces' => [
        'App\BackgroundJobs',
    ],
    
    // Default retry settings
    'retry' => [
        'attempts' => 3,       // Max retry attempts
        'delay' => 5,          // Seconds between retries
    ],
    
    // Exception-specific retry rules
    'retry_exceptions' => [
        Illuminate\Database\QueryException::class => [
            'attempts' => 5,
            'delay' => 10,
        ],
    ],
    
    // Log file paths
    'log_path' => storage_path('logs/background_jobs.log'),
    'error_log_path' => storage_path('logs/background_jobs_errors.log'),
];

Usage
Using the Helper Function
// Simple job
runBackgroundJob('App\Jobs\ProcessOrder', 'handle', ['order_id' => 123]);

// With 60-second delay
runBackgroundJob('App\Jobs\Cleanup', 'run', [], 60);

CLI Execution

php run-job.php "App\Jobs\ExampleJob" "handle" '{"param1":"value"}'

License
This package is open-source software licensed under the MIT license.

Laravel Sponsors
We would like to extend our thanks to the Laravel sponsors for funding Laravel development



