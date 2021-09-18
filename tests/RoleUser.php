<?php
namespace App\Tests;

use App\Repository\UserRepository;

trait RoleUser
{

    public function setUp(): void
    {
        parent::setUp();// ? not needed?
        
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $container = self::$container;
        $cache = self::$container->get('App\Utils\Interfaces\CacheInterface');
        $this->cache = $cache->cache;
        $this->cache->clear();
        self::ensureKernelShutdown();

     //.parent::setUp();
        $this->client = static::createClient(); /*[],[    WHAY??????
            'PHP_AUTHd_USER' => 'jahael@gmail.com',
            'PHP_AUTH_PW' => '12345',
        ]);*/
        //$this->client->insulate();

        $userRepository = $this->client->getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('rmsysz@gmail.com');
        
        // simulate $testUser being logged in
        $this->client->loginUser($testUser);
        

        $this->client->disableReboot();

        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        // RESPONSIBLE FOR NOT REAL UPDATING DB
        $this->entityManager->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);
        // RESPONSIBLE FOR NOT REAL UPDATING DB
    }

    public function tearDown():void
    {
        parent::tearDown();
        $this->cache->clear();
        // RESPONSIBLE FOR NOT REAL UPDATING DB
        $this->entityManager->rollback();
        $this->entityManager->close(); // avoid memory leaks
        // RESPONSIBLE FOR NOT REAL UPDATING DB
        $this->entityManager = null;
        // self::ensureKernelShutdown();
    }
}