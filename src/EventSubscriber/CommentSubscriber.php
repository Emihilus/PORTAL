<?php

namespace App\EventSubscriber;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentSubscriber implements EventSubscriberInterface
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onCommentRegularReply($event)
    {
        $notification = new Notification();
        $notification->setRecipientUser($event->params['recipientComment']->getByUser());
        $notification->setRelatedEntity(['auction' => $event->params['relatedComment']->getAuction()->getId()]);
        $notification->setMessage('Użytkownik '.$event->params['relatedComment']->getByUser().' odpowiedział na Twój komentarz w aukcji '.$event->params['relatedComment']->getAuction()->getTitle());
        $this->em->persist($notification);
        $this->em->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            'auction.comment_regular_reply' => 'onCommentRegularReply',
        ];
    }
}
