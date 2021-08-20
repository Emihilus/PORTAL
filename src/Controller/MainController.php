<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Auction;
use App\Entity\AuctionImage;
use App\Entity\TempImage;
use App\Form\AuctionCreateFormType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
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
        $auction = $this->getDoctrine()->getRepository(Auction::class)->findOneByIdWithOffers($auctionId);


        return $this->render('main/auction_details.html.twig', [
            'auction' => $auction,
        ]);
    }

    /**
     * @Route("/create-auction", name="create-auction", methods={"POST","GET"})
     */
    public function createAuctionForm(Request $request): Response
    {
         $auction = new Auction();

         $form = $this->createForm(AuctionCreateFormType::class, $auction);
         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid())
         {
            $TOKEN = $request->request->get('auction_create_form')['token'];
            $NEW_ORDER = explode(",",$request->request->get('auction_create_form')['image-order']);
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy(['id'=> 1]);
            $auction = $form->getData();
            $auction->setByUser($user);
            $auction->setCreatedAt(null);

            $tempImages = $em->getRepository(TempImage::class)->findByToken($TOKEN);
            for ($i = 0 ; $i < count($NEW_ORDER); $i++)
            {
                $auctionImage = new AuctionImage();
                $auctionImage->setFilename($tempImages[$NEW_ORDER[$i]]->getFilename());
                $auctionImage->setOrderIndicator($i);
                $auctionImage->setAuction($auction);
                $em->persist($auctionImage);
                
                rename($this->getParameter('tempImagePath').$tempImages[$NEW_ORDER[$i]]->getFilename(),$this->getParameter('auctionImagePath').$tempImages[$NEW_ORDER[$i]]->getFilename());

                $this->processToThumbnail($tempImages[$NEW_ORDER[$i]]->getFilename());
            }

            $em->persist($auction);
            $em->flush();

            $this->addFlash(
                'success',
                "Your changes were saved! "
            );

            return $this->redirectToRoute('create-auction');
         }

         return $this->render('main/auction_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function processToThumbnail($filename)
    {
                //Your Image
        $imgSrc = $this->getParameter('auctionImagePath').$filename;

        //getting the image dimensions
        list($width, $height) = getimagesize($imgSrc);

        //saving the image into memory (for manipulation with GD Library)
        $myImage = imagecreatefromjpeg($imgSrc);

        // calculating the part of the image to use for thumbnail
        if ($width > $height) {
        $y = 0;
        $x = ($width - $height) / 2;
        $smallestSide = $height;
        } else {
        $x = 0;
        $y = ($height - $width) / 2;
        $smallestSide = $width;
        }

        // copying the part into thumbnail
        $thumbSize = 100;
        $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
        imagecopyresampled($thumb, $myImage, 0, 0, $x, $y, $thumbSize, $thumbSize, $smallestSide, $smallestSide);

        //final output
        // header('Content-type: image/jpeg');
        imagejpeg($thumb, $this->getParameter('auctionImagePathThumbnail').'th-'.$filename);
            }
}
