<?php

namespace App\Controller\AJAX;

use App\Entity\Auction;
use App\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/at/ep")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminToolsAJAXController extends AbstractController
{
    /**
     * @Route("/restoreAuction", name="restoreAuction", methods={"POST"})
     */
    public function toggleVerification(Request $request)
    {
        $json = json_decode($request->getContent());

        $em = $this->getDoctrine()->getManager();
        $auction = $em->getRepository(Auction::class)->findOneBy([
            'id' => $json->auctionId
        ]);

        $auction->setIsDeleted(false);

        $em->persist($auction);
        $em->flush();

        return new JsonResponse([
            'result' => "Success"
        ]);
    }

    /**
     * @Route("/restoreComment", name="restoreComment", methods={"POST"})
     */
    public function toggleVerificationa(Request $request)
    {
        $json = json_decode($request->getContent());

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->findOneBy([
            'id' => $json->commentId
        ]);

        $comment->setIsDeleted(false);

        $em->persist($comment);
        $em->flush();

        return new JsonResponse([
            'result' => "Success"
        ]);
    }

}