<?php

namespace App\EventSubscriber;

use function json_last_error;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BeforeActionSubscriber
    implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelController',
        );
    }
    
    public function onKernelController(RequestEvent $event)
    {
        $request = $event->getRequest();
        
        if ($request->getContentType() != 'json' || !$request->getContent()) {
            return;
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $event->setResponse(new Response(json_encode(["errors" => [['error_on' => 'format', 'error_message' => 'json invalide']]]), 400, ['Content-Type' => 'application/json']));
        }
        
        $request->request->replace(is_array($data) ? $data : array());
    }
    
}