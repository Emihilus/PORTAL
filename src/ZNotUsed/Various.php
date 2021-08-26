<?php


/* from ajax bottom

PART DESIGN ERRORS without db hghest check
public function makeOffer(Request $request, ValidatorInterface $validator)
    {
        $em = $this->getDoctrine()->getManager();
        //$result = $request->getContent();
        //dump($request);
        //dump($request->getContent());
        $json = json_decode($request->getContent());
        //dump($json);

       /* if($json->offerValue > 999999999)
        {

            return new JsonResponse([
                'RECEIVED VALUE' => $json->offerValue,
                'errorsBody' => "Oferta nie może być wieksza niż 10 mln PLN"
            ]);
        }


        $offer = new Offer();
        $offer->setValue($json->offerValue);
        $offer->setCreatedAt(null);
        $offer->setAuction($em->getRepository(Auction::class)->find($json->auctionId));
        $offer->setByUser($em->getRepository(User::class)->find(1));

        
        $validatorErrors = $validator->validate($offer);
        dump($validatorErrors);
        dump(count($validatorErrors));

        if(count($validatorErrors) == 0)
        {
            $em->persist($offer);
            $em->flush();
            return new JsonResponse([
                'RECEIVED VALUE' => $json->offerValue
            ]);
        }
        else
        {
            $rendered = $this->render('main/ajax_parts/auction_make_offer_errors_part.html.twig', [
                'errors' => $validatorErrors
            ]);
            dump($rendered);
            return new JsonResponse([
                'RECEIVED VALUE' => $json->offerValue,
                'errorsBody' => $rendered->getContent()
            ]);
        }
    }



    manual hghhst offer validation
    else
        {
            $payloadArray = ['errors' => $validatorErrors];
            if ($auctionWithHghstOffer[1] > $json->offerValue)
            {
               $payloadArray['offerError']  = "The value of your offer ($json->offerValue) is smaller than the highest offer for this auction ($auctionWithHghstOffer[1])";
            }
            else
            {
                $payloadArray['offerError'] = "";
            }
            $rendered = $this->render('main/ajax_parts/auction_make_offer_errors_part.html.twig', $payloadArray);
            
            return new JsonResponse([
                'RECEIVED VALUE' => $json->offerValue,
                'errorsBody' => $rendered->getContent()
            ]);
        }
*/