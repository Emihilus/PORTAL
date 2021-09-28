<?php

namespace App\Controller;

use DateTime;
use App\Entity\TempImage;
use App\Entity\Notification;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CRONTasksController extends AbstractController
{
    // CRON TASK
    /**
     * @Route("/ep/cw", name="cronW")
     */
    public function cronW()
    {
        $em = $this->getDoctrine()->getManager();

        $this->deleteOldTempImages($em);

        // QUERY BUILDER DESIGN - 32 QUERIES
        /*$qb = $em->createQueryBuilder();
        $qb->select('a')
        ->from('App\Entity\Auction', 'a')
        ->where('a.endsAt < :now')
        ->andWhere('a.notificationHandled = false')
        ->leftJoin('a.offers', 'o')
        ->leftJoin('o.byUser', 'u')
        ->setParameter('now', new DateTime());
        $auctions = $qb->getQuery()->getResult();*/

        // QUERY BUILDER FOREACH
        /* foreach ($auctions as $auction) 
        {
            $winNotification = new Notification();

            $hghstOffer = null;
            foreach ($auction->getOffer() as $offer) 
            {
                if($hghstOffer == null || $offer->getValue() > $hghstOffer->getValue())
                    $hghstOffer = $offer;
            }
            $winNotification->setRecipientUser($hghstOffer->getByUser());
            $winNotification->setMessage('Wygrales aukcje '.$auction->getTitle());
            $em->persist($winNotification);
        }*/


        $dql = "SELECT a,
        (SELECT zu.id
        FROM App\Entity\Offer zo
        LEFT JOIN App\Entity\User zu WITH zo.byUser=zu
        WHERE zo.auction=a
        AND zo.Value=(SELECT MAX(fo.Value) FROM App\Entity\Offer fo WHERE fo.auction=zo.auction)
        ) as hghstOfferOwner
        
        FROM App\Entity\Offer o
        
        LEFT JOIN App\Entity\Auction a WITH a=o.auction
        
        WHERE a.endsAt < ?1 
        AND a.notificationHandled = false";

        $auctions = $em->createQuery($dql)
        ->setParameter(1,new DateTime())->getResult();


        foreach ($auctions as $auction) 
        {
            $winNotification = new Notification();

            $winNotification->setRecipientUser($em->getReference('App\Entity\User', $auction['hghstOfferOwner']));
            $winNotification->setMessage('Wygrales aukcje '.$auction[0]->getTitle());
            $winNotification->setRelatedEntity(['auction' => $auction[0]->getId()]);
            $em->persist($winNotification);

            $sellNotification = new Notification();
            $sellNotification->setRecipientUser($auction[0]->getByUser());
            $sellNotification->setMessage('Sprzedałeś aukcje '.$auction[0]->getTitle());
            $sellNotification->setRelatedEntity(['auction' => $auction[0]->getId()]);
            $em->persist($sellNotification);

            $auction[0]->setNotificationHandled(true);
            $em->persist($auction[0]);
        }


        
        // BUYER COMMENT NOTIFICATION

        //  QUERYBUILDER DESIGN
        /*$qb = $em->createQueryBuilder();
        $qb->select('c')
        ->from('App\Entity\Comment', 'c')
        ->leftJoin('c.auction', 'a')
        ->where('c.notificationHandled = false')
        ->andWhere($qb->expr()->neq('c.value', -2));
        $comments = $qb->getQuery()->getResult();

        foreach ($comments as $comment) 
        {
            $buyerCommentNotification = new Notification();
            $buyerCommentNotification->setRecipientUser($comment->getAuction()->getByUser());
            $buyerCommentNotification->setMessage('Otrzymałeś komenatrz sprzedaży dot aukcji '.$comment->getAuction()->getTitle());
            $em->persist($buyerCommentNotification);
        }*/


        
        $dql = "SELECT u.id, a.title, c, u.username, c.value
        
        FROM App\Entity\User u
        
        LEFT JOIN App\Entity\Auction a WITH u=a.byUser
        LEFT JOIN App\Entity\Comment c WITH a=c.auction
        
        WHERE c.value > -2 
        AND c.notificationHandled = false";

        $comments = $em->createQuery($dql)->getResult();
        
        // Sending Buyer Comment notifications through this CRON Task endpoint due to automatic buyer comment creation in DataFixtures, in real scenario better solution is to use EventSubscriber which is already implemented in case of posting regular comment reply.
        foreach ($comments as $comment) 
        {
            $buyerCommentNotification = new Notification();

            if($comment['value'] > 1)
            {
                $buyerCommentNotification->setRecipientUser($comment[0]->getReplyTo()->getByUser());
                $buyerCommentNotification->setMessage('Sprzedwca '.$comment['username'].' odpowiedział na Twój komentarz dotyczący aukcji '.$comment['title']);
                $buyerCommentNotification->setRelatedEntity(['username' => $comment['username']]);
            }
            else
            {
                $buyerCommentNotification->setRecipientUser($em->getReference('App\Entity\User', $comment['id']));
                $buyerCommentNotification->setMessage('Otrzymałeś komenatrz sprzedaży dot aukcji '.$comment['title']);
                $buyerCommentNotification->setRelatedEntity(['username' => $comment['username']]);
            }
            $em->persist($buyerCommentNotification);

            $comment[0]->setNotificationHandled(true);
            $em->persist($comment[0]);
        }


        // REMOVE OLD NOTIFICATONS
        $now = new DateTime('-1 hour');

        $qb = $em->createQueryBuilder();
        $qb->select('n')
        ->from('App\Entity\Notification', 'n')
        ->where($qb->expr()->lt('n.seenAt', ':date'))
        ->setParameter('date', $now);
        $notifications = $qb->getQuery()->getResult();

        foreach ($notifications as $notification) 
        {
            $em->remove($notification);
        }
        $em->flush();

        // dump($notifications);
        // dump($auctions);
        // dump($comments);



        // return $this->render('z_not_used/tst.twig');
        return new Response("Executed");
    }

    private function deleteOldTempImages($em)
    {
        $tempImages = $em->getRepository(TempImage::class)->findAll();
        $now = new DateTime();

        foreach ($tempImages as $tempImage) 
        {
            
            if($now->getTimestamp() - $tempImage->getCreatedAt()->getTimestamp()> 600)
            {
                try
                {
                    $result = unlink($this->getParameter('tempImagePath').$tempImage->getFilename());
                }
                catch (\Exception $e)
                {
                    if($e->getCode() == 0)
                        $result = true;
                }
                
                if($result)
                {
                    $em->remove($tempImage);
                }
                
            }
        }
        $em->flush();
    }
}
