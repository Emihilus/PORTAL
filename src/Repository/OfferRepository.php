<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Offer;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Offer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offer[]    findAll()
 * @method Offer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    // /**
    //  * @return Offer[] Returns an array of Offer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Offer
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    private function processFilters($filtersJson, $queryBuilder)
    {
        if($filtersJson)
        {
            if(isset($filtersJson->f_search))
            {
                if($filtersJson->fo_search == 1)
                    $byWhat = 'a.title';
                else
                    $byWhat = 'a.description';

                $queryBuilder->andWhere($byWhat.' LIKE :sQuery')
                ->setParameter('sQuery', '%'.$filtersJson->f_search.'%');
            }

            if(isset($filtersJson->f_liveness))
            {
                switch($filtersJson->f_liveness)
                {
                    case 1:
                        $queryBuilder->andWhere('a.endsAt > :date')
                        ->setParameter('date', new \DateTime());
                        break;

                    case 2:
                        $queryBuilder->andWhere('a.endsAt < :date')
                        ->setParameter('date', new \DateTime());
                        break;

                    case 4:
                        $queryBuilder->andWhere('a.endsAt > :date')
                        ->setParameter('date', new \DateTime())
                        ->andWhere('a.endsAt < :dateN')
                        ->setParameter('dateN', new \DateTime('+1 day'));
                        break;

                }
            }

            if(isset($filtersJson->f_prices) && $filtersJson->f_prices > 0)
            {
                $queryBuilder->andHaving('hghst > :sval')
                ->setParameter('sval', $filtersJson->f_prices*100);
            }

            if(isset($filtersJson->f_pricee) && $filtersJson->f_pricee > 0)
            {
                $queryBuilder->andHaving('hghst < :enval')
                ->setParameter('enval', $filtersJson->f_pricee*100);
            }

            if(isset($filtersJson->f_byuser))
            {
                $queryBuilder->leftJoin('a.byUser', 'u')
                ->addSelect('u.username')
                ->andWhere('u.username = :byusr')
                ->setParameter('byusr', $filtersJson->f_byuser);
            }

            $orderity = 'ASC';
            if(isset($filtersJson->s_order))
            {
                switch($filtersJson->s_order)
                {
                    case 2:
                        $orderity = 'DESC';
                        break;
                }
            }

            if(isset($filtersJson->s_criteria))
            {
                switch($filtersJson->s_criteria)
                {
                    case 1:
                        $queryBuilder->orderBy('a.title', $orderity);
                        break;

                    case 2:
                        $queryBuilder->orderBy('a.createdAt', $orderity);
                        break;

                    case 3:
                        $queryBuilder->orderBy('a.endsAt', $orderity);
                        break;

                    case 4:
                        $queryBuilder->orderBy('hghst', $orderity);
                        break;

                        
                    // NESTED SOLUTION
                    case 5:
                        $queryBuilder->addSelect('(SELECT COUNT(ofa) 
                        FROM App\Entity\Offer ofa WHERE ofa.auction = a) as offerCount')
                        ->orderBy('offerCount', $orderity);
                        break;

                    // JOINED - NEEDS FULL GROUP BY OFF
                   /* case 5:
                        $queryBuilder->orderBy('a.title', $orderity)
                        ->leftJoin('a.offers', 'ofe')
                        ->addSelect('ofe.Value');
                        break;*/

                    // NESTED SOLUTION
                    case 6:
                        $queryBuilder->addSelect('(SELECT COUNT(ofa) 
                        FROM App\Entity\Comment ofa WHERE ofa.auction = a) as commentCount')
                        ->orderBy('commentCount', $orderity);
                        break;

                }
            }
        }
        return $queryBuilder;
    }


    public function qBuilderOfferOriented(?User $user, $filters = null)
    {
        $query = $this->createQueryBuilder('a')

        ->addSelect('(SELECT MAX(oa.Value) 
        FROM App\Entity\Offer oa
        WHERE a.id = oa.auction ) as hghst')

        ->from('App\Entity\Offer', 'o')

        ->leftJoin('App\Entity\Auction', 'a', Expr\Join::WITH, 'a = o.auction')
        ->addSelect('a')

        ->leftJoin('a.images', 'i')
        ->addSelect('i.filename')

        ->where('i.orderIndicator = 0 OR i.orderIndicator IS NULL')

        ->andWhere('o.byUser.username = :offerIssuer')
        ->setParameter('offerIssuer', $filters->oo_byuser)
        ->andWhere('o.Value = (SELECT MAX(f.Value) FROM App\Entity\Offer f WHERE f.auction=o.auction)')
        ;
        if($user)
        {
            $query->leftJoin('a.likedByUsers', 'l', Expr\Join::WITH, 'l.id = :user')
            ->setParameter('user', $user->getId())
            ->addSelect('l');
        }

        $query = $this->processFilters($filters, $query);

        return $query->getQuery()->getResult();
    }

    
}
