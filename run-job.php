#!/usr/bin/env php
<?php

// 1. Error reporting - show all errors during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Try to bootstrap Laravel (with error handling)
try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
} catch (Exception $e) {
    file_put_contents(
        __DIR__.'/storage/logs/background_jobs_errors.log',
        date('Y-m-d H:i:s')." - Failed to bootstrap Laravel: ".$e->getMessage()."\n",
        FILE_APPEND
    );
    exit(1);
}

// 3. Check arguments
if (count($argv) < 3) {
    echo "Usage: php run-job.php ClassName methodName '{\"param1\":\"value\"}' [attempt=1]\n";
    echo "Example: php run-job.php \"App\\Jobs\\ProcessOrder\" \"handle\" '{\"orderId\":123}' 1\n";
    exit(1);
}

// 4. Get and validate arguments
$className = $argv[1];
$methodName = $argv[2];

// 5. Parse parameters as JSON (more flexible than comma-separated)
try {
    $params = isset($argv[3]) ? json_decode($argv[3], true) : [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON parameters");
    }
} catch (Exception $e) {
    file_put_contents(
        __DIR__.'/storage/logs/background_jobs_errors.log',
        date('Y-m-d H:i:s')." - JSON decode failed: ".$e->getMessage()."\n",
        FILE_APPEND
    );
    exit(1);
}

$attempt = isset($argv[4]) ? (int)$argv[4] : 1;

// 6. Log that we're starting the job
file_put_contents(
    __DIR__.'/storage/logs/background_jobs.log',
    date('Y-m-d H:i:s')." - Starting job: $className@$methodName (Attempt $attempt)\n",
    FILE_APPEND
);

// 7. Run the job with error handling
try {
    $runner = new App\BackgroundJobs\JobRunner();
    $runner->execute($className, $methodName, $params, $attempt);
} catch (Exception $e) {
    file_put_contents(
        __DIR__.'/storage/logs/background_jobs_errors.log',
        date('Y-m-d H:i:s')." - Job failed: $className@$methodName - ".$e->getMessage()."\n",
        FILE_APPEND
    );
    exit(1);
}

// 8. Success log
file_put_contents(
    __DIR__.'/storage/logs/background_jobs.log',
    date('Y-m-d H:i:s')." - Job completed: $className@$methodName\n",
    FILE_APPEND
);