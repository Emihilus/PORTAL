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
                        $replyComment->setContent('Przyk??adowa odpowied?? sprzedawcy');
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
            ['Piec wendzarzniczy Huel', (new \DateTime()), '+1 day', 'Huel Hot & Savoury to pe??nowarto??ciowe, ciep??e i smaczne danie na bazie ro??lin, kt??re zawiera wszystkie z 26 niezb??dnych witamin i sk??adnik??w mineralnych. Pe??en pysznych sk??adnik??w, smaku i wszystkiego czego potrzebujesz, aby zachowa?? dobr?? kondycj?? i zdrowie.'],
            ['Piec Ptaki', (new \DateTime()), '+4 days', 'G??o??ny ma??y ptaszek podobny do wr??belka ale wydaje mi si?? ??e to mo??e by?? bilbil ogrodowy???? czy kto?? potwierdzi?'],
            ['Piec Przyrodnicze w??dr??wki', (new \DateTime()), '+6 days', 'BOCIANY w po??owie sierpnia rozpoczynaj?? swoj?? coroczn?? podr???? do Afryki.'],
            ['Piec Astrofotografia Polska', (new \DateTime()), '+6 days', 'Noc Perseid??w ????????
            Ten kr??tki film to efekt mojej kilkugodzinnej, nocnej pracy z gwiazdami.
            Jest to 200 zdj???? drogi mlecznej.
            Ka??de na??wietlane 20sek z jedno sekundow?? przerw?? mi??dzy zdj??ciami.
            200x20sek + 200sek = 4200sek 
            (70minut)
            Tyle czasu jest potrzebne by stworzy?? film 10sekundowy.
            Kto?? pomy??li: po co?
            Ja odpowiem: bo warto!
            ...a Wam jak si?? podoba taki efekt? ????
            Przyjemno??ci ???????????
            Fot: Micha?? Szyszka Fotografia'],


            ['Piec PAleteia PL - Duchowo???? i lifestyle', (new \DateTime()), '+6 days', 'W jednej z praskich dzielnic znajduje si?? ko??ci???? pw. Naj??wi??tszej Maryi Panny Zwyci??skiej, a w nim otoczona kultem figurka Dzieci??tka Jezus. Od 400 lat wznoszone s?? tutaj modlitwy, a dzi??ki Jezusowi dokonuje si?? te?? wiele cud??w.'],


            ['Piec Hustler', (new \DateTime()), '+6 days', 'Tzw. "przed" i "po".
            Fotki wykonane w pi??tek 13 sierpnia
            w Bukowinie Tatrza??skiej, z Wierchu Olcza??skiego ok. godz.22:30.
            Widoczne ??wiat??o na jednym ze szczyt??w to Kasprowy Wierch, a ??wiate??ko poni??ej to My??lenickie Turnie (stacja przesiadkowa).
            Zrobi??em rekonesans na s??ynnej ??apszance,
            ale uzna??em, ??e z moim amatorskim hobby
            nie zrobi?? nic lepszego ni?? by??o tu ju?? prezentowane (w szczeg??lno??ci w sprawie obr??bki DM).
            Czy co?? tam wida?? - oce??cie sami ????
            Info:
            20mm; 8x15sek, f/1.4; ISO-1600. 
            8 klatek po????czone w programie Sequator
            i majsterkowane suwakami w Lr.
            CANON EOS 6D MkII + SIGMA A 20mm f/1.4'],


            ['Piec Kandydaci na Studia 2021 (Oficjalna Grupa)', (new \DateTime()), '+6 days', 'Hej, szuka kto?? mo??e wsp????lokatorki? Ewentualnie ??eby razem poszuka?? mieszkania tak w max 3 osoby ????
            Jestem spokojn?? osob??, lubi?? czysto???? i porz??dek, problem??w ze mn?? nie powinno by?? i tego samego oczekuj?? ????
            Okolice Sky Tower by??yby okay, chocia?? troch?? dalej te?? dam rad??, jakie?? ta??sze mieszkanie za max 1000 z op??atami. B??d?? studiowa?? na UWr (wydzia?? na Pocztowej).'],


            ['Piec eToro ', (new \DateTime()), '+6 days', 'S??ysza??e?? o SHIBA INU? W to kryptoaktywo mo??na teraz inwestowa?? na eToro. Do????cz ju?? dzi?? i przekonaj si?? sam, dlaczego SHIB wzbudza entuzjazm w??r??d inwestor??w na eToro.'],


            ['Piec Ciekawostkawka', (new \DateTime()), '+6 days', 'Wrak punickiego statku wojennego z Marsali
            W 1971 roku, w porcie sycylijskiego miasta Marsala odkryto pozosta??o??ci punickiego statku wojskowego. S?? to najstarsze odkryte zachowane szcz??tki tego typu. Badacze okre??laj?? statek jako ???Punta Scario??? i ich zdaniem mia?? on albo charakter zwiadowczy, albo s??u??y?? do tarowania mniejszych ??odzi.
            O pochodzeniu statku naukowcy wiedz?? za spraw?? zachowanych liter na kad??ubie statku. W szcz??tkach statku natrafiono na liczne naczynia, r????nego kszta??tu oraz ko??ci zwierz??t (jeleni, owiec, k??z czy ??wi??). Co interesuj??ce, archeolodzy natrafili tak??e na ??odygi marihuany ??? badacze sugeruj??, ??e te mog??y by?? ??ute przez wio??larzy.
            Statek mierzy?? do oko??o 35 metr??w, a jego szeroko???? wynosi??a do ok. 4,8 metr??w. G????boko???? zanurzenia wynosi??a z kolei do 2,7 metra. Jak s??dz?? badacze statek m??g?? bra?? udzia?? w przegranej przez Kartagin?? bitwie morskiej ko??o Wysp Egadzkich w 241 roku p.n.e. P????niejsze badania drewna wskazuj?? rok 235 p.n.e., co sugeruje ??e statek mogli przej???? Rzymianie i wykorzystywa?? go w charakterze mniejszej jednostki rozpoznawczej (np. navis actuaria lub navis speculatoria). Kad??ub wykonany zosta?? z d??bu i mia?? delikatn?? oraz smuk???? konstrukcj??.
            Artefakt znajduje si?? w Parco Archeologico di Lilibeo w Marsali (Sycylia).'],


            ['Piec Gapi?? si?? w niebo noc??', (new \DateTime()), '+6 days', 'Perseidy 2021 ????????
            G????wnym zamys??em tego zdj??cia, by??o uchwycenie drogi mlecznej nad Tatrami Zachodnimi. Jednak liczy??em na to, ??e podczas maksimum Perseid??w, cho?? kilka wpadnie w kadr. 
            W czasie, gdy z grupk?? znajomych le??eli??my na kocach na ??cie??ce rowerowej, aparat na statywie dzielnie ??apa?? fotony i meteory. W noc maksimum by??o ch??odno, bo ledwie 9 stopni, wiec nie siedzieli??my tam d??u??ej ni?? dwie godziny. Jednak uda??o si?? uchwyci?? go??ym okiem kilkadziesi??t jasnych Perseid??w zostawiaj??cych ??liczne zielone smugi, a kt??rych przeloty wywo??ywa??y g??o??ne okrzyki zachwytu. 
            Drugiego dnia, zn??w wybra??em si?? w to miejsce z kumplem, by nazbiera??, troch?? "spadak??w". Posiedzieli??my ok dwie godziny, podczas kt??rych meteory pojawia??y si?? z podobn?? intensywno??ci?? co dnia poprzedniego ????????.
            Mamy tu materia?? z r??wno dw??ch godzin fotografowania (40 minut pierwszego i 80 drugiego dnia). Okaza??o si??, ??e na ponad 20 klatkach pojawi??y si?? pi??kne, szmaragdowe kreseczki. 
            Po kilkugodzinym dopasowywaniu i ustawianiu zdj???? efekt prezentuje si?? nastepuj??co. ????????
            Zarowno g??ra jak i d???? to w sumie 40 minut (150 - 15-sto sekundowych klatek) na ISO 800, Sigm?? Art 18-35mm przy 18 mm ogniskowej, z przes??on?? 1,8 podpi??t?? do Nikona D5300. Perseidy za?? dodane s?? ????cznie z 19 poszczeg??lnych klatek.
            Wi??cej moich zdj???? mo??ecie znale???? na IG: https://www.instagram.com/kuba12988/
             PS S??owa krytyki, czy te?? rady mile widziane, jednak nie ukrywam jestem zadowolony ze swojej roboty ????????'],
        ];
    }

    public static function getGroups(): array
     {
        return ['grouasdp13'];
     }
}
