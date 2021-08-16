<?php

namespace App\DataFixtures;

use App\Entity\Auction;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadAuctions($manager);
    }

    private function loadUsers(ObjectManager $manager) : void
    {
        foreach ($this->getUserData() as [$username,  $email, $password, $roles])
        {
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setRoles($roles);
            $manager->persist($user);
            $this->tempUser = $user;
        }

        $manager->flush();
    }

    public function getUserData(): array
    {
        return[
            ['Emis', 'jahael@gmail.com', '12345', ['ROLE_ADMIN']]
        ];
    }


    private function loadAuctions(ObjectManager $manager) : void
    {
        foreach ($this->getAuctionData() as [$title,  $createdAt, $description])
        {
            $auction = new Auction();
            $auction->setByUser($this->tempUser);
            $auction->setTitle($title);
            $createdAtCurrent = $createdAt;
            $auction->setCreatedAt($createdAtCurrent);
            $ends = $createdAt->modify('+1 day');
            $auction->setEndsAt($ends);
            $auction->setDescription($description);
            $manager->persist($auction);
        }

        $manager->flush();
    }

    public function getAuctionData(): array
    {
        return[
            ['Piec wendzarzniczy BOLOKS', (new \DateTime()), 'Mały, ale wariat.']
        ];
    }
}
