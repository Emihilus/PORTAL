<?php

namespace App\Controller;

use App\Entity\Auction;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @Route("/auction-list/{page}", name="auction-list", defaults ={"page": "1" })
     */
    public function index($page): Response
    {
        $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAll();
        $allCount = count($auctions);

        if(!isset($_COOKIE['itemsPerPage']))
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }
        $itemsPerPage = $_COOKIE['itemsPerPage'];

        $auctions = $this->paginator->paginate($auctions, $page, $itemsPerPage);

        return $this->render('main/auction_list.html.twig', [
            'auctions' => $auctions,
            'pages' => $allCount % $itemsPerPage === 0 ? $allCount / $itemsPerPage : intval($allCount / $itemsPerPage) + 1,
            'itemsPerPage' => $itemsPerPage
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

    /**
     * @Route("/create-auction", name="create-auction")
     */
    public function createAuctionForm(): Response
    {
         // creates a task object and initializes some data for this example
         $auction = new Auction();
 
         $form = $this->createFormBuilder($auction)
             ->add('task', TextType::class)
             ->add('dueDate', DateType::class)
             ->add('save', SubmitType::class, ['label' => 'Create Task'])
             ->getForm();

        return $this->render('main/auction_create.html.twig', [
            'auction' => 'asda',
        ]);
    }
}
