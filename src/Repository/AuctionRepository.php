<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Auction;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * @method Auction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Auction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Auction[]    findAll()
 * @method Auction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuctionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auction::class);
    }




    // /**
    //  * @return Auction[] Returns an array of Auction objects
    //  */

    public function findAllWithFirstAuctionImage()
    {
       return $this->createQueryBuilder('a')
            ->leftJoin('a.images', 'i')
            ->addSelect('i.filename')
            ->where('i.orderIndicator = 0')
            ->orWhere('i.orderIndicator IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllWithAuctionImages()
    {
        //$em = $this->getEntityManager();
       return $this->createQueryBuilder('a')
            ->leftJoin('a.images', 'i')
            ->addSelect('i')
            ->getQuery()
            ->getResult()
        ;
        //return $em->createQuery('SELECT a, i FROM auction a LEFT JOIN a.auction_image i')->getResult();
    }
    

    
    public function findOneByIdWithOffers($value): ?Auction
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.offers', 'o')
            ->addSelect('o')
            ->Where('a.id = :val')
            ->setParameter('val', $value)
            ->orderBy('o.Value', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByIdWithAuctionImagesAndOffers($value): ?Auction
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.images', 'i')
            ->addSelect('i')
            ->leftJoin('a.offers', 'o')
            ->addSelect('o')
            ->Where('a.id = :val')
            ->setParameter('val', $value)
            ->orderBy('o.Value', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByIdWithAuctionImagesAndOffersAndComments($value): ?Auction
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.images', 'i')
            ->addSelect('i')
            ->leftJoin('a.comments', 'c')
            ->addSelect('c')
            ->leftJoin('a.offers', 'o')
            ->addSelect('o')
            ->Where('a.id = :val')
            ->setParameter('val', $value)
            ->orderBy('o.Value', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByIdWithHighestOffer($value)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.offers', 'o')
            ->addSelect('MAX(o.Value)')
            ->Where('a.id = :val')
            ->groupBy('a.id')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    

    public function findAllWithFirstImageAndHighestOffer()
    {
       return $this->createQueryBuilder('a')
            ->addSelect('('.$this->createQueryBuilder('b')
            ->select('MAX(o.Value)')
            ->from('App\Entity\Offer', 'o')
            ->where('a.id = o.auction')
            ->getDQL(). ') as hghst')

            ->leftJoin('a.images', 'i')
            ->addSelect('i.filename')
            ->where('i.orderIndicator = 0')
            ->orWhere('i.orderIndicator IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllWithFirstImageAndHighestOfferByUser($user)
    {
       return $this->createQueryBuilder('a')
            ->addSelect('('.$this->createQueryBuilder('b')
            ->select('MAX(o.Value)')
            ->from('App\Entity\Offer', 'o')
            ->where('a.id = o.auction')
            ->getDQL(). ') as hghst')

            ->leftJoin('a.images', 'i')
            ->addSelect('i.filename')
            ->where('i.orderIndicator = 0')
            ->orWhere('i.orderIndicator IS NULL')


            ->andWhere('a.byUser = :val')
            ->setParameter('val', $user)

            ->getQuery()
            ->getResult()
        ;
    }


// wybierz aukcje dolacz info czy ten auction id jest w liked auctions by specified usr

    public function findAllWithFirstImageAndHighestOfferWithOwner(?User $user, $filters = null)
    {
        $query = $this->createQueryBuilder('a')
        ->addSelect('('.$this->createQueryBuilder('b')
        ->select('MAX(o.Value)')
        ->from('App\Entity\Offer', 'o')
        ->where('a.id = o.auction')
        ->getDQL(). ') as hghst')

        ->leftJoin('a.images', 'i')
        ->addSelect('i.filename')
        ->where('i.orderIndicator = 0')
        ->orWhere('i.orderIndicator IS NULL')

        ->leftJoin('a.byUser', 'u')
        ->addSelect('u.username');

        // IMPERFECT
        if($user)
        {
            $query->leftJoin('a.likedByUsers', 'l', Expr\Join::WITH, 'l.id = :user')
            ->setParameter('user', $user->getId())
            ->addSelect('l');
        }

        if($filters)
        {
            if(isset($filters->f_search))
            {
                if($filters->fo_search == 1)
                    $byWhat = 'a.title';
                else
                    $byWhat = 'a.description';

                $query->andWhere($byWhat.' LIKE :sQuery')
                ->setParameter('sQuery', '%'.$filters->f_search.'%');
            }

            if(isset($filters->f_liveness))
            {
                switch($filters->f_liveness)
                {
                    case 1:
                        $query->andWhere('a.endsAt > :date')
                        ->setParameter('date', new \DateTime());
                        break;

                    case 2:
                        $query->andWhere('a.endsAt < :date')
                        ->setParameter('date', new \DateTime());
                        break;

                    case 4:
                        $query->andWhere('a.endsAt > :date')
                        ->setParameter('date', new \DateTime())
                        ->andWhere('a.endsAt < :dateN')
                        ->setParameter('dateN', new \DateTime('+1 day'));
                        break;

                }
            }

            if(isset($filters->f_prices) && $filters->f_prices > 0)
            {
                $query->andHaving('hghst > :sval')
                ->setParameter('sval', $filters->f_prices*100);
            }

            if(isset($filters->f_pricee) && $filters->f_pricee > 0)
            {
                $query->andHaving('hghst < :enval')
                ->setParameter('enval', $filters->f_pricee*100);
            }

            if(isset($filters->f_byuser))
            {
                $query->andWhere('a.byUser.username = :byusr')
                ->setParameter('bysr', $filters->f_byuser);
            }
        }

        $query = $query->getQuery()->getResult();


       return $query;
    }

    public function findAllWithFirstImageAndHighestOfferWithOwner2(?User $user, ?User $currentUser)
    {
        $query = $this->createQueryBuilder('a')
        ->addSelect('('.$this->createQueryBuilder('b')
        ->select('MAX(o.Value)')
        ->from('App\Entity\Offer', 'o') 
        ->where('a.id = o.auction')
        ->getDQL(). ') as hghst')

        ->leftJoin('a.images', 'i')
        ->addSelect('i.filename')
        ->where('i.orderIndicator = 0')
        ->orWhere('i.orderIndicator IS NULL')

        ->leftJoin('a.byUser', 'u')
        ->addSelect('u.username')
        
        ->andWhere('a.byUser = :val')
        ->setParameter('val', $user)
        ;

        // IMPERFECT
        if($user)
        {
            $query->leftJoin('a.likedByUsers', 'l', Expr\Join::WITH, 'l.id = :user')
            ->setParameter('user', $user->getId())
            ->addSelect('l');
        }


        $query = $query->getQuery()->getResult();


       return $query;
    }

   /* public function queryAuctionsWithSpecifiLeadingcUserCollection($user)
    {
       return $this->createQueryBuilder('a')
       ->leftJoin('('.$this->createQueryBuilder('b')
       ->select('App\Entity\Offer')
       ->from('App\Entity\Offer', 'r')
       ->where('')
       )



            ->where('e.Value=('.$this->createQueryBuilder('b')
                ->select('MAX(r.Value)')
                ->from('App\Entity\Offer', 'r')
                ->where('r.auction=e.auction')
                ->getDQL().')')

            ->andWhere('r.byUser =  :val')
            ->setParameter('val', $user)
            
            ->getQuery()
            ->getResult()
        ;
    }*/


// WORKING
    public function dqlLeadingAuctionsOfUser($user)
    {
        $dql = 'SELECT a, i.filename, 

        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a.id = oa.auction
        ) as hghst 

        FROM App\Entity\Offer o 


        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i


        WHERE o.byUser=?1 
        AND o.Value=(SELECT MAX(f.Value) FROM App\Entity\Offer f WHERE f.auction=o.auction)
        AND a.endsAt>CURRENT_TIMESTAMP()
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)';
        
        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }
    
    public function dqlParticipatingAuctionsOfUser($user)
    {
        $dql = 'SELECT a, i.filename, 
        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a.id = oa.auction
        ) as hghst 

        FROM App\Entity\Offer o 

        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i

        WHERE o.byUser=?1
        AND a.endsAt>CURRENT_TIMESTAMP()
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)';


        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }

    public function dqlParticipatedAuctionsOfUser($user)
    {
        $dql = 'SELECT a, i.filename, 
        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a.id = oa.auction
        ) as hghst 

        FROM App\Entity\Offer o 

        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i

        WHERE o.byUser=?1
        AND a.endsAt<CURRENT_TIMESTAMP()
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)';


        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }

    public function dqlWonAuctionsOfUser($user)
    {
        $dql = 'SELECT a, i.filename, 
        
        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a.id = oa.auction
        ) as hghst 

        FROM App\Entity\Offer o 


        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i


        WHERE o.byUser=?1 
        AND o.Value=(SELECT MAX(f.Value) FROM App\Entity\Offer f WHERE f.auction=o.auction) 
        AND a.endsAt<CURRENT_TIMESTAMP()
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)';

        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }

    public function dqlSoldAuctionsOfUser($user)
    {
        $dql = 'SELECT a, i.filename, 

        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a.id = oa.auction
        ) as hghst 

        FROM App\Entity\Auction a
        LEFT JOIN a.images i

        WHERE a.byUser=?1 
        AND a.endsAt<CURRENT_TIMESTAMP()
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)';


        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }

    public function dqlCurrentAuctionsOfUser($user)
    {
        $dql = 'SELECT a, i.filename, 
        
        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a.id = oa.auction
        ) as hghst 

        FROM App\Entity\Auction a
        LEFT JOIN a.images i

        WHERE a.byUser=?1 
        AND a.endsAt>CURRENT_TIMESTAMP()

        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)';
        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }

    public function dqlParticipatingNotLeadingAuctionsOfUser($user)
    {
        $dql = 'SELECT a, i.filename, 
        
        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a = oa.auction
        ) as hghst 

        FROM App\Entity\Offer o 

        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i

        WHERE o.byUser=?1 
        AND o.Value<(SELECT MAX(f.Value) FROM App\Entity\Offer f WHERE f.auction=o.auction)
        AND 0=(SELECT COUNT(b) FROM App\Entity\Offer e 
        LEFT JOIN App\Entity\Auction b WITH b=e.auction 
        WHERE b=a
        AND e.Value=(SELECT MAX(r.Value) FROM App\Entity\Offer r WHERE r.auction=e.auction) 
        AND e.byUser=?1)
        AND a.endsAt>CURRENT_TIMESTAMP()
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)';


        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }
    
    /// hasnt won
    public function dqlParticipatedNotLeadingAuctionsOfUser($user)
    {
        $dql = 'SELECT a, i.filename, 
        
        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a.id = oa.auction
        ) as hghst 

        FROM App\Entity\Offer o 

        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i

        WHERE o.byUser=?1 
        AND o.Value<(SELECT MAX(f.Value) FROM App\Entity\Offer f WHERE f.auction=o.auction)
        AND 0=(SELECT COUNT(b) FROM App\Entity\Offer e 
        LEFT JOIN App\Entity\Auction b WITH b=e.auction 
        WHERE b=a
        AND e.Value=(SELECT MAX(r.Value) FROM App\Entity\Offer r WHERE r.auction=e.auction) 
        AND e.byUser=?1)
        AND a.endsAt<CURRENT_TIMESTAMP()
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)';

        
        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }

}

/*[PROPERTIES]

WHERE
Filtry:
szukaj wg nazwy
Opisu

All   - no ctimestamp
Aktywne - endsAt>ctimestamp
Zakonczone - endsAt<ctimespamt

Zaraz się skoncza 1h - endsAt> ctimestamp and endsAt<ctimestamp+1h

Od ceny do ceny - hghest x - hghest x
By użytkownik lista - auction.byUser = x 
WSZYSTKO TO WHERE

WSZYSTKO TO ORDER
Sort:
czasu zakon - endsAt 
Ceny - hghst
 nazwy - auction.title
Ilości ofert - count(offers)
Ilosci komentarzy - count (comments)
WSZYSTKO TO ORDER
*/

/*
dql and qb design
  public function dqlSoldAuctionsOfUser($user)
    {
        $dql = 'SELECT a, ('.$this->createQueryBuilder('ba')
        ->select('MAX(oa.Value)')
        ->from('App\Entity\Offer', 'oa')
        ->where('a.id = oa.auction')
        ->getDQL().') as hghst 
        FROM App\Entity\Auction a
        WHERE a.byUser=?1 
        AND a.endsAt<CURRENT_TIMESTAMP()';
        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }
*/

/* 2 posibiltes : auctions perspective
select * from auctions

right join (SELECT *
FROM offers ofUP
WHERE ofUP.value=(SELECT max(ofNE.value) FROM offers ofNE WHERE ofNE.auction_id=ofUP.auction_id)
AND ofUP.by_user_id=4) as supa 

on supa.auction_id=auctions.id


 OR  offers perspective



 SELECT auctions.*
FROM offers 

LEFT JOIN auctions ON offers.auction_id=auctions.id

where offers.by_user_id = 4 AND
offers.value=(SELECT max(ofNE.value) FROM offers ofNE WHERE ofNE.auction_id=offers.auction_id)
group BY auction_id*/