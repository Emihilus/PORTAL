<?php

namespace App\Controller;

use App\Entity\Auction;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAll();

        return $this->render('main/show_auctions.html.twig', [
            'auctions' => $auctions,
        ]);
    }

    /**
     * @Route("/auction-details/{auctionId}", name="auction-details")
     */
    public function auctionDetails($auctionId): Response
    {
        $auction = $this->getDoctrine()->getRepository(Auction::class)->findOneBy(['id' => $auctionId]);

        return $this->render('main/auction_details.html.twig', [
            'auction' => $auction,
        ]);
    }
}
