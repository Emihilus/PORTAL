<?php

namespace App\Controller;

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
class AJAXController extends AbstractController
{
    


    // UPLOAD TEMPORARY IMAGES
    /**
     * @Route("/uploadTemporary", name="uploadTemporary", methods={"POST"})
     */
    public function uploadTemp(Request $request)
    {
        $TOKEN = $request->request->get('TOKEN');
        $filename = '';
        if (0 < $_FILES['file']['error']) 
        {
            return new JsonResponse([
                'errors' => 'Error: ' . $_FILES['file']['error']
            ]);
        } else {
            $filename = $this->getSaveFilename($TOKEN, 1);
            move_uploaded_file($_FILES['file']['tmp_name'], $this->getParameter('tempImagePath') . $filename);
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

    private function getSaveFilename(string $token, int $count): string
    {
        if (file_exists($this->getParameter('tempImagePath') . "[$token]$count.jpg"))
            return $this->getSaveFilename($token, $count + 1);
        else
            return "[$token]$count.jpg";
    }
    // UPLOAD TEMPORARY IMAGES


    // OFFER AJAX PLACEMENT
    /**
     * @Route("/makeOffer", name="makeOffer", methods={"POST"})
     */
    public function makeOffer(Request $request, ValidatorInterface $validator)
    {
        $json = json_decode($request->getContent());

        if ($this->getUser() != null && isset($json->csrf) && $this->isCsrfTokenValid($this->getUser()->getId(), $json->csrf)) 
        {
            $em = $this->getDoctrine()->getManager();

            $offer = new Offer();
            $offer->setValue($json->offerValue);
            $offer->setCreatedAt(null);
            $auctionWithHghstOffer = $em->getRepository(Auction::class)->findOneByIdWithHighestOffer($json->auctionId);
            $offer->setAuction($auctionWithHghstOffer[0]);
            $offer->setByUser($this->getUser());

            $validatorErrors = $validator->validate($offer);

            if (count($validatorErrors) == 0 && $auctionWithHghstOffer[1]+99 < $json->offerValue) 
            {
                $em->persist($offer);
                $em->flush();
                return new JsonResponse([
                    'recv' => $json->offerValue]);
            } 
            else 
            {
                if ($auctionWithHghstOffer[1]+99 > $json->offerValue) 
                {
                    $validatorErrors->add(new ConstraintViolation('The value of your offer (' . ($json->offerValue / 100) . ' PLN) must be at least 1 PLN bigger than than the highest offer for this auction (' . (($auctionWithHghstOffer[1]+100) / 100) . ' PLN)', null, ['param' => 'param'], $json->offerValue, null, 45, null, null, new LessThan($auctionWithHghstOffer[1]), 'null'));
                }
                $rendered = $this->render('parts/ajax/auction_make_offer_errors_part.html.twig', [
                    'errors' => $validatorErrors
                ]);

                dump($validatorErrors);
                return new JsonResponse([
                    'recv' => $json->offerValue,
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
    // OFFER AJAX PLACEMENT


    // DELETE (SET ISDELETED TO TRUE) AUCTION 
    /**
     * @Route("/deleteAuction", name="deleteAuction", methods={"POST"})
     */
    public function deleteAuction(Request $request)
    {

        if ($this->getUser() != null) 
        {
            $json = json_decode($request->getContent());
            $em = $this->getDoctrine()->getManager();
            $auction = $em->getRepository(Auction::class)->find($json->auctionId);


            if ($auction->getByUser() == $this->getUser() || $this->isGranted('ROLE_ADMIN')) 
            {

                if($auction->getEndsAt() < new \DateTime())
                {
                    return new JsonResponse([
                        'result' => "You can't delete already ended auction"
                    ]);
                }

                $auction->setIsDeleted(true);
                $em->persist($auction);
                $em->flush();

                return new JsonResponse([
                    'result' => "Success"
                ]);
            } 
            else 
            {
                ; // WRONG USER
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
    // DELETE (SET ISDELETED TO TRUE) AUCTION 


    // TRIGGER END NOW AUCION
    /**
     * @Route("/endNowAuction", name="endNowAuction", methods={"POST"})
     */
    public function endNowAuction(Request $request)
    {

        if ($this->getUser() != null) 
        {
            $json = json_decode($request->getContent());
            $em = $this->getDoctrine()->getManager();
            $auction = $em->getRepository(Auction::class)->find($json->auctionId);


            if ($auction->getByUser() == $this->getUser() || $this->isGranted('ROLE_ADMIN')) 
            {
                if($auction->getEndsAt() < new \DateTime())
                {
                    return new JsonResponse([
                        'result' => "Cannot end, auction already ended"
                    ]);
                }

                $auction->setEndsAtManually(new \DateTime());
                $em->persist($auction);
                $em->flush();

                return new JsonResponse([
                    'result' => "Success"
                ]);
            } 
            else 
            {
                ; // WRONG USER
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
    // TRIGGER END NOW AUCION


    // TOGGLE FAVORITE STATE
    /**
     * @Route("/toggleFavoriteAuction", name="toggleFavoriteAuction", methods={"POST"})
     */
    public function toggleFavoriteAuction(Request $request)
    {
        if ($this->getUser() != null) 
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
    // TOGGLE FAVORITE STATE

    // ADMIN TOGGLE USER EMAIL VERIFICATION STATE
    /**
     * @Route("/toggleVerification", name="toggleVerification", methods={"POST"})
     */
    public function toggleVerification(Request $request)
    {
        if ($this->isGranted('ROLE_ADMIN')) 
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
    // ADMIN TOGGLE USER EMAIL VERIFICATION STATE


    // ADMIN TOGGLE BAN
    /**
     * @Route("/toggleBan", name="toggleBan", methods={"POST"})
     */
    public function toggleBan(Request $request)
    {
        if ($this->isGranted('ROLE_ADMIN')) 
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
    // ADMIN TOGGLE BAN


    



    
}
