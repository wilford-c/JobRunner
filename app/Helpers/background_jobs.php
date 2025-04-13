<?php

use Symfony\Component\Process\Process;

if (!function_exists('runBackgroundJob')) {
    function runBackgroundJob($class, $method, $params = [])
    {
        $paramsString = implode(',', array_map(function($param) {
            return is_string($param) ? escapeshellarg($param) : $param;
        }, $params));
        
        $command = [
            PHP_BINARY,
            base_path('run-job.php'),
            $class,
            $method,
            $paramsString
        ];
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $process = new Process($command);
            $process->setOptions(['create_new_console' => true]);
            $process->start();
        } else {
            // Unix
            $command = 'nohup ' . implode(' ', array_map('escapeshellarg', $command)) . ' > /dev/null 2>&1 &';
            shell_exec($command);
        }
    }
}