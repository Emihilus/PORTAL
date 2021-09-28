<?php
// PDPAPW.PL EXAMPLE PHPUNIT TESTS - EMIS
namespace App\Tests;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class CommentPantherTest extends PantherTestCase
{
    public function testPostingCommentAndUserLoginSuccessfull(): void
    {
        $client = $this->loginPantherClient(static::createPantherClient());

        $crawler = $client->request('GET', '/auction-details/1');

        $randomComment = 'S3cr3t Str1ng '.random_int(0,1000);
        $crawler->selectButton('Zamieść komentarz')->form([
            'ctA' => $randomComment
        ]);
        $client->executeScript('submitComment();');
        $client->waitForElementToContain('//span[@class="ncc"]', $randomComment);
        // check new comment exists
        $this->assertSame($randomComment, $crawler->filterXpath('//span[@class="ncc"]')->text());

        $newCommentElemId = $crawler->filterXpath('//span[@class="ncc"]')->attr('id');

        // check after page refresh new comment still exists
        $crawler = $client->request('GET', '/auction-details/1');
        $this->assertSame($randomComment, $crawler->filterXpath("//span[@id='$newCommentElemId']")->text());

    }

    protected function loginPantherClient(Client $client)
    {
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Zaloguj')->form([
            'username' => 'Emis',
            'password' => '12345'
        ]);
        $client->submit($form);
        return $client;
    }
}
