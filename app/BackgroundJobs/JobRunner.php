<?php

namespace App\BackgroundJobs;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use RedisException;
use Exception;
use InvalidArgumentException;

class JobRunner
{
    public function execute(string $className, string $methodName, array $params = [], int $attempt = 1)
    {
        $this->log("Starting job {$className}@{$methodName}", [
            'class' => $className,
            'method' => $methodName,
            'params' => $params,
            'attempt' => $attempt,
        ]);

        try {
            $this->validateJob($className, $methodName);
            
            $instance = app()->make($className);
            
            $result = call_user_func_array([$instance, $methodName], $params);
            
            $this->log("Job completed successfully", [
                'class' => $className,
                'method' => $methodName,
                'result' => $result,
                'status' => 'success',
            ]);
            
            return $result;
        } catch (Exception $e) {
            $this->handleError($className, $methodName, $params, $attempt, $e);
            throw $e;
        }
    }

    protected function validateJob(string $className, string $methodName)
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Class {$className} does not exist", 404);
        }

        if (isset(config('background-jobs.allowed_jobs')[$className])) {
            if (!in_array($methodName, config('background-jobs.allowed_jobs')[$className]['allowed_methods'])) {
                throw new InvalidArgumentException("Method {$methodName} not allowed for class {$className}", 403);
            }
            return;
        }

        foreach (config('background-jobs.allowed_namespaces', []) as $namespace) {
            if (str_starts_with($className, $namespace)) {
                return;
            }
        }

        throw new InvalidArgumentException("Class {$className} is not allowed", 403);
    }

    protected function handleError(string $className, string $methodName, array $params, int $attempt, Exception $e)
    {
        if ($e instanceof InvalidArgumentException) {
            return;
        }

        $maxAttempts = config('background-jobs.retry.attempts');
        $defaultDelay = config('background-jobs.retry.delay');
        
        foreach (config('background-jobs.retry_exceptions', []) as $exceptionType => $rules) {
            if ($e instanceof $exceptionType) {
                $maxAttempts = $rules['attempts'];
                $defaultDelay = $rules['delay'];
                break;
            }
        }

        $this->logError("Job failed: " . $e->getMessage(), [
            'class' => $className,
            'method' => $methodName,
            'exception' => get_class($e),
            'stack_trace' => $e->getTraceAsString(),
            'attempt' => $attempt,
            'max_attempts' => $maxAttempts,
            'next_delay' => $defaultDelay,
        ]);

        if ($attempt < $maxAttempts) {
            sleep($defaultDelay);
            $this->execute($className, $methodName, $params, $attempt + 1);
        }
    }

    protected function log(string $message, array $context = [])
    {
        $logPath = config('background-jobs.log_path');
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n" . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        file_put_contents($logPath, $logMessage, FILE_APPEND);
    }

    protected function logError(string $message, array $context = [])
    {
        $logPath = config('background-jobs.error_log_path');
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n" . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        file_put_contents($logPath, $logMessage, FILE_APPEND);
    }
}