<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Subscriber destine a ajouter des headers personnalises aux reponses API.
 */
class AdditionalHeadersSubscriber implements EventSubscriberInterface
{
    /**
     * Ajoute un header 'Vary' afin de preciser aux clients (navigateurs) de
     * ne pas mettre en cache les requetes provenant d'origines differentes.
     *
     * Facilite le developpement de clients sur differents environnements appelant
     * la meme instance de l'API.
     *
     * @param ResponseEvent $event
     * @return void
     */
    public function addVaryHeader(ResponseEvent $event)
    {
        $response = $event->getResponse();

        $response->headers->set('Vary', 'Origin');
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => 'addVaryHeader',
        ];
    }
}
