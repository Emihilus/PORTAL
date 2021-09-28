<?php

namespace App\Controller\AJAX;

use DateTime;
use App\Entity\Notification;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/ep")
 */
class NotificationsController extends AbstractController
{
    
    /**
     * @Route("/get-notifications", name="get-notifications")
     */
    public function myNotifications(): Response
    {
        $em = $this->getDoctrine()->getManager(); 
        $notifications = $em->getRepository(Notification::class)->findBy([
            'recipientUser' => $this->getUser()
        ]);

        $now = new DateTime();

        foreach ($notifications as $notification) 
        {
            $notification->getSeenAt() == null ? $notification->wasNull = true : $notification->wasNull = false ;

            if($notification->wasNull)
            {
                $notification->setSeenAt($now);
                $em->persist($notification);
            }
        }
        $em->flush();

        dump($notifications);

        return $this->render('parts/ajax/notifications_part.html.twig',[
            'notifications' => $notifications
        ]);
    }

    // GET UNREAD
    /**
     * @Route("/get-notifications-ur", name="get-notifications-ur")
     */
    public function getUnreadNotifications(): Response
    {
        $em = $this->getDoctrine()->getManager(); 
        $notifications = $em->getRepository(Notification::class)->findBy([
            'recipientUser' => $this->getUser(),
            'seenAt' => null
        ]);

       /* $now = new DateTime();

        foreach ($notifications as $notification) 
        {
            $notification->getSeenAt() == null ? $notification->wasNull = true : $notification->wasNull = false ;

            if($notification->wasNull)
            {
                $notification->setSeenAt($now);
                $em->persist($notification);
            }
        }
        $em->flush();

        dump($notifications);*/

        return $this->render('parts/ajax/notifications_part.html.twig',[
            'notifications' => $notifications
        ]);
    }

    // DONT MARK AS READ
    /**
     * @Route("/get-notifications-dm", name="get-notifications-dm")
     */
    public function getDontMarkNotifications(): Response
    {
        $em = $this->getDoctrine()->getManager(); 
        $notifications = $em->getRepository(Notification::class)->findBy([
            'recipientUser' => $this->getUser()
        ]);

        return $this->render('parts/ajax/notifications_part.html.twig',[
            'notifications' => $notifications
        ]);
        /*return new JsonResponse([
            'notifCount' => count($notifications),
            'render' => $this->render('parts/ajax/notifications_part.html.twig',[
                'notifications' => $notifications
            ])->getContent()
        ]);*/
    }

    /**
     * @Route("/mar-notifications", name="mar-notifications")
     */
    public function markNotificationsAsRead(): Response
    {
        $em = $this->getDoctrine()->getManager(); 

        $notifications = $em->getRepository(Notification::class)->findBy([
            'recipientUser' => $this->getUser(),
            'seenAt' => null
        ]);

        $now = new DateTime();

        foreach ($notifications as $notification) 
        {
            $notification->setSeenAt($now);
            $em->persist($notification);
        }
        $em->flush();

        dump($notifications);

        return new Response ('dene');
    }
}