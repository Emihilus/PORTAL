<?php
// PDPAPW.PL EXAMPLE PHPUNIT TESTS - EMIS
namespace App\Tests\RoutesLoad;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoutesTest extends WebTestCase
{
    public function testUsersListLoaded()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/users-list');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Users list');

        $this->assertStringContainsString('details', $crawler->filterXpath('//div[@class="container container-fluid"]/a')->text());
        // dump($crawler->filterXPath('//div[@class="container container-fluid"]/a[position()=2]')->attr('href'));

        return [
            'profile-details' => $crawler->filterXPath('//div[@class="container container-fluid"]/a')->attr('href'),
            'username' => $crawler->filterXPath('//div[@class="container container-fluid"]/span[position()=1]')->text()
        ];
    }

    /**
     * @depends testUsersListLoaded
     */
    public function profileDetailsLoaded($path)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path['profile-details']);
        $this->assertStringContainsString('Wszystkie aukcje', $crawler->filterXpath('//span[@id="pu"]')->text());
    }

}
