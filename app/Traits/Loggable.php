<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait Loggable
{
    /**
     * Log info message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logInfo(string $message, array $context = [])
    {
        Log::info($message, $context);
    }

    /**
     * Log error message
     * 
     * @param \Exception $exception
     * @param array $context
     * @return void
     */
    protected function logError(\Exception $exception, array $context = [])
    {
        Log::error($exception->getMessage(), array_merge($context, [
            'exception' => $exception,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]));
    }

    /**
     * Log warning message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logWarning(string $message, array $context = [])
    {
        Log::warning($message, $context);
    }

    /**
     * Log debug message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logDebug(string $message, array $context = [])
    {
        Log::debug($message, $context);
    }
}