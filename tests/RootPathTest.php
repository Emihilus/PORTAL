<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RootPathTest extends WebTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->followRedirects();
        $crawler = $client->request('GET', '/');
        // $this->assertResponseStatusCodeSame(301);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('span', 'PDPAPW');
    }
}
