<?php

namespace App\Controller\AJAX;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Offer;
use App\Entity\Auction;
use App\Entity\Comment;
use App\Entity\Notification;
use App\Entity\TempImage;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * @Route("/ep")
 */
class FetchAuctionsController extends AbstractController
{
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @Route("/setPerPage", name="setPerPage", methods={"POST"})
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
     * @Route("/getAuctions", name="getAuctions", methods={"POST"})
     */
    public function getAuctions(Request $request)
    {
        $auctions = '';
        $json = json_decode($request->getContent());


        switch ($json->mMode) 
        {
            case 0:
                $method = "ListAllAuctions";
                break;

            case 1: // of specific user
                $method = "ListAllAuctions";
                break;

            case 2:
                //$method = "SoldAuctionsOfUser";
                $method = "ListAllAuctions";
                break;

            case 3:
                // $method = "CurrentAuctionsOfUser";
                $method = "ListAllAuctions";
                break;

            case 4:
                $method = "LeadingAuctionsOfUser";
                break;

            case 5: /// WON
                $method = "LeadingAuctionsOfUser";
                break;

            case 6:
                $method = "ParticipatingAuctionsOfUser";
                break;

            case 7:
                $method = "ParticipatingNotLeadingAuctionsOfUser";
                break;

            case 8: //PARTICIPATED
                $method = "ParticipatingAuctionsOfUser";
                break;

            case 9: //PARTICIPATED
                $method = "ParticipatingNotLeadingAuctionsOfUser";
                break;

            case -1:
                $method = 'ListAllAuctions';
                break;
        }


        if ($json->mMode < 4) 
        {
            $queryFunction = 'qBuilder' . $method;
            $auctions = $this->getDoctrine()->getRepository(Auction::class)->$queryFunction($this->getUser(), $json->filters);
        } 
        else 
        {
            $queryFunction = 'dql' . $method;
            $auctions = $this->getDoctrine()->getRepository(Auction::class)->$queryFunction($this->getUser(), $json->filters);
        }

        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }

        $allCount = count($auctions);
        $itemsPerPage = $_COOKIE['itemsPerPage'];
        $auctions = $this->paginator->paginate($auctions, $json->requestedPage, $itemsPerPage);

        return $this->render('parts/ajax/auction_list_ajax_part.html.twig', [
            'auctions' => $auctions,
            'pages' => $allCount % $itemsPerPage === 0 ? $allCount / $itemsPerPage : intval($allCount / $itemsPerPage) + 1,
            'itemsPerPage' => $itemsPerPage,
            'requestedPage' => $json->requestedPage

        ]);
    }


    // GET AUCTIONS FOR PLACING CUSTOMER COMMENTS
    /**
     * @Route("/getAllCAuctions", name="getAllCAuctions", methods={"POST"})
     */
    public function getAllCAuctions(Request $request)
    {
        $auctions = '';
        $json = json_decode($request->getContent());

        $auctions = $this->getDoctrine()->getRepository(Auction::class)->qBuilderAllCList($this->getUser());

        dump($auctions);
       
        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }

        $allCount = count($auctions);
        $itemsPerPage = $_COOKIE['itemsPerPage'];
        $auctions = $this->paginator->paginate($auctions, $json->requestedPage, $itemsPerPage);

        return $this->render('parts/ajax/auction_allc_list_ajax_part.html.twig', [
            'auctions' => $auctions,
            'pages' => $allCount % $itemsPerPage === 0 ? $allCount / $itemsPerPage : intval($allCount / $itemsPerPage) + 1,
            'itemsPerPage' => $itemsPerPage
        ]);
    }
    // GET AUCTIONS FOR PLACING CUSTOMER COMMENTS


    // AUTOCOMPLETE GET AUCTIONS
    /**
     * @Route("/getAuctionsAutocomplete", name="getAuctionsAutocomplete", methods={"POST"})
     */
    public function getAuctionsForAutocomplete(Request $request)
    {
        $auctions = '';
        $json = json_decode($request->getContent());


        switch ($json->mMode) 
        {
            case 0:
                $method = "ListAllAuctions";
                break;

            case 1: // of specific user
                $method = "ListAllAuctions";
                break;

            case 2:
                //$method = "SoldAuctionsOfUser";
                $method = "ListAllAuctions";
                break;

            case 3:
                // $method = "CurrentAuctionsOfUser";
                $method = "ListAllAuctions";
                break;

            case 4:
                $method = "LeadingAuctionsOfUser";
                break;

            case 5: /// WON
                $method = "LeadingAuctionsOfUser";
                break;

            case 6:
                $method = "ParticipatingAuctionsOfUser";
                break;

            case 7:
                $method = "ParticipatingNotLeadingAuctionsOfUser";
                break;

            case 8: //PARTICIPATED
                $method = "ParticipatingAuctionsOfUser";
                break;

            case 9: //PARTICIPATED
                $method = "ParticipatingNotLeadingAuctionsOfUser";
                break;

            case -1:
                $method = 'ListAllAuctions';
                break;
        }

        if ($json->mMode < 4) 
        {
            $queryFunction = 'qBuilder' . $method;
            $auctions = $this->getDoctrine()->getRepository(Auction::class)->$queryFunction($this->getUser(), $json->filters);
        } 
        else 
        {
            $queryFunction = 'dql' . $method;
            $auctions = $this->getDoctrine()->getRepository(Auction::class)->$queryFunction($this->getUser(), $json->filters);
        }

        dump($auctions);

        $auctionTitles = [];
        foreach ($auctions as $auction) 
        {
            array_push($auctionTitles, $auction[0]->getTitle());
        }


        return new JsonResponse([
            'auctions' => $auctionTitles
        ]);
    }
    // AUTOCOMPLETE GET AUCTIONS
}