<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RootPathTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(301);

        $crawler = $client->followRedirects();
        $this->assertSelectorTextContains('span', 'PDPAPW');
    }
}
