<?php

namespace App\DataFixtures;

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
    }

    private function loadUsers(ObjectManager $manager) : void
    {
        foreach ($this->getUserData() as [$name, $last_name, $email, $password, $api_key, $roles])
        {
            $user = new User();
            $user->setUsername($name);
            $user->setLastName($last_name);
            $user->setEmail($email);
            $user->setPassword($this->pwd_encoder->encodePassword($user, $password));
            $user->setVimeloApiKey($api_key);
            $user->setRoles($roles);
            $manager->persist($user);
        }
    }

    public function getUserData(): array
    {
        return[
            ['Emis', 'Smitch', 'jahael@gmail.com', '12345', 'ffb8a9b0cfd15ddef9bf7bffe8e7cb30', ['ROLE_ADMIN']],
            ['Maciek', 'Ryshka', 'rmsysz@gmail.com', '12345', 'apik', ['ROLE_USER']],
            ['Marciin', 'MaśLANKA', 'mmasl@gmail.com', '12345', 'apik', ['ROLE_USER']],
            ['Fourth', 'Sephiroth', 'zoro@gmail.com', '12345', 'apik', ['ROLE_USER']],
            ['Marian', 'Sephiroth', 'marianus@gmail.com', '12345', 'apik', ['ROLE_USER']]
        ];
    }
}
