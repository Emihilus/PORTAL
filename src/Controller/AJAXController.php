<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Offer;
use App\Entity\Auction;
use App\Entity\TempImage;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AJAXController extends AbstractController
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
        $auctions = '';
        $json = json_decode($request->getContent());

        switch ($json->type)
        {
            // PUBLIC LIST
            case 0:
                $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAllWithFirstImageAndHighestOfferWithOwner($this->getUser());
                break;

            // MY AUCTIONS LIST
            case 1: 
                $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAllWithFirstImageAndHighestOfferWithOwner2($this->getUser(),$this->getUser());
                break;

            // AUCTIONS OF SPECIFIC USER LIST
            case 2: 
                $auctions = $this->getDoctrine()->getRepository(Auction::class)->findAllWithFirstImageAndHighestOfferWithOwner2($this->getDoctrine()->getRepository(User::class)->findOneBy(['username'=> $json->username]),$this->getUser());
                break;
        }
        
        $itemsPerPage = $_COOKIE['itemsPerPage'];
        $auctions = $this->paginator->paginate($auctions, $json->requestedPage, $itemsPerPage);
        return $this->render('parts/ajax/auction_list_ajax_part.html.twig', [
            'auctions' => $auctions
        ]);
    }
    // WE NEED TO AVOID REDUNDANCY SO PUT IN HERE CONDTION FOR SELECTING AUCTIONS CREATED ONLY BY SPECIFIED USER, USEFUL FOR FUTURE OPTION SHOW SPECIFIC USER AUCTIONS

    /**
     * @Route("/ep/uploadTemporary", name="uploadTemporary", methods={"POST"})
     */
    public function uploadTemp(Request $request)
    {
        $TOKEN = $request->request->get('TOKEN');
        $filename = '';
        if ( 0 < $_FILES['file']['error'] ) 
        {
            return new JsonResponse([
                'errors' => 'Error: ' . $_FILES['file']['error']
            ]);
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
            'errors' => ''
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
    public function makeOffer(Request $request, ValidatorInterface $validator)
    {
        if($this->getUser() != null)
        {
                $em = $this->getDoctrine()->getManager();
                $json = json_decode($request->getContent());


                $offer = new Offer();
                $offer->setValue($json->offerValue);
                $offer->setCreatedAt(null);
                $auctionWithHghstOffer = $em->getRepository(Auction::class)->findOneByIdWithHighestOffer($json->auctionId);
                $offer->setAuction($auctionWithHghstOffer[0]);
                $offer->setByUser($em->getRepository(User::class)->find(1));

                
                $validatorErrors = $validator->validate($offer);

                if(count($validatorErrors) == 0 && $auctionWithHghstOffer[1] < $json->offerValue)
                {
                    $em->persist($offer);
                    $em->flush();
                    return new JsonResponse([]);
                }
                else
                {
                    if ($auctionWithHghstOffer[1] > $json->offerValue)
                    {
                        $validatorErrors->add(new ConstraintViolation('The value of your offer ('.($json->offerValue/100).' PLN) is smaller than the highest offer for this auction ('.($auctionWithHghstOffer[1]/100).' PLN)', null, ['param'=>'param'],$json->offerValue, null, 45, null, null, new LessThan($auctionWithHghstOffer[1]),'null'));
                    }
                    $rendered = $this->render('ajax_parts/auction_make_offer_errors_part.html.twig', [
                        'errors' => $validatorErrors
                    ]);

                    dump($validatorErrors);
                    return new JsonResponse([
                        'RECEIVED VALUE' => $json->offerValue,
                        'errorsBody' => $rendered->getContent()
                    ]);
                }


        }
        else
        {
            return new JsonResponse([
                'errorsBody' => "This action is for logged in users only"
            ]);
        }
    }


    /**
     * @Route("/ep/deleteAuction", name="deleteAuction", methods={"POST"})
     */
    public function deleteAuction(Request $request)
    {

        if($this->getUser() != null)
        {
            $json = json_decode($request->getContent());
            $em = $this->getDoctrine()->getManager();
            $auction = $em->getRepository(Auction::class)->find($json->auctionId);


            if($auction->getByUser() == $this->getUser())
            {
                ;// DELETE AUCTION
                return new JsonResponse([
                    'result' => "Success"
                ]);
            }
            else
            {
                ;// WRONG USER
                return new JsonResponse([
                    'result' => "Wrong user"
                ]);
            }
            
            
        }
        else
        {
            return new JsonResponse([
                'result' => "This action is permitted for logged in users only you dumbass hacker"
            ]);
        }
    }


     /**
     * @Route("/ep/toggleFavoriteAuction", name="toggleFavoriteAuction", methods={"POST"})
     */
    public function toggleFavoriteAuction(Request $request)
    {
        if($this->getUser() != null)
        {
            $json = json_decode($request->getContent());
            $em = $this->getDoctrine()->getManager();
            $auction = $em->getRepository(Auction::class)->find($json->auctionId);

            $user = $this->getUser();

            $json->add ? $user->addLikedAuction($auction) : $user->removeLikedAuction($auction);

            $em->persist($user);
            $em->flush();
            return new JsonResponse([
                'result' => "Success"
            ]);
            
        }
        else
        {
            return new JsonResponse([
                'result' => "This action is permitted for logged in users"
            ]);
        }
    }

    /**
     * @Route("/ep/toggleVerification", name="toggleVerification", methods={"POST"})
     */
    public function toggleVerification(Request $request)
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');

        // dump($this->isGranted('ROLE_ADMIN'));

        if($this->isGranted('ROLE_ADMIN'))
        {
            $json = json_decode($request->getContent());

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy([
                'username' => $json->username
            ]);

            $json->action ? $user->setIsVerified(false) : $user->setIsVerified(true);

            $em->persist($user);
            $em->flush();

            return new JsonResponse([
                'result' => "Success"
            ]);
            
        }
        else
        {
            return new JsonResponse([
                'result' => "Forbidden"
            ]);
        }
    }

    /**
     * @Route("/ep/toggleBan", name="toggleBan", methods={"POST"})
     */
    public function toggleBan(Request $request)
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');

        // dump($this->isGranted('ROLE_ADMIN'));

        if($this->isGranted('ROLE_ADMIN'))
        {
            $json = json_decode($request->getContent());

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy([
                'username' => $json->username
            ]);

            $json->action ? $user->setIsBanned(true) : $user->setIsBanned(false);

            $em->persist($user);
            $em->flush();

            return new JsonResponse([
                'result' => "Success"
            ]);
            
        }
        else
        {
            return new JsonResponse([
                'result' => "Forbidden"
            ]);
        }
    }

    /**
     * @Route("/ep/postComment", name="postComment", methods={"POST"})
     */
    public function postComment(Request $request)
    {
        $json = json_decode($request->getContent());

        if($this->getUser() != null)
        {
            $em = $this->getDoctrine()->getManager();

            $comment = 


            $json->action ? $user->setIsBanned(true) : $user->setIsBanned(false);

            $em->persist($user);
            $em->flush();



            return new JsonResponse([
                'result' => "Success"
            ]);
        }
        else
        {
            return new JsonResponse([
                'result' => "Forbidden"
            ]);
        }

        return new JsonResponse([
            'itemsPerPage' => $result 
        ]);
    }
}