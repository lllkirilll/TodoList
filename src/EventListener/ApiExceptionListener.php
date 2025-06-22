<?php

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

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $statusCode = 500;
        $message = 'An unexpected error occurred.';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } elseif ($exception instanceof InvalidArgumentException) {
            $statusCode = 400; // Bad Request
            $message = $exception->getMessage();
        }

        $errorData = [
            'error' => [
                'code' => $statusCode,
                'message' => $message,
            ],
        ];

        $response = new JsonResponse($errorData, $statusCode);

        $event->setResponse($response);
    }
}
