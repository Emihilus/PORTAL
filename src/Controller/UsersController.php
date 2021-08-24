<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * @Route("/my-profile", name="my-profile")
     */
    public function myProfile(): Response
    {
       
        return $this->render('userprofile/my_profile.html.twig', []);
    }
}
