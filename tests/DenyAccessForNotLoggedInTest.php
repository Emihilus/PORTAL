<?php
// PDPAPW.PL EXAMPLE PHPUNIT TESTS - EMIS
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DenyAccessForNotLoggedInTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();

        $kernel = self::bootKernel()->getContainer()->get('router');
        foreach ([
            $kernel->generate('create-auction'),
            $kernel->generate('place-acomment'),
            $kernel->generate('comment-all-auctions'),
            $kernel->generate('my-profile'),
            $kernel->generate('change-password'),
            $kernel->generate('my-notifications'),
        ] as $path)
        {
            $crawler = $client->request('GET', $path);
            $this->assertResponseStatusCodeSame(302);
        }
    }

}
