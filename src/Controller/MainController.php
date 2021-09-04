<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Offer;
use App\Entity\Auction;
use App\Entity\Comment;
use App\Entity\TempImage;
use App\Form\CommentType;
use App\Entity\AuctionImage;
use App\Form\AuctionCreateFormType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
    public function index(): Response
    {
        //$auctions = $this->getDoctrine()->getRepository(Auction::class)->findAll();
        //$allCount = count($auctions);

        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }
        //$itemsPerPage = $_COOKIE['itemsPerPage'];

        //$auctions = $this->paginator->paginate($auctions, $page, $itemsPerPage);

        return $this->render('auction/auction_list.html.twig',/*['page' => $page ], [
            'auctions' => $auctions,
            'pages' => $allCount % $itemsPerPage === 0 ? $allCount / $itemsPerPage : intval($allCount / $itemsPerPage) + 1,
            'itemsPerPage' => $itemsPerPage
        ]*/);
    }

    /**
     * @Route("/auction-details/{auctionId}", name="auction-details")
     */
    public function auctionDetails($auctionId, ValidatorInterface $validator): Response
    {
        $auction = $this->getDoctrine()->getRepository(Auction::class)->findOneByIdWithAuctionImagesAndOffersAndComments($auctionId);

        dump($auction);
        $constraintValue = $validator->getMetadataFor(Offer::class)->properties['Value']->constraints[0]->value;

        /*dump($meta);
        dump($meta->getPropertyMetadata("Value")[0]->getConstraints()[0]->value);*/

        return $this->render('auction/auction_details.html.twig', [
            'auction' => $auction,
            'validation_maxValue' => $constraintValue
        ]);
    }

    /**
     * @Route("/place-acomment/{auctionId}", name="place-acomment")
     */
    public function commentAuction($auctionId, ValidatorInterface $validator, Request $request): Response
    {
        $auction = $this->getDoctrine()->getRepository(Auction::class)->findOneByIdWithAuctionImagesAndOffersAndComments($auctionId);

        dump($auction);
        $constraintValue = $validator->getMetadataFor(Offer::class)->properties['Value']->constraints[0]->value;

        $task = new Comment();

        $form = $this->createForm(CommentType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();

            return $this->redirectToRoute('task_success');
        }

        return $this->render('userprofile/comment_auction.html.twig', [
            'form' => $form->createView(),
            'auction' => $auction,
            'validation_maxValue' => $constraintValue
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
            $em = $this->getDoctrine()->getManager();
            $TOKEN = $request->request->get('auction_create_form')['token'];
            $tempImages = $em->getRepository(TempImage::class)->findByToken($TOKEN);

            if ($tempImages != null) 
            {

                $NEW_ORDER = explode(",", $request->request->get('auction_create_form')['image-order']);
                // $user = $em->getRepository(User::class)->findOneBy(['id' => 1]);
                $auction = $form->getData();
                $auction->setByUser($this->getUser());
                $auction->setCreatedAt(null);


                for ($i = 0; $i < count($NEW_ORDER); $i++) 
                {
                    $auctionImage = new AuctionImage();
                    $auctionImage->setFilename($tempImages[$NEW_ORDER[$i]]->getFilename());
                    $auctionImage->setOrderIndicator($i);
                    $auctionImage->setAuction($auction);
                    $em->persist($auctionImage);

                    rename($this->getParameter('tempImagePath') . $tempImages[$NEW_ORDER[$i]]->getFilename(), $this->getParameter('auctionImagePath') . $tempImages[$NEW_ORDER[$i]]->getFilename());

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
            else
            {
                $this->addFlash(
                    'danger',
                    "At least one image must be uploaded"
                );
            }
        }

        return $this->render('auction/auction_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function processToThumbnail($filename)
    {
        //Your Image
        $imgSrc = $this->getParameter('auctionImagePath') . $filename;

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
        imagejpeg($thumb, $this->getParameter('auctionImagePathThumbnail') . 'th-' . $filename);
    }
}
