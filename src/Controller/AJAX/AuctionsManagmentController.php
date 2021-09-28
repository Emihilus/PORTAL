<?php

namespace App\Controller\AJAX;

use App\Entity\Auction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/ep")
 */
class AuctionsManagmentController extends AbstractController
{
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
                'result' => "This action is permitted for logged in users only"
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
                'result' => "This action is permitted for logged in users only"
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
}