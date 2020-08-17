<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Subscriber that adds custom headers to API responses.
 */
class AdditionalHeadersSubscriber implements EventSubscriberInterface
{
    /**
     * Add a "Vary" header to tell clients (browsers) not to cache
     * requests that come from different origins.
     *
     * Makes development easier for clients that use different environments of the same API.
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
