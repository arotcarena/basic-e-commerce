<?php

namespace App\EventSubscriber;

use App\Config\TextConfig;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoginSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $request,
        private CartService $cartService
    )
    {

    }


    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var FlashBag */
        $flashBag = $this->request->getSession()->getBag('flashes');
        $flashBag->add('success', TextConfig::ALERT_LOGIN_SUCCESS);
        //suppression des données du formulaire de checkout dans le sessionStorage
        $flashBag->add('sessionStorage_remove', json_encode(['checkout', 'check_st']));

        $this->cartService->onLoginUpdate($event->getUser());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }
}
