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
    public function __construct($path, PaginatorInterface $paginator)
    {
        $this->rootPath = $path;
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
        $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAll();

        $_POST_requestedPage = $request->get('requestedPage');
        $itemsPerPage = $_COOKIE['itemsPerPage'];
        $auctions = $this->paginator->paginate($auctions, $_POST_requestedPage, $itemsPerPage);

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
            $filename = "[$TOKEN]" . $_FILES['file']['name'];
            move_uploaded_file($_FILES['file']['tmp_name'], $this->rootPath."/tempImg/$filename");
        }
    
        $tempImage = new TempImage();
        $tempImage->setToken($TOKEN);
        $tempImage->setFilename($filename);

        $em = $this->getDoctrine()->getManager();
        $em->persist($tempImage);
        $em->flush();


        return new JsonResponse([
            'resulkt' => session_id(),
            'resp' => $TOKEN
        ]);
    }

}
