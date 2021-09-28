<?php
// PDPAPW.PL EXAMPLE PHPUNIT TESTS - EMIS
namespace App\Tests\RoutesLoad;

use Symfony\Component\Panther\PantherTestCase;

class PantherAuctionListPageTes extends PantherTestCase
{
    public function testAuctionListLoaded()
    {
        $client = static::createPantherClient();
        $crawler = $client->followRedirects();
        $crawler = $client->request('GET', '/');

        $client->waitFor('#auctions');

        // Assert first item loaded
        $this->assertStringContainsString('koniec',$crawler->filterXpath('//ul[@id="auctions"]/div[@class="d-flex my-1"]/a/div[contains(@class,"first-sectionn")]')->eq(2)->text());

        return substr($crawler->filterXpath('//ul[@id="auctions"]/div/a')->attr('onclick'),6,-2);
    }

    /**
     * @depends testAuctionListLoaded
     */
    public function testAuctionDataLoaded($exampleAuctionPath)
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', $exampleAuctionPath);

        $this->assertStringContainsString('Aukcja', $crawler->filterXpath('//span[@id="h1cap"]')->text());

        $container = self::bootKernel()->getContainer();

        return [
        'username' => $crawler->filterXpath('//a[@class="appLink"]')->text(),
        'mModes' => [
            $container->getParameter('mm_Sold_Selling'),
            $container->getParameter('mm_Sold'),
            $container->getParameter('mm_Selling'),
            $container->getParameter('mm_Leading_In'),
            $container->getParameter('mm_Won'),
            $container->getParameter('mm_Participating_In'),
            $container->getParameter('mm_Participating_In_Not_Leading'),
            $container->getParameter('mm_Participated_In'),
            $container->getParameter('mm_Participated_In_Not_Leading')
            ]
        ];
    }

    /**
     * @depends testAuctionDataLoaded
     */
    public function testUserAuctions($passed)
    {
        $client = static::createPantherClient();

        foreach($passed['mModes'] as $mMode)
        {
            $crawler = $client->request('GET', '/user-auctions/'.$passed['username'].'/'.$mMode);
            $this->assertAuctionsAppeared($client,$crawler);
        }
        

    }


    private function assertAuctionsAppeared($client, $crawler)
    {
        $client->waitFor('#auctions');

        if($crawler->filterXpath('//b[@id="none"]')->count() != 0)
        {
            $this->assertSame('Brak aukcji',$crawler->filterXpath('//b[@id="none"]')->text());
        }
        else
        {
            $this->assertStringContainsString('koniec',$crawler->filterXpath('//ul[@id="auctions"]/div[@class="d-flex my-1"]/a/div[contains(@class,"first-sectionn")]')->eq(2)->text());
        }

    }
}

