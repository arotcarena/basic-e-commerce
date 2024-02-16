<?php

namespace App\EventSubscriber;

use App\Config\TextConfig;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $request
    )
    {

    }

    public function onLogout($event): void
    {
        /** @var FlashBag */
        $flashBag = $this->request->getSession()->getBag('flashes');
        $flashBag->add('success', TextConfig::ALERT_LOGOUT_SUCCESS);
        //suppression des données du formulaire de checkout dans le sessionStorage
        $flashBag->add('sessionStorage_remove', json_encode(['checkout', 'check_st']));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
