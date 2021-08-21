<?php

namespace App\Repository;

use App\Entity\Auction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
    
   /* public function findAllWithFirstAuctionImageIncorrect()
    {
       return $this->createQueryBuilder('a')
            ->leftJoin('a.images', 'i')
            ->addSelect('MAX(i.orderIndicator) AS ds, i.filename')
            ->groupBy('.id, i.filename')
            ->getQuery()
            ->getResult()
        ;
    }*/


    /* mysql correct query
    SELECT AUCTIONS WITH HIGHEST OFFER ENTITY

        select a.*, b.* 
        from auctions a 
        left join offers b
            on b.auction_id =                        
            ( select bb.auction_id                
                from offers bb 
                where a.id = bb.auction_id
                ORDER BY bb.value
                limit 1
            )
       */

    /* 
       SELECT AUTCIONS WITH HIGHEST OFFER VALUE WITHOUT ENTITY
       select
        auctions.id,
        (
            select max(offers.value)
            from offers
            where auctions.id = offers.auction_id
        ) as hgh
        from
            auctions
    */

    public function findAllWithHighestOfferValue()
    {
        //$expr = $this->getEntityManager()->getExpressionBuilder();
       return $this->createQueryBuilder('a')
            ->addSelect('(SELECT MAX(offers.value) from offers where auctions.id = offers.auction_id) as hgh')
            ->getQuery()
            ->getResult()
        ;
    }
}
