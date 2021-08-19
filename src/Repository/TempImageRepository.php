<?php

namespace App\Repository;

use App\Entity\TempImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TempImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method TempImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method TempImage[]    findAll()
 * @method TempImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TempImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TempImage::class);
    }

    // /**
    //  * @return TempImage[] Returns an array of TempImage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TempImage
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
