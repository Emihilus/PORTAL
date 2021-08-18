<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Auction;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

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
            setcookie('itemsPerPage', 10, time() + (86400 * 30), "/");
        }
        $itemsPerPage = $_COOKIE['itemsPerPage'];

        $auctions = $this->paginator->paginate($auctions, $page, $itemsPerPage);

        return $this->render('main/auction_list_ajax.html.twig', [
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
     * @Route("/ep/setPerPage", name="setPerPage", methods={"POST"})
     */
    public function setPerPageCookie(Request $request)
    {

        $result = $request->get('itemsPerPage');
        setcookie('itemsPerPage', $result, time() + (86400 * 30), "/");

        return new JsonResponse([
            'itemsPerPage' => $result 
        ]);
    }

    /**
     * @Route("/ep/getAuctions", name="getAuctions", methods={"POST"})
     */
    public function getAuctions(Request $request, SerializerInterface $serializer)
    {

        $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAll();


        $_POST_requestedPage = $request->get('requestedPage');

        $itemsPerPage = $_COOKIE['itemsPerPage'];

        $auctions = $this->paginator->paginate($auctions, $_POST_requestedPage, $itemsPerPage);

        $allCount = count($auctions);

        /*$arrayCollection = [];
        foreach($auctions as $auction) {
            $arrayCollection[] = array(
                'id' => $auction->getId(),
                'title' => $auction->getTitle(),
                'endsAt' => $auction->getEndsAt(),
                // ... Same for each property you want
            );
       }*/

        return $this->render('main/auction_list_ajax_part.html.twig', [
            'auctions' => $auctions
        ]);
        /*return new JsonResponse([
            'auctions' => $arrayCollection,
            'rqp' => $_POST_requestedPage,
            'ac' => $allCount
        ]); */
    }
}
