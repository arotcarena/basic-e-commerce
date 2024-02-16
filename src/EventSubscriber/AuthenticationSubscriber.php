<?php

namespace App\EventSubscriber;

use App\Config\SiteConfig;
use App\Config\TextConfig;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    public function onAuthentication(AuthenticationSuccessEvent $event): void
    {
        /** @var User */
        $user = $event->getAuthenticationToken()->getUser();
        if(!$user->isConfirmed())
        {
            throw new AuthenticationException(TextConfig::ERROR_NOT_CONFIRMED_USER, 100);
        }
        if(in_array(SiteConfig::ROLE_USER_RESTRICTED, $user->getRoles()))
        {
            throw new AuthenticationException(TextConfig::ERROR_RESTRICTED_USER, 100);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => 'onAuthentication',
        ];
    }
}
