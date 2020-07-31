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
 * Récupère toutes les exceptions qui n'ont pas été attrapées et les convertit en une réponse JSON correctement
 * formatée.
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
     * Convertit toute exception qui n'est pas du type ApiException en ApiException pour pouvoir retourner une réponse
     * correctement formatée.
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
     * Crée une réponse adaptée à partir d'une ApiException
     * @param ExceptionEvent $event
     */
    public function createResponseFromApiException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Attention : on ne traite l'exception que s'il s'agit d'une ApiException (donc créée par nos soins)
        if ($exception instanceof ApiException) {
            $response = new JsonResponse($exception->toArray(), $exception->getStatusCode());
            $event->setResponse($response);
        }
    }

    /**
     * Retourne le statut HTTP correspondant à une exception donnée. Par défaut on considèrera que l'on a une 500.
     * @param Throwable $e
     * @return int
     */
    private function guessStatusCode(\Throwable $e): int
    {
        // Les exceptions implémentant HttpExceptionInterface ont déjà une référence à un statut HTTP
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        // Par défaut on considère qu'il s'agit d'une 500 (l'exception n'est pas gérée)
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
