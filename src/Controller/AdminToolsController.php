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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/at")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminToolsController extends AbstractController
{
    /**
     * @Route("/auctions-deleted", name="auctions-deleted")
     */
    public function index(): Response
    {
        $auctions = $this->getDoctrine()->getRepository(Auction::class)->findBy(['isDeleted' => '1']);

        return $this->render('admin_tools/auctions_deleted.html.twig', [
            'auctions' => $auctions
        ]);
    }


    /**
     * @Route("/comments-deleted", name="comments-deleted")
     */
    public function indexc(): Response
    {
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['isDeleted' => '1']);

        return $this->render('admin_tools/comments_deleted.html.twig', [
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/comments-newest", name="comments-newest")
     */
    public function indexne(): Response
    {
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(
            [],
            ['modifiedAt' => 'DESC'],
            20 
        );

        $commentsCA = $this->getDoctrine()->getRepository(Comment::class)->findBy(
            [],
            ['createdAt' => 'DESC'],
            20 
        );

        return $this->render('admin_tools/comments_newest.html.twig', [
            'comments' => $comments,
            'commentsCA' => $commentsCA
        ]);
    }

}
