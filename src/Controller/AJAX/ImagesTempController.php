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
class ImagesTempController extends AbstractController
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
}