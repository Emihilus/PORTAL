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

/*SELECT a0_.id AS id_0, a0_.title AS title_1, a0_.created_at AS created_at_2, a0_.ends_at AS ends_at_3, a0_.description AS description_4, (SELECT MAX(o1_.value) AS sclr_6 FROM auctions a2_, offers o1_ WHERE a0_.id = o1_.auction_id) AS sclr_5, a3_.filename AS filename_7, u4_.username AS username_8, a0_.by_user_id AS by_user_id_9, ttta.cresult FROM auctions a0_, (SELECT user_auction.user_id as cresult FROM user_auction, auctions WHERE user_auction.auction_id=a0_.id AND user_auction.user_id=1) ttta LEFT JOIN auction_images a3_ ON a0_.id = a3_.auction_id LEFT JOIN users u4_ ON a0_.by_user_id = u4_.id WHERE a3_.order_indicator = 0 OR a3_.order_indicator IS NULL*/
    public function findAllWithFirstImageAndHighestOfferWithOwner(?User $user)
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