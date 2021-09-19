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
            $auction = $em->getRepository(Auction::class)->findOneByIdWithHighestOffer($json->auctionId);
            $offer->setAuction($auction);
            $offer->setByUser($this->getUser());
            $offers = $auction->getOffers();

           /* dump(count($auction[0]->getOffers()));
            foreach ($auction[0]->getOffers() as $offer)
            {
                dump($offer);
            }*/
            
            $validatorErrors = $validator->validate($offer);

            if (count($validatorErrors) == 0 && $offers[0]->getValue()+99 < $json->offerValue) 
            {
                $em->persist($offer);

                $notification = new Notification();
                $notification->setRecipientUser($offer[0]->getByUser());
                $notification->setRelatedEntity(['auction' => $auction->getId()]);
                $notification->setMessage('Twoja oferta została przebita w aukcji '.$auction->getTitle().' przez użytkownika '.$this->getUser()->getUsername());
                $em->persist($notification);

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
