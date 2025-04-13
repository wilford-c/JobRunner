<?php

namespace App\Jobs;

class ExampleJob
{
    public function handle($param1, $param2)
    {
        // Simulate some work
        sleep(2);
        
        return "Processed with $param1 and $param2";
    }
    
    public function process($data)
    {
        // Another allowed method
        return strtoupper($data);
    }
}