<?php

namespace App\Controller;

use App\Entity\Auction;
use App\Entity\TempImage;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class EndPointsController extends AbstractController
{
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
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
    public function getAuctions(Request $request)
    {
        $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAllWithFirstImageAndHighestOffer();
        // dump();

        $_POST_requestedPage = $request->get('requestedPage');
        $itemsPerPage = $_COOKIE['itemsPerPage'];
        $auctions = $this->paginator->paginate($auctions, $_POST_requestedPage, $itemsPerPage);
       /*$auctionImage = $this->getDoctrine()->getRepository(AuctionImage::class)->findOneBy([
            'auction_id' => ''
        ])*/
        //dump($auctions);
        return $this->render('main/tools/auction_list_ajax_part.html.twig', [
            'auctions' => $auctions
        ]);
    }

    /**
     * @Route("/ep/uploadTemporary", name="uploadTemporary", methods={"POST"})
     */
    public function uploadTemp(Request $request)
    {
        $TOKEN = $request->request->get('TOKEN');
        $filename = '';
        if ( 0 < $_FILES['file']['error'] ) 
        {
            echo 'Error: ' . $_FILES['file']['error'] . '<br>';
        }
        else 
        {
            $filename = $this->getSaveFilename($TOKEN,1);
            move_uploaded_file($_FILES['file']['tmp_name'], $this->getParameter('tempImagePath').$filename);
        }
    
        $tempImage = new TempImage();
        $tempImage->setToken($TOKEN);
        $tempImage->setFilename($filename);
        $tempImage->setCreatedAt(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($tempImage);
        $em->flush();


        return new JsonResponse([
            'resulkt' => session_id(),
            'resp' => $TOKEN
        ]);
    }

    private function getSaveFilename(string $token, int $count) :string
    {
        if(file_exists($this->getParameter('tempImagePath')."[$token]$count.jpg"))
            return $this->getSaveFilename($token,$count+1);
        else
            return "[$token]$count.jpg";
    }



    /**
     * @Route("/ep/makeOffer", name="makeOffer", methods={"POST"})
     */
    public function makeOffer(Request $request)
    {

        $result = $request->get('offerValue');
        dump($request);

        return new JsonResponse([
            'RECEIVED VALUE' => $result
        ]);
    }

}
