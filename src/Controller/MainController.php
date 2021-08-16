<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Flex\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="hime")
     */
    public function index(UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
