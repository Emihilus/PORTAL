<?php
namespace App\Listener;

use App\Entity\Notification;

class OfferPassListener
{
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function onOfferPass($event)
    {
        $notification = new Notification();
        $notification->setRecipientUser($event->params['userPassed']);
        $notification->setRelatedEntity(['auction' => $event->params['relatedAuctionId']]);
        $notification->setMessage('Twoja oferta została przebita w aukcji '.$event->params['relatedAuctionTitle'].' przez użytkownika '.$event->params['passedByUser']);
        $this->em->persist($notification);
        $this->em->flush();
    }
}