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
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @Route("/setPerPage", name="setPerPage", methods={"POST"})
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
     * @Route("/getAuctions", name="getAuctions", methods={"POST"})
     */
    public function getAuctions(Request $request)
    {
        $auctions = '';
        $json = json_decode($request->getContent());


        switch ($json->mMode) 
        {
            case 0:
                $method = "ListAllAuctions";
                break;

            case 1: // of specific user
                $method = "ListAllAuctions";
                break;

            case 2:
                //$method = "SoldAuctionsOfUser";
                $method = "ListAllAuctions";
                break;

            case 3:
                // $method = "CurrentAuctionsOfUser";
                $method = "ListAllAuctions";
                break;

            case 4:
                $method = "LeadingAuctionsOfUser";
                break;

            case 5: /// WON
                $method = "LeadingAuctionsOfUser";
                break;

            case 6:
                $method = "ParticipatingAuctionsOfUser";
                break;

            case 7:
                $method = "ParticipatingNotLeadingAuctionsOfUser";
                break;

            case 8: //PARTICIPATED
                $method = "ParticipatingAuctionsOfUser";
                break;

            case 9: //PARTICIPATED
                $method = "ParticipatingNotLeadingAuctionsOfUser";
                break;

            case -1:
                $method = 'ListAllAuctions';
                break;
        }


        if ($json->mMode < 4) 
        {
            $queryFunction = 'qBuilder' . $method;
            $auctions = $this->getDoctrine()->getRepository(Auction::class)->$queryFunction($this->getUser(), $json->filters);
        } 
        else 
        {
            $queryFunction = 'dql' . $method;
            $auctions = $this->getDoctrine()->getRepository(Auction::class)->$queryFunction($this->getUser(), $json->filters);
        }

        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }

        $allCount = count($auctions);
        $itemsPerPage = $_COOKIE['itemsPerPage'];
        $auctions = $this->paginator->paginate($auctions, $json->requestedPage, $itemsPerPage);

        return $this->render('parts/ajax/auction_list_ajax_part.html.twig', [
            'auctions' => $auctions,
            'pages' => $allCount % $itemsPerPage === 0 ? $allCount / $itemsPerPage : intval($allCount / $itemsPerPage) + 1,
            'itemsPerPage' => $itemsPerPage,
            'requestedPage' => $json->requestedPage

        ]);
    }


    // GET AUCTIONS FOR PLACING CUSTOMER COMMENTS
    /**
     * @Route("/getAllCAuctions", name="getAllCAuctions", methods={"POST"})
     */
    public function getAllCAuctions(Request $request)
    {
        $auctions = '';
        $json = json_decode($request->getContent());

        $auctions = $this->getDoctrine()->getRepository(Auction::class)->qBuilderAllCList($this->getUser());

        dump($auctions);
       
        if (!isset($_COOKIE['itemsPerPage'])) 
        {
            setcookie('itemsPerPage', 20, time() + (86400 * 30), "/");
            $_COOKIE['itemsPerPage'] = 20;
        }

        $allCount = count($auctions);
        $itemsPerPage = $_COOKIE['itemsPerPage'];
        $auctions = $this->paginator->paginate($auctions, $json->requestedPage, $itemsPerPage);

        return $this->render('parts/ajax/auction_allc_list_ajax_part.html.twig', [
            'auctions' => $auctions,
            'pages' => $allCount % $itemsPerPage === 0 ? $allCount / $itemsPerPage : intval($allCount / $itemsPerPage) + 1,
            'itemsPerPage' => $itemsPerPage
        ]);
    }
    // GET AUCTIONS FOR PLACING CUSTOMER COMMENTS


    // AUTOCOMPLETE GET AUCTIONS
    /**
     * @Route("/getAuctionsAutocomplete", name="getAuctionsAutocomplete", methods={"POST"})
     */
    public function getAuctionsForAutocomplete(Request $request)
    {
        $auctions = '';
        $json = json_decode($request->getContent());


        switch ($json->mMode) 
        {
            case 0:
                $method = "ListAllAuctions";
                break;

            case 1: // of specific user
                $method = "ListAllAuctions";
                break;

            case 2:
                //$method = "SoldAuctionsOfUser";
                $method = "ListAllAuctions";
                break;

            case 3:
                // $method = "CurrentAuctionsOfUser";
                $method = "ListAllAuctions";
                break;

            case 4:
                $method = "LeadingAuctionsOfUser";
                break;

            case 5: /// WON
                $method = "LeadingAuctionsOfUser";
                break;

            case 6:
                $method = "ParticipatingAuctionsOfUser";
                break;

            case 7:
                $method = "ParticipatingNotLeadingAuctionsOfUser";
                break;

            case 8: //PARTICIPATED
                $method = "ParticipatingAuctionsOfUser";
                break;

            case 9: //PARTICIPATED
                $method = "ParticipatingNotLeadingAuctionsOfUser";
                break;

            case -1:
                $method = 'ListAllAuctions';
                break;
        }

        if ($json->mMode < 4) 
        {
            $queryFunction = 'qBuilder' . $method;
            $auctions = $this->getDoctrine()->getRepository(Auction::class)->$queryFunction($this->getUser(), $json->filters);
        } 
        else 
        {
            $queryFunction = 'dql' . $method;
            $auctions = $this->getDoctrine()->getRepository(Auction::class)->$queryFunction($this->getUser(), $json->filters);
        }

        dump($auctions);

        $auctionTitles = [];
        foreach ($auctions as $auction) 
        {
            array_push($auctionTitles, $auction[0]->getTitle());
        }


        return new JsonResponse([
            'auctions' => $auctionTitles
        ]);
    }
    // AUTOCOMPLETE GET AUCTIONS


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
        dump($result);

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



    /**
     * @Route("/get-notifications", name="get-notifications")
     */
    public function myNotifications(): Response
    {
        $em = $this->getDoctrine()->getManager(); 
        $notifications = $em->getRepository(Notification::class)->findBy([
            'recipientUser' => $this->getUser()
        ]);

        $now = new DateTime();

        foreach ($notifications as $notification) 
        {
            $notification->getSeenAt() == null ? $notification->wasNull = true : $notification->wasNull = false ;

            if($notification->wasNull)
            {
                $notification->setSeenAt($now);
                $em->persist($notification);
            }
        }
        $em->flush();

        dump($notifications);

        return $this->render('parts/ajax/notifications_part.html.twig',[
            'notifications' => $notifications
        ]);
    }

    // GET UNREAD
    /**
     * @Route("/get-notifications-ur", name="get-notifications-ur")
     */
    public function getUnreadNotifications(): Response
    {
        $em = $this->getDoctrine()->getManager(); 
        $notifications = $em->getRepository(Notification::class)->findBy([
            'recipientUser' => $this->getUser(),
            'seenAt' => null
        ]);

       /* $now = new DateTime();

        foreach ($notifications as $notification) 
        {
            $notification->getSeenAt() == null ? $notification->wasNull = true : $notification->wasNull = false ;

            if($notification->wasNull)
            {
                $notification->setSeenAt($now);
                $em->persist($notification);
            }
        }
        $em->flush();

        dump($notifications);*/

        return $this->render('parts/ajax/notifications_part.html.twig',[
            'notifications' => $notifications
        ]);
    }

    // DONT MARK AS READ
    /**
     * @Route("/get-notifications-dm", name="get-notifications-dm")
     */
    public function getDontMarkNotifications(): Response
    {
        $em = $this->getDoctrine()->getManager(); 
        $notifications = $em->getRepository(Notification::class)->findBy([
            'recipientUser' => $this->getUser()
        ]);

        return $this->render('parts/ajax/notifications_part.html.twig',[
            'notifications' => $notifications
        ]);
        /*return new JsonResponse([
            'notifCount' => count($notifications),
            'render' => $this->render('parts/ajax/notifications_part.html.twig',[
                'notifications' => $notifications
            ])->getContent()
        ]);*/
    }

    /**
     * @Route("/mar-notifications", name="mar-notifications")
     */
    public function markNotificationsAsRead(): Response
    {
        $em = $this->getDoctrine()->getManager(); 

        $notifications = $em->getRepository(Notification::class)->findBy([
            'recipientUser' => $this->getUser(),
            'seenAt' => null
        ]);

        $now = new DateTime();

        foreach ($notifications as $notification) 
        {
            $notification->setSeenAt($now);
            $em->persist($notification);
        }
        $em->flush();

        dump($notifications);

        return new Response ('dene');
    }
}
