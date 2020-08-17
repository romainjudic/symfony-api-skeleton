<?php

namespace App\EventSubscriber;

use App\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Subscriber for customizing the authentication process.
 */
class JwtAuthenticationSubscriber implements EventSubscriberInterface
{
    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;

    /**
     * JwtAuthenticationSubscriber constructor.
     * @param RoleHierarchyInterface $roleHierarchy
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Customize responses returned when authentication fails.
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $message = 'Wrong login/password';
        if ($event instanceof JWTInvalidEvent) {
            $message = 'Invalid token';
        } elseif ($event instanceof JWTNotFoundEvent) {
            $message = 'Token not found';
        } elseif ($event instanceof JWTExpiredEvent) {
            $message = 'The token is expired';
        }

        // We can not rely on the exception listener here because the "kernel.exceptions" is not thrown in that case.
        // We still use an ApiException to make sure the correct format is respected.
        $exception = new AuthenticationException($message);

        $event->setResponse(new JsonResponse(
            $exception->toArray(),
            $exception->getStatusCode()
        ));
    }

    /**
     * Add custom information about the user in the payload here.
     *
     * Warning: do not put large data here because this token is sent in every request.
     *
     * @param JWTCreatedEvent $event
     */
    public function customizeJWTPayload(JWTCreatedEvent $event)
    {
        $payload = $event->getData();

        // Retrieve the full list of roles
        $payload['roles'] = $this->roleHierarchy->getReachableRoleNames($payload['roles']);
    }

    public static function getSubscribedEvents()
    {
        return [
            // Error handling
            'lexik_jwt_authentication.on_authentication_failure' => 'onAuthenticationFailure',
            'lexik_jwt_authentication.on_jwt_invalid' => 'onAuthenticationFailure',
            'lexik_jwt_authentication.on_jwt_not_found' => 'onAuthenticationFailure',
            'lexik_jwt_authentication.on_jwt_expired' => 'onAuthenticationFailure',
            // Custom data in the JWT's payload
            'lexik_jwt_authentication.on_jwt_created' => 'customizeJWTPayload',
        ];
    }
}
