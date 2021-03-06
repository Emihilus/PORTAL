<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Offer;
use App\Entity\Auction;
use App\Entity\Comment;
use App\Entity\AuctionImage;
use App\DataFixtures\RandomGenerator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    const numAuctions = 400;

    private UserPasswordHasherInterface $passwordHasher;
    private ContainerInterface $container;

    public function __construct(UserPasswordHasherInterface $passwordHasher,ContainerInterface $container)
    {
        $this->passwordHasher = $passwordHasher;
        $this->tempUserArray = [];
        $this->container = $container;
    }
// etno tobiaszz za zabka

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadAuctions($manager);
    }

    private function loadUsers(ObjectManager $manager) : void
    {
        foreach ($this->getUserData() as [$username,  $email, $password, $roles,$phone])
        {
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setRoles($roles);
            $user->setPhone($phone);
            $user->setIsVerified(true);
            $manager->persist($user);
            array_push($this->tempUserArray, $user);
        }

        $manager->flush();
    }


    private function loadAuctions(ObjectManager $manager) : void
    {

        $array = $this->getAuctionData();
       
        $standardImagesArray = [];
        foreach (new \DirectoryIterator($this->container->getParameter('auctionImagePath')) as $file) 
        {
            if ($file->isFile()) 
            {
                array_push($standardImagesArray, $file->getFilename());
            }
        }



        // foreach ($array as [$title,  $createdAt, $PEROID, $description])
        for($j = 0; $j < static::numAuctions ; $j++)
        {
            $auction = new Auction();
            $auctionCreator = $this->tempUserArray[random_int(0,count($this->tempUserArray)-1)];
            $auction->setByUser($auctionCreator);
            $auction->setTitle(RandomGenerator::generateRandomName());

            $auction->setCreatedAt(new \DateTime(random_int(-10,-1).' days'));
            $auction->setEndsAtManually(new \DateTime(random_int(-1,6).' days'));

            

            $auction->setDescription(($j%2==0) ? RandomGenerator::generateRandomSentence(200) : $array[random_int(0,count($array)-1)][3]);


            // CENA WYWOLAWCZA
            $offer = new Offer();
            $offer->setAuction($auction);
            $offer->setCreatedAt($auction->getCreatedAt());
            $offer->setValue(200);

            $manager->persist($offer);

            // ADD OFFERS WITH RANDOM VALUE AND PAST DATE

            $hghstValue = 0;
            for ($i = 0; $i< random_int(1,10); $i++)
            {
                $user = $this->tempUserArray[random_int(0,count($this->tempUserArray)-1)];
                $value = random_int(0,454543);

                if($auction->getEndsAt()< new \DateTime())
                {
                    if($value > $hghstValue)
                    {
                        $hghstUser = $user;
                        $hghstValue = $value;
                    }
    
                }
                $offer = new Offer();
                $offer->setByUser($user);
                $offer->setAuction($auction);
                $offer->setCreatedAt(new \DateTime('-'.random_int(1,1000000).' seconds'));
                $offer->setValue($value);
                
                // ADD RANDOM COMMENTS
                $comment = new Comment();
                $comment->setByUser($this->tempUserArray[random_int(0,count($this->tempUserArray)-1)]);
                $comment->setAuction($auction);
                $comment->setValue(-2);
                $comment->setContent(RandomGenerator::generateRandomSentence(10));
                $comment->addLikedBy($this->tempUserArray[random_int(0,count($this->tempUserArray)-1)]);
                $comment->addLikedBy($this->tempUserArray[random_int(0,count($this->tempUserArray)-1)]);
                $comment->addLikedBy($this->tempUserArray[random_int(0,count($this->tempUserArray)-1)]);

                $manager->persist($comment);
                $manager->persist($offer);
            }

            // ADD RANDOM BUYER COMMENTS
            if($auction->getEndsAt()< new \DateTime() && random_int(0,2) == 1)
                {
                    $result = random_int(-1,1);

                    switch ($result)
                    {
                        case -1:
                            $caption  = 'Przykladowy komentarz negatywny';
                            break;

                        case 0:
                            $caption  = 'Komentarz neutralny';
                            break;

                        case 1:
                            $caption  = 'Komentarz pozytywny';
                            break;

                    }

                    $comment = new Comment();
                    $comment->setByUser($hghstUser);
                    $comment->setAuction($auction);
                    $comment->setValue($result);
                    $comment->setContent($caption);
                    $manager->persist($comment);

                    if (random_int(0,3) == 0)
                    {
                        $replyComment = new Comment();
                        $replyComment->setByUser($auctionCreator);
                        $replyComment->setAuction($auction);
                        $replyComment->setValue(2);
                        $replyComment->setReplyTo($comment);
                        $replyComment->setContent('Przykładowa odpowiedź sprzedawcy');
                        $manager->persist($replyComment);
                    }


                }

            // ADD DUMMY IMAGES WITH RANDOM AMOUNT
            for ($x = 0 ; $x < random_int(2,5) ; $x++)
            {
                $auctionImage = new AuctionImage();
                $auctionImage->setAuction($auction);
                $auctionImage->setFilename($standardImagesArray[random_int(0,count($standardImagesArray)-1)]);
                $auctionImage->setOrderIndicator($x);
                $manager->persist($auctionImage);
            }

            $manager->persist($auction);
            //$counter++;
        }

        $manager->flush();
    }


    public function getUserData(): array
    {
        return[
            ['Emis', 'john@gmail.com', '12345ss', ['ROLE_ADMIN'], '519130641'],
            ['Emazemhs', 'jahael@gmail.com', '12345', ['ROLE_USER'], '2852757'],
            ['CoColiono', 'malcolmz@gmail.com', '12345', ['ROLE_USER'], '31201919'],
            ['Masterklas', 'antarka@gmail.com', '12345', ['ROLE_USER'], '797770170']
        ];
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


            ['Piec PAleteia PL - Duchowość i lifestyle', (new \DateTime()), '+6 days', 'W jednej z praskich dzielnic znajduje się kościół pw. Najświętszej Maryi Panny Zwycięskiej, a w nim otoczona kultem figurka Dzieciątka Jezus. Od 400 lat wznoszone są tutaj modlitwy, a dzięki Jezusowi dokonuje się też wiele cudów.'],


            ['Piec Hustler', (new \DateTime()), '+6 days', 'Tzw. "przed" i "po".
            Fotki wykonane w piątek 13 sierpnia
            w Bukowinie Tatrzańskiej, z Wierchu Olczańskiego ok. godz.22:30.
            Widoczne światło na jednym ze szczytów to Kasprowy Wierch, a światełko poniżej to Myślenickie Turnie (stacja przesiadkowa).
            Zrobiłem rekonesans na słynnej Łapszance,
            ale uznałem, że z moim amatorskim hobby
            nie zrobię nic lepszego niż było tu już prezentowane (w szczególności w sprawie obróbki DM).
            Czy coś tam widać - oceńcie sami 😉
            Info:
            20mm; 8x15sek, f/1.4; ISO-1600. 
            8 klatek połączone w programie Sequator
            i majsterkowane suwakami w Lr.
            CANON EOS 6D MkII + SIGMA A 20mm f/1.4'],


            ['Piec Kandydaci na Studia 2021 (Oficjalna Grupa)', (new \DateTime()), '+6 days', 'Hej, szuka ktoś może współlokatorki? Ewentualnie żeby razem poszukać mieszkania tak w max 3 osoby 😊
            Jestem spokojną osobą, lubię czystość i porządek, problemów ze mną nie powinno być i tego samego oczekuję 😀
            Okolice Sky Tower byłyby okay, chociaż trochę dalej też dam radę, jakieś tańsze mieszkanie za max 1000 z opłatami. Będę studiować na UWr (wydział na Pocztowej).'],


            ['Piec eToro ', (new \DateTime()), '+6 days', 'Słyszałeś o SHIBA INU? W to kryptoaktywo można teraz inwestować na eToro. Dołącz już dziś i przekonaj się sam, dlaczego SHIB wzbudza entuzjazm wśród inwestorów na eToro.'],


            ['Piec Ciekawostkawka', (new \DateTime()), '+6 days', 'Wrak punickiego statku wojennego z Marsali
            W 1971 roku, w porcie sycylijskiego miasta Marsala odkryto pozostałości punickiego statku wojskowego. Są to najstarsze odkryte zachowane szczątki tego typu. Badacze określają statek jako „Punta Scario” i ich zdaniem miał on albo charakter zwiadowczy, albo służył do tarowania mniejszych łodzi.
            O pochodzeniu statku naukowcy wiedzą za sprawą zachowanych liter na kadłubie statku. W szczątkach statku natrafiono na liczne naczynia, różnego kształtu oraz kości zwierząt (jeleni, owiec, kóz czy świń). Co interesujące, archeolodzy natrafili także na łodygi marihuany – badacze sugerują, że te mogły być żute przez wioślarzy.
            Statek mierzył do około 35 metrów, a jego szerokość wynosiła do ok. 4,8 metrów. Głębokość zanurzenia wynosiła z kolei do 2,7 metra. Jak sądzą badacze statek mógł brać udział w przegranej przez Kartaginę bitwie morskiej koło Wysp Egadzkich w 241 roku p.n.e. Późniejsze badania drewna wskazują rok 235 p.n.e., co sugeruje że statek mogli przejąć Rzymianie i wykorzystywać go w charakterze mniejszej jednostki rozpoznawczej (np. navis actuaria lub navis speculatoria). Kadłub wykonany został z dębu i miał delikatną oraz smukłą konstrukcję.
            Artefakt znajduje się w Parco Archeologico di Lilibeo w Marsali (Sycylia).'],


            ['Piec Gapię się w niebo nocą', (new \DateTime()), '+6 days', 'Perseidy 2021 🌠🌠
            Głównym zamysłem tego zdjęcia, było uchwycenie drogi mlecznej nad Tatrami Zachodnimi. Jednak liczyłem na to, że podczas maksimum Perseidów, choć kilka wpadnie w kadr. 
            W czasie, gdy z grupką znajomych leżeliśmy na kocach na ścieżce rowerowej, aparat na statywie dzielnie łapał fotony i meteory. W noc maksimum było chłodno, bo ledwie 9 stopni, wiec nie siedzieliśmy tam dłużej niż dwie godziny. Jednak udało się uchwycić gołym okiem kilkadziesiąt jasnych Perseidów zostawiających śliczne zielone smugi, a których przeloty wywoływały głośne okrzyki zachwytu. 
            Drugiego dnia, znów wybrałem się w to miejsce z kumplem, by nazbierać, trochę "spadaków". Posiedzieliśmy ok dwie godziny, podczas których meteory pojawiały się z podobną intensywnością co dnia poprzedniego 😁😁.
            Mamy tu materiał z równo dwóch godzin fotografowania (40 minut pierwszego i 80 drugiego dnia). Okazało się, że na ponad 20 klatkach pojawiły się piękne, szmaragdowe kreseczki. 
            Po kilkugodzinym dopasowywaniu i ustawianiu zdjęć efekt prezentuje się nastepująco. 😁😁
            Zarowno góra jak i dół to w sumie 40 minut (150 - 15-sto sekundowych klatek) na ISO 800, Sigmą Art 18-35mm przy 18 mm ogniskowej, z przesłoną 1,8 podpiętą do Nikona D5300. Perseidy zaś dodane są łącznie z 19 poszczególnych klatek.
            Więcej moich zdjęć możecie znaleźć na IG: https://www.instagram.com/kuba12988/
             PS Słowa krytyki, czy też rady mile widziane, jednak nie ukrywam jestem zadowolony ze swojej roboty 😜😅'],
        ];
    }

    public static function getGroups(): array
     {
        return ['grouasdp13'];
     }
}
