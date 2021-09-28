<?php

namespace App\Controller\AJAX;

use App\Entity\Offer;
use App\Entity\Auction;
use App\Entity\Notification;
use App\Event\OfferPassEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/ep")
 */
class OfferController extends AbstractController
{

    public function __construct(EventDispatcherInterface $dispatcher) 
    {
        $this->dispatcher = $dispatcher;
    }
    
    // OFFER AJAX SUBMISSION
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
            $auction = $em->getRepository(Auction::class)->findOneByIdWithHighestOffer($json->auctionId);
            $offer->setAuction($auction);
            $offer->setByUser($this->getUser());
            $offers = $auction->getOffers();
            
            $validatorErrors = $validator->validate($offer);

            if (count($validatorErrors) == 0 && $offers[0]->getValue()+99 < $json->offerValue) 
            {
                $em->persist($offer);

                if(($offers[0]->getByUser() != null ) && ($this->getUser() != $offers[0]->getByUser()))
                {
                    $event = new OfferPassEvent([
                        'userPassed' => $offers[0]->getByUser(),
                        'relatedAuctionId' => $auction->getId(),
                        'relatedAuctionTitle' => $auction->getTitle(),
                        'passedByUser' => $this->getUser()->getUsername()
                    ]);
                    $this->dispatcher->dispatch($event, 'auction.offer_pass');
                    
                }

                $em->flush();

                return new JsonResponse([
                    'recv' => $json->offerValue]);
            } 
            else 
            {
                if ($offers[0]->getValue()+99 > $json->offerValue) 
                {
                    $validatorErrors->add(new ConstraintViolation('The value of your offer (' . ($json->offerValue / 100) . ' PLN) must be at least 1 PLN bigger than than the highest offer for this auction (' . (($offers[0]->getValue()+100) / 100) . ' PLN)', null, ['param' => 'param'], $json->offerValue, null, 45, null, null, new LessThan($offers[0]->getValue()), 'null'));
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
