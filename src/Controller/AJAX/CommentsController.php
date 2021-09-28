<?php

namespace App\Controller\AJAX;

use DateTime;
use App\Entity\Auction;
use App\Entity\Comment;
use App\Event\CommentRegularReplyEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/ep")
 */
class CommentsController extends AbstractController
{
    public function __construct(EventDispatcherInterface $dispatcher) 
    {
        $this->dispatcher = $dispatcher;
    }
    
    // POST COMMENT AJAX CALL
    /**
     * @Route("/postComment", name="postComment", methods={"POST"})
     */
    public function postComment(Request $request)
    {
        $json = json_decode($request->getContent());

        if ($this->getUser() != null && isset($json->csrf) && $this->isCsrfTokenValid($this->getUser()->getId(), $json->csrf)) 
        {
            $em = $this->getDoctrine()->getManager();

            $comment = new Comment();
            $comment->setContent($json->content);
            $comment->setByUser($this->getUser());

            $auction = $em->getRepository(Auction::class)->find($json->auctionId);
            $comment->setAuction($auction);

            if(isset($json->replyTo))
            {
                $comment->setReplyTo($em->getReference('App\Entity\Comment', $json->replyTo));
            }

            if(isset($json->inReplyTo))
            {
                $comments = $em->createQueryBuilder()
                ->select('COUNT(c.id)')
                ->from('App\Entity\Comment', 'c')
                ->where('c.auction = :auction')
                ->setParameter('auction', $auction)
                ->andWhere('c.value = 2')
                ->getQuery()
                ->getSingleScalarResult();
                if($comments > 0)
                {
                    return new JsonResponse([
                        'result' => "Reply to this comment already exists"
                    ]);
                }
                $comment->setValue(2);
                $comment->setReplyTo($em->getReference('App\Entity\Comment', $json->inReplyTo));
            }

            if(isset($json->value))
            {
                $comments = $em->createQueryBuilder()
                ->select('COUNT(c.id)')
                ->from('App\Entity\Comment', 'c')
                ->where('c.auction = :auction')
                ->setParameter('auction', $auction)
                ->andWhere('c.value > -2')
                ->andWhere('c.value < 2')
                ->getQuery()
                ->getSingleScalarResult();
                if($comments > 0)
                {
                    return new JsonResponse([
                        'result' => "Buyer comment already exists"
                    ]);
                }
                $comment->setValue($json->value);
            }

            $em->persist($comment);
            $em->flush();
            $genId = $comment->getId();

            if(isset($json->replyTo))
            {
                $this->dispatcher->dispatch(new CommentRegularReplyEvent([
                    'recipientComment' => $em->getReference('App\Entity\Comment', $json->replyTo),
                    'relatedComment' => $comment
                ]), 'auction.comment_regular_reply');
            }

            return new JsonResponse([
                'result' => "Success",
                'genId' => $genId
            ]);
        } 
        else 
        {
            return new JsonResponse([
                'result' => "Forbidden"
            ]);
        }
    }
    // POST COMMENT AJAX CALL


    // DELETE (SET ISDELETE TO TRUE) ONLY FOR ADMIN AJAX CALL
     /**
     * @Route("/deleteComment", name="deleteComment", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteComment(Request $request)
    {
        $json = json_decode($request->getContent());
        $em = $this->getDoctrine()->getManager();

        // single deletion, cascade required - ?
        $result = $em->createQueryBuilder()
        ->update('App\Entity\Comment', 'c')
        ->set('c.isDeleted', true)
        ->where('c.id = :cid')
        ->setParameter('cid',$json->commentId)
        ->getQuery()->execute();

        return new JsonResponse([
            'result' => $result
        ]);
    }
    // DELETE (SET ISDELETE TO TRUE) ONLY FOR ADMIN AJAX CALL


    // EDIT COMMENT ONLY FOR USERS 
    /**
     * @Route("/editComment", name="editComment", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function editComment(Request $request)
    {
        $json = json_decode($request->getContent());
        $em = $this->getDoctrine()->getManager();

        // single deletion, cascade required
        $result = $em->createQueryBuilder()
        ->update('App\Entity\Comment', 'c')
        ->set('c.content', ':newcontent')
        ->setParameter('newcontent', $json->content)
        ->set('c.modifiedAt', ':modified')
        ->setParameter('modified', new DateTime())
        ->where('c.id =:cid')
        ->setParameter('cid',$json->commentId);

        if(!$this->isGranted('ROLE_ADMIN'))
        {
            $result = $result
            ->andWhere('c.byUser = :usr')
            ->setParameter('usr', $this->getUser());
        }

        $result = $result->getQuery()->execute();
        // dump($result);

        return new JsonResponse([
            'result' => $result
        ]);
    }
    // EDIT COMMENT ONLY FOR USERS 


    // LIKE COMMENT AJAX CALL
    /**
     * @Route("/likeComment", name="likeComment", methods={"POST"})
     */
    public function likeComment(Request $request)
    {
        $json = json_decode($request->getContent());

        if ($this->getUser() != null) 
        {
            $em = $this->getDoctrine()->getManager();

            $comment = $em->getRepository(Comment::class)->find($json->commentId);
            if($json->likeState)
                $comment->removeLikedBy($this->getUser());
            else
            {
                $comment->addLikedBy($this->getUser());
                $comment->removeDislikedBy($this->getUser());
            }

            $em->persist($comment);
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
    // LIKE COMMENT AJAX CALL


    // DISLIKE COMMENT AJAX CALL
    /**
     * @Route("/dislikeComment", name="dislikeComment", methods={"POST"})
     */
    public function dislikeComment(Request $request)
    {
        $json = json_decode($request->getContent());

        if ($this->getUser() != null) 
        {
            $em = $this->getDoctrine()->getManager();

            $comment = $em->getRepository(Comment::class)->find($json->commentId);
            if($json->dislikeState)
                $comment->removeDislikedBy($this->getUser());
            else
            {
                $comment->removeLikedBy($this->getUser());
                $comment->addDislikedBy($this->getUser());
            }

            $em->persist($comment);
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
    // DISLIKE COMMENT AJAX CALL
}