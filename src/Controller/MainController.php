<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Auction;
use App\Entity\Comment;
use App\Entity\TempImage;
use App\Form\CommentType;
use App\Entity\AuctionImage;
use App\Form\AuctionCreateFormType;
use App\ControllerTools\ImagesProcessor;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    public function __construct(PaginatorInterface $paginator, ImagesProcessor $imagesProcessor)
    {
        $this->paginator = $paginator;
        $this->imagesProcessor = $imagesProcessor;
    }

    /**
     * @Route("/auction-list/{page}", name="auction-list", defaults ={"page": "1" })
     */
    public function index(): Response
    {
        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }

        return $this->render('auction/auction_list.html.twig');
    }

    /**
     * @Route("/auction-details/{auctionId}", name="auction-details")
     */
    public function auctionDetails($auctionId, ValidatorInterface $validator): Response
    {
        $auction = $this->getDoctrine()->getRepository(Auction::class)->findOneByIdWithAuctionImagesAndOffersAndComments($auctionId,$this->getUser());

        // dump($auction);
        $constraintValue = $validator->getMetadataFor(Offer::class)->properties['Value']->constraints[0]->value;

        return $this->render('auction/auction_details.html.twig', [
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
        $this->getParameter('kernel.environment') == 'heroku' ? $heroku = true : $heroku = false;
        $form = $this->createForm(AuctionCreateFormType::class, $auction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            if($form['startingPrice']->getData() >= 100)
            {
                $em = $this->getDoctrine()->getManager();
                $TOKEN = $request->request->get('auction_create_form')['token'];
                $tempImages = $em->getRepository(TempImage::class)->findByToken($TOKEN);

                if ($tempImages != null) 
                {

                    $NEW_ORDER = explode(",", $request->request->get('auction_create_form')['image-order']);
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

                        $this->imagesProcessor->processToThumbnail(
                            $this->getParameter('auctionImagePath') . $tempImages[$NEW_ORDER[$i]]->getFilename(),
                            $this->getParameter('auctionImagePathThumbnail') . 'th-' . $tempImages[$NEW_ORDER[$i]]->getFilename());
                    }

                    $offer = new Offer();
                    //$offer->setByUser($this->getUser());
                    $offer->setValue($form['startingPrice']->getData());
                    $auction->addOffer($offer);

                    $em->persist($offer);
                    $em->persist($auction);
                    $em->flush();

                    $this->addFlash(
                        'success',
                        "Your changes were saved! "
                    );

                    return $this->redirectToRoute('auction-details', ['auctionId' => $auction->getId()]);
                }
                else
                {
                    $this->addFlash(
                        'danger',
                        "At least one image must be uploaded"
                    );
                }
            }
            else
            {
                $this->addFlash(
                    'danger',
                    "Minimalna cena wywoławcza to 1 PLN"
                ); 
            }

        }

        return $this->render('auction/auction_create.html.twig', [
            'form' => $form->createView(),
            'heroku' => $heroku
        ]);
    }

     /**
     * @Route("/place-acomment/{auctionId}", name="place-acomment", defaults ={"auctionId": "-1" })
     */
    public function commentAuction($auctionId, ValidatorInterface $validator, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $auction = $em->getRepository(Auction::class)->findOneByIdWithAuctionImagesAndOffersAndCommentsRESTRICT($auctionId,$this->getUser());

        dump($auction);
        if(!isset($auction) || ($auction->getOffers()[0]->getByUser() != $this->getUser()))
        {
            return new Response('You are not allowed to comment this auction.');
        }

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
            
            foreach($auction->getComments() as $auctionComment)
            {
                if($auctionComment->getValue() != -2)
                {
                    $this->addFlash(
                        'danger',
                        "Ta sprzedaż została juz skomentowana."
                    );
                    return $this->redirectToRoute('profile-details',['username' => $auction->getByUser()]);
                }
            }


            $comment = $form->getData();
            $comment->setAuction($auction);
            $comment->setByUser($this->getUser());

            $em->persist($comment);
            $em->flush();

            $this->addFlash(
                'success',
                "Your changes were saved! "
            );
            return $this->redirectToRoute('profile-details',['username' => $auction->getByUser()]);
        }

        return $this->render('userprofile/comment_auction.html.twig', [
            'form' => $form->createView(),
            'auction' => $auction
        ]);
    }

}
