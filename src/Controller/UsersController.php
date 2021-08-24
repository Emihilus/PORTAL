<?php

namespace App\Controller;

use App\Entity\Auction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UsersController extends AbstractController
{
    /**
     * @Route("/my-profile", name="my-profile")
     */
    public function myProfile(): Response
    {
        return $this->render('userprofile/my_profile.html.twig');
    }


    /**
     * @Route("/my-auctions", name="my-auctions")
     */
    public function myAuctions(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $auctions = $em->getRepository(Auction::class)->findAllWithFirstImageAndHighestOfferByUser($this->getUser());
        
        return $this->render('userprofile/my_auctions.html.twig', [
            'auctions' => $auctions
        ]);
    }
}
