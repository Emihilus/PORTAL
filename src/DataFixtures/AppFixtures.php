<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager, UserPasswordEncoderInterface $hasher)
    {
        $this->loadUsers($manager);
    }

    private function loadUsers(ObjectManager $manager) : void
    {
        foreach ($this->getUserData() as [$name, $last_name, $email, $password, $api_key, $roles])
        {
            $user = new User();
            $user->setName($name);
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
            ['Jon', 'Smitch', 'jahael@gmail.com', '12345', 'ffb8a9b0cfd15ddef9bf7bffe8e7cb30', ['ROLE_ADMIN']],
            ['Maciek', 'Ryshka', 'rmsysz@gmail.com', '12345', 'apik', ['ROLE_USER']],
            ['Marciin', 'MaśLANKA', 'mmasl@gmail.com', '12345', 'apik', ['ROLE_USER']],
            ['Fourth', 'Sephiroth', 'zoro@gmail.com', '12345', 'apik', ['ROLE_USER']],
            ['Marian', 'Sephiroth', 'marianus@gmail.com', '12345', 'apik', ['ROLE_USER']]
        ];
    }
}
