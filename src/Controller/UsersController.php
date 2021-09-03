<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Auction;
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
        if($USR == null)
            $USR = $user;
        return $this->render('userprofile/profile_details.html.twig', ['USR' => $USR]);
    }


    /**
     * @Route("/user-auctions/{username}/{mode}/{page}", name="user-auctions", defaults = {"page": "1", "mode":"1" })
     */
    public function myAuctions($mode, $page, ?User $user, Request $request): Response
    {
       // $method = '';
       // $auctions = "";
        //$type = 1;
       // $em = $this->getDoctrine()->getManager();
        
        /*switch ($request->get('_route'))
        {
            case 'my-auctions';
                $auctions = $em->getRepository(Auction::class)->findAllWithFirstImageAndHighestOfferByUser($this->getUser());
                $type = 1;
                break;

            case 'user-auctions':*/
               // $type = 2;
                switch($mode)
                {
                    case 1:
                        $method = "AuctionsOfSpecificUser";
                        break;
                    
                    case 2:
                        $method = "SoldAuctionsOfUser";
                        break;

                    case 3:
                        $method = "CurrentAuctionsOfUser";
                        break;

                    case 4:
                        $method = "LeadingAuctionsOfUser";
                        break;

                    case 5:
                        $method = "WonAuctionsOfUser";
                        break;

                    case 6:
                        $method = "ParticipatingAuctionsOfUser";
                        break;

                    case 7:
                        $method = "ParticipatingNotLeadingAuctionsOfUser";
                        break;

                    case 8:
                        $method = "ParticipatedAuctionsOfUser";
                        break;

                    case 9:
                        $method = "ParticipatedNotLeadingAuctionsOfUser";
                        break;
                }
                // if($mode>1)
                // {
                //     $type=3;
                    //$assembliedMehtod='dql'.$method;
                    //$auctions = $em->getRepository(Auction::class)->$assembliedMehtod($user);
                //}
               /* break;
        }*/
        
        //$allCount = count($auctions);

        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }
        // $itemsPerPage = $_COOKIE['itemsPerPage'];

        //$auctions = $this->paginator->paginate($auctions, $page, $itemsPerPage);

        return $this->render('userprofile/user_auctions.html.twig', [
            /*'pages' => $allCount % $itemsPerPage === 0 ? $allCount / $itemsPerPage : intval($allCount / $itemsPerPage) + 1,
            'itemsPerPage' => $itemsPerPage,*/
            //'type' => $type,
            'method' => $method
        ]);
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
