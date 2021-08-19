<?php

namespace App\Repository;

use App\Entity\AuctionImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuctionImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuctionImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuctionImage[]    findAll()
 * @method AuctionImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuctionImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuctionImage::class);
    }

    // /**
    //  * @return AuctionImage[] Returns an array of AuctionImage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AuctionImage
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
