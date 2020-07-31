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
 * Ce subscriber permet de personnaliser le processus d'authentification JWT de Lexik.
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
     * On personnalise les réponses retournées en cas d'erreur d'authentification.
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $message = 'Votre identifiant ou mot de passe est incorrect';
        if ($event instanceof JWTInvalidEvent) {
            $message = 'Le token JWT est invalide';
        } elseif ($event instanceof JWTNotFoundEvent) {
            $message = 'Le token JWT est introuvable';
        } elseif ($event instanceof JWTExpiredEvent) {
            $message = 'Le token JWT est expiré';
        }

        // On ne passe pas par le listener d'exceptions car dans ce cas l'événement "kernel.exception" n'est pas levé
        // On passe quand même par une exception personnalisée pour bénéficier du format
        $exception = new AuthenticationException($message);

        $event->setResponse(new JsonResponse(
            $exception->toArray(),
            $exception->getStatusCode()
        ));
    }

    /**
     * On peut ajouter ici des infos sur l'utilisateur dans le payload.
     *
     * Attention: ne pas mettre d'infos trop volumineuses car ce token est envoyé à chaque requête.
     *
     * @param JWTCreatedEvent $event
     */
    public function customizeJWTPayload(JWTCreatedEvent $event)
    {
        $payload = $event->getData();

        // Récupération de la liste complète des rôles
        $payload['roles'] = $this->roleHierarchy->getReachableRoleNames($payload['roles']);
    }

    public static function getSubscribedEvents()
    {
        return [
            // Gestion des erreurs
            'lexik_jwt_authentication.on_authentication_failure' => 'onAuthenticationFailure',
            'lexik_jwt_authentication.on_jwt_invalid' => 'onAuthenticationFailure',
            'lexik_jwt_authentication.on_jwt_not_found' => 'onAuthenticationFailure',
            'lexik_jwt_authentication.on_jwt_expired' => 'onAuthenticationFailure',
            // Ajout d'infos dans le payload du JWT
            'lexik_jwt_authentication.on_jwt_created' => 'customizeJWTPayload',
        ];
    }
}
