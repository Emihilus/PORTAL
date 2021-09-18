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
class OfferController extends AbstractController
{
    
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

            // dump(count($auctionWithHghstOffer[0]->getOffers()));
            $highestValue = 0 ;
            $highestId = 0;
            foreach ($auctionWithHghstOffer[0]->getOffers() as $offer)
            {
                dump($offer);
            }
            
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
    
}
