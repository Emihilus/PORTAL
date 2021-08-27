<?php

namespace App\EventSubscriber;

use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserAuthRejectorSubscriber implements EventSubscriberInterface
{
    public function onCheckPassportEvent(CheckPassportEvent $event)
    {
        if (!$event->getPassport() instanceof UserPassportInterface) {
            return;
        }

        $user = $event->getPassport()->getUser();
        
        if(!$user->isVerified())
            throw new CustomUserMessageAccountStatusException('Your user account is not verified.');

        if($user->isBanned())
            throw new CustomUserMessageAccountStatusException('You have been banned.');
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => 'onCheckPassportEvent',
        ];
    }
}
