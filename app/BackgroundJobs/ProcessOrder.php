<?php

namespace App\BackgroundJobs;

class ProcessOrder
{
    public function handle($orderId)
    {
        // Your job logic here
        logger("Processing order $orderId");
        return "Order $orderId processed successfully";
    }
}