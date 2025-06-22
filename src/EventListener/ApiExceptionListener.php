<?php
// src/EventListener/ApiExceptionListener.php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use InvalidArgumentException;

/**
 * Catches exceptions for API routes (routes starting with /api)
 * and converts them into a standard JSON error response.
 */
class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // We only want to handle exceptions for our /api routes.
        // For other routes, we let the default Symfony error handling take over.
        if (strpos($request->getPathInfo(), '/api') !== 0) {
            return;
        }

        // Default error details
        $statusCode = 500;
        $message = 'An unexpected error occurred.';

        // If the exception is a standard Symfony HTTP exception, we use its code and message.
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }
        // We handle the validation exception from our DTOs specifically.
        elseif ($exception instanceof InvalidArgumentException) {
            $statusCode = 400; // Bad Request
            $message = $exception->getMessage();
        }

        // For a production environment, you might want to hide the details of 500 errors.
        // if ($statusCode === 500 && $this->env === 'prod') {
        //     $message = 'Internal Server Error';
        // }

        $errorData = [
            'error' => [
                'code' => $statusCode,
                'message' => $message,
            ],
        ];

        $response = new JsonResponse($errorData, $statusCode);

        // This replaces the original (ugly) error page with our nice JSON response.
        $event->setResponse($response);
    }
}
