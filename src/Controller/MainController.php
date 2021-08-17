<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Auction;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MainController extends AbstractController
{
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @Route("/auction-list", name="home")
     */
    public function index(): Response
    {
        $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAll();

        return $this->render('main/sauction_list.html.twig', [
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
