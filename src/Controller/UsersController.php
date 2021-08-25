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
     * @Route("/profile-details/{user}", name="profile-details")
     */
    public function profileDetails($user): Response
    {
        $USR = $this->getDoctrine()->getRepository(User::class)->findOneBy(['username' => $user]);

        return $this->render('userprofile/profile_details.html.twig', ['USR' => $USR]);
    }


    /**
     * @Route("/my-auctions/{page}", name="my-auctions", defaults = {"page": "1" })
     * @Route("/user-auctions/{username}/{page}", name="user-auctions", defaults = {"page": "1" })
     */
    public function myAuctions($page, ?User $user, Request $request): Response
    {
        $auctions = "";
        $em = $this->getDoctrine()->getManager();

        switch ($request->get('_route'))
        {
            case 'my-auctions';
                $auctions = $em->getRepository(Auction::class)->findAllWithFirstImageAndHighestOfferByUser($this->getUser());
                break;

            case 'user-auctions':
                $auctions = $em->getRepository(Auction::class)->findAllWithFirstImageAndHighestOfferByUser($user);
                break;
        }
        
        $allCount = count($auctions);

        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }
        $itemsPerPage = $_COOKIE['itemsPerPage'];

        $auctions = $this->paginator->paginate($auctions, $page, $itemsPerPage);

        return $this->render('userprofile/user_auctions.html.twig', [
            'auctions' => $auctions,
            'pages' => $allCount % $itemsPerPage === 0 ? $allCount / $itemsPerPage : intval($allCount / $itemsPerPage) + 1,
            'itemsPerPage' => $itemsPerPage
        ]);
    }
}
