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
        foreach ($this->getAuctionData() as [$title,  $createdAt, $PEROID, $description])
        {
            $auction = new Auction();
            $auction->setByUser($this->tempUser);
            $auction->setTitle($title);
            $createdAtCurrent = clone $createdAt;
            $auction->setCreatedAt($createdAtCurrent);
            $createdAt->modify($PEROID);
            $auction->setEndsAt($createdAt);
            $auction->setDescription($description);
            $manager->persist($auction);
        }

        $manager->flush();
    }

    public function getAuctionData(): array
    {
        return[
            ['Piec wendzarzniczy Huel', (new \DateTime()), '+1 day', 'Huel Hot & Savoury to pełnowartościowe, ciepłe i smaczne danie na bazie roślin, które zawiera wszystkie z 26 niezbędnych witamin i składników mineralnych. Pełen pysznych składników, smaku i wszystkiego czego potrzebujesz, aby zachować dobrą kondycję i zdrowie.'],
            ['Piec Ptaki', (new \DateTime()), '+4 days', 'Głośny mały ptaszek podobny do wróbelka ale wydaje mi się że to może być bilbil ogrodowy😊 czy ktoś potwierdzi?'],
            ['Piec Przyrodnicze wędrówki', (new \DateTime()), '+6 days', 'BOCIANY w połowie sierpnia rozpoczynają swoją coroczną podróż do Afryki.'],
            ['Piec Astrofotografia Polska', (new \DateTime()), '+6 days', 'Noc Perseidów 🌠🌟
            Ten krótki film to efekt mojej kilkugodzinnej, nocnej pracy z gwiazdami.
            Jest to 200 zdjęć drogi mlecznej.
            Każde naświetlane 20sek z jedno sekundową przerwą między zdjęciami.
            200x20sek + 200sek = 4200sek 
            (70minut)
            Tyle czasu jest potrzebne by stworzyć film 10sekundowy.
            Ktoś pomyśli: po co?
            Ja odpowiem: bo warto!
            ...a Wam jak się podoba taki efekt? 😉
            Przyjemności 👊🙃❗
            Fot: Michał Szyszka Fotografia'],


            ['Piec Moon Card', (new \DateTime()), '+6 days', 'THE MOON CARD REPRESENTS THE JOURNEY INTO UKNOWN'],


            ['Piec Przyrodnicze wędrówki', (new \DateTime()), '+6 days', 'BOCIANY w połowie sierpnia rozpoczynają swoją coroczną podróż do Afryki.'],


            ['Piec Przyrodnicze wędrówki', (new \DateTime()), '+6 days', 'BOCIANY w połowie sierpnia rozpoczynają swoją coroczną podróż do Afryki.'],


            ['Piec Przyrodnicze wędrówki', (new \DateTime()), '+6 days', 'BOCIANY w połowie sierpnia rozpoczynają swoją coroczną podróż do Afryki.'],


            ['Piec Przyrodnicze wędrówki', (new \DateTime()), '+6 days', 'BOCIANY w połowie sierpnia rozpoczynają swoją coroczną podróż do Afryki.'],


            ['Piec Przyrodnicze wędrówki', (new \DateTime()), '+6 days', 'BOCIANY w połowie sierpnia rozpoczynają swoją coroczną podróż do Afryki.'],


            ['Piec Przyrodnicze wędrówki', (new \DateTime()), '+6 days', 'BOCIANY w połowie sierpnia rozpoczynają swoją coroczną podróż do Afryki.'],

            ['Piec Przyrodnicze wędrówki', (new \DateTime()), '+6 days', 'BOCIANY w połowie sierpnia rozpoczynają swoją coroczną podróż do Afryki.'],
        ];
    }
}
