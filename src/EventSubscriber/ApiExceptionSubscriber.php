<?php

namespace App\EventSubscriber;

use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use \Throwable;

/**
 * Catches all the exceptions that were not catched before and converts them
 * to a correctly formatted JSON response.
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => [
                ['convertUnexpectedExceptionToApiException', -1],
                ['createResponseFromApiException', -2],
            ],
        ];
    }

    /**
     * Convert all the exceptions to ApiExceptions (unless they already are of this type).
     * @param ExceptionEvent $event
     */
    public function convertUnexpectedExceptionToApiException(ExceptionEvent $event): void
    {
        if (!($event->getThrowable() instanceof ApiException)) {
            $originalException = $event->getThrowable();

            $statusCode = $this->guessStatusCode($originalException);

            $apiException = new ApiException(
                $statusCode,
                null,
                $originalException->getMessage(),
                $originalException,
                $originalException->getCode()
            );

            $event->setThrowable($apiException);
        }
    }

    /**
     * Create a well formatted response from an ApiException.
     * @param ExceptionEvent $event
     */
    public function createResponseFromApiException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Warning: only process ApiException (the ones we are sure that we created)
        if ($exception instanceof ApiException) {
            $response = new JsonResponse($exception->toArray(), $exception->getStatusCode());
            $event->setResponse($response);
        }
    }

    /**
     * Return the right HTTP code to use for a given exception. Defaults to 500.
     * @param Throwable $e
     * @return int
     */
    private function guessStatusCode(\Throwable $e): int
    {
        // Exceptions that implement HttpException already have an HTTP status
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        // Defaults to 500 when the status can not be guessed
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
