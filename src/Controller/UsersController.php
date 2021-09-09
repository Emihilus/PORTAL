<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Auction;
use App\Entity\Notification;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UsersController extends AbstractController
{
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    

    /**
     * @Route("/my-profile", name="my-profile")
     */
    public function myProfile(): Response
    {
        return $this->render('userprofile/my_profile.html.twig');
    }

    /**
     * @Route("/profile-details/{username}", name="profile-details")
     */
    public function profileDetails(User $user): Response
    {
        $USR = $this->getDoctrine()->getRepository(User::class)->dqlUserInfoCollection($user);
        dump($USR);
        if($USR == null)
            $USR = $user;
        return $this->render('userprofile/profile_details.html.twig', ['USR' => $USR]);
    }


    /**
     * @Route("/user-auctions/{username}/{mode}/{page}", name="user-auctions", defaults = {"page": "1", "mode":"1" })
     */
    public function myAuctions($mode, $page, ?User $user, Request $request): Response
    {
       
        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }
      
        return $this->render('userprofile/user_auctions.html.twig', [
            'mMode' => $mode
        ]);
    }

    /**
     * @Route("/comment-all-auctions/{page}", name="comment-all-auctions", defaults = {"page": "1" })
     */
    public function commentAllAuctions(): Response
    {  
        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }
      
        return $this->render('userprofile/comment_allauctions.html.twig' );
    }



    /**
     * @Route("/users-list", name="users-list")
     */
    public function usersList(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('userprofile/users_list.html.twig',[
            'users' => $users
        ]);
    }

    /**
     * @Route("/my-notifications", name="my-notifications")
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

        return $this->render('userprofile/my_notifications.html.twig',[
            'notifications' => $notifications
        ]);
    }

    /**
     * @Route("/dql", name="dql")
     */
    public function dql(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $auc = $em->getRepository(Auction::class)->dqlHasntWonAuctionsOfUser($this->getUser());

        return $this->render('z_not_used/tst.twig',[
            'auc' => $auc
        ]);
    }
}
