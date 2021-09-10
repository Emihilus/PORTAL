<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Auction;
use DateTime;
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
    private const hghstSelect = '(SELECT MAX(oa.Value) FROM
    App\Entity\Offer oa
    WHERE a.id = oa.auction
    ) as hghst';

    private const hghstOfferOwnerSelect = '(SELECT zu.username 
    FROM App\Entity\Offer zo
    LEFT JOIN App\Entity\User zu WITH zo.byUser=zu
    WHERE zo.auction=a
    AND zo.Value=(SELECT MAX(fo.Value) FROM App\Entity\Offer fo WHERE fo.auction=zo.auction)
    ) as hghstOfferOwner';

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
            ->andWhere('a.isDeleted = false')

            ->leftJoin('c.likedBy', 'lb')
            ->leftJoin('c.dislikedBy', 'dlb')
            ->addSelect('lb, dlb')

            ->andWhere('c.isDeleted = false')

            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByIdWithAuctionImagesAndOffersAndCommentsRESTRICT($auctionId, $user): ?Auction
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.images', 'i')
            ->addSelect('i')
            ->leftJoin('a.comments', 'c')
            ->addSelect('c')
            ->leftJoin('a.offers', 'o')
            ->addSelect('o')
            ->Where('a.id = :val')
            ->setParameter('val', $auctionId)
            ->orderBy('o.Value', 'DESC')
            ->andWhere('a.isDeleted = false')

            ->andWhere('a.endsAt < :now')
            ->setParameter('now', new DateTime())

            ->leftJoin('c.likedBy', 'lb')
            ->leftJoin('c.dislikedBy', 'dlb')
            ->addSelect('lb, dlb')

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

    public function qBuilderAllCList(?User $user)
    {
        $query = $this->createQueryBuilder('a');
        $query = $query
        ->addSelect(self::hghstSelect)
        ->addSelect(self::hghstOfferOwnerSelect)
        ->addSelect('(SELECT COUNT(c.id) FROM App\Entity\Comment c WHERE a=c.auction AND c.value > -2) as coment')

        ->leftJoin('a.images', 'i')
        ->addSelect('i.filename')

        ->where('i.orderIndicator = 0 OR i.orderIndicator IS NULL')
        ->andWhere('a.isDeleted = false')
    
        ->andWhere('a.endsAt < :now')
        ->setParameter('now', new DateTime())

        ->having('hghstOfferOwner = :usr')
        ->andHaving('coment = 0')
        ->setParameter('usr', $user->getUserIdentifier())
        ;
        

        

        return $query->getQuery()->getResult();
    }


///////////////////////// QUERY BUILDER DESIGN  <4   ///////////////////////////////////
    public function qBuilderListAllAuctions(?User $user, $filters = null)
    {
        $query = $this->createQueryBuilder('a')

        ->addSelect(self::hghstSelect)
        ->addSelect(self::hghstOfferOwnerSelect)

        ->leftJoin('a.images', 'i')
        ->addSelect('i.filename')

        ->where('i.orderIndicator = 0 OR i.orderIndicator IS NULL')
        ->andWhere('a.isDeleted = false');

        if($user)
        {
            $query->leftJoin('a.likedByUsers', 'l', Expr\Join::WITH, 'l.id = :user')
            ->setParameter('user', $user->getId())
            ->addSelect('l');
        }

        $query = $this->processFilters($user, $filters, $query);

        return $query->getQuery()->getResult();
    }

    


/////////////////// DQL DESIGN >4 /////////////////////////////
    public function dqlLeadingAuctionsOfUser(?User $currentUser, $filters = null) // LEADING OR WON
    {
        $lSelect = '';
        $lJoin = '';
        if($currentUser)
        {
            $lSelect = 'l,';
            $lJoin = 'LEFT JOIN a.likedByUsers l WITH l.id = ?10 ';
        }

        $fil = $this->processFiltersDQL($filters);

        $dql = "SELECT a, $lSelect i.filename, 
        ".self::hghstSelect.",
        ".self::hghstOfferOwnerSelect."
        {$fil['selectString']}

        FROM App\Entity\Offer o 

        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i
        $lJoin
        {$fil['leftJoinString']}

        WHERE o.byUser=?1 
        AND o.Value=(SELECT MAX(f.Value) FROM App\Entity\Offer f WHERE f.auction=o.auction)
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)
        AND a.isDeleted = false 
        {$fil['whereString']} 
        {$fil['havingString']}
        {$fil['orderByString']}";
        
        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $this->_em->getRepository(User::class)->findOneBy(['username' => $filters->oo_byuser])->getId());

        if($currentUser)
            $query = $query->setParameter(10, $currentUser);

        foreach($fil['paramsArray'] as $params)
        {
            $query = $query->setParameter($params[0],$params[1]);
        }

        return $query->getResult();
    }
    
    public function dqlParticipatingAuctionsOfUser(?User $currentUser, $filters = null) // OR PARTICIPATED
    {
        $lSelect = '';
        $lJoin = '';
        if($currentUser)
        {
            $lSelect = 'l,';
            $lJoin = 'LEFT JOIN a.likedByUsers l WITH l.id = ?10 ';
        }

        $fil = $this->processFiltersDQL($filters);

        $dql = "SELECT a, $lSelect i.filename, 
        ".self::hghstSelect.",
        ".self::hghstOfferOwnerSelect."
        {$fil['selectString']}

        FROM App\Entity\Offer o 

        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i
        $lJoin
        {$fil['leftJoinString']}

        WHERE o.byUser=?1
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)
        AND a.isDeleted = false 
        {$fil['whereString']} 
        {$fil['havingString']}
        {$fil['orderByString']}";
        
        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $this->_em->getRepository(User::class)->findOneBy(['username' => $filters->oo_byuser])->getId());

        if($currentUser)
            $query = $query->setParameter(10, $currentUser);

        foreach($fil['paramsArray'] as $params)
        {
            $query = $query->setParameter($params[0],$params[1]);
        }

        return $query->getResult();
    }

    public function dqlParticipatingNotLeadingAuctionsOfUser(?User $currentUser, $filters = null) // OR PARTICIPATED
    {
        $lSelect = '';
        $lJoin = '';
        if($currentUser)
        {
            $lSelect = 'l,';
            $lJoin = 'LEFT JOIN a.likedByUsers l WITH l.id = ?10 ';
        }

        $fil = $this->processFiltersDQL($filters);

        $dql = "SELECT a, $lSelect i.filename, 
        ".self::hghstSelect.",
        ".self::hghstOfferOwnerSelect."
        {$fil['selectString']}

        FROM App\Entity\Offer o 

        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i
        $lJoin
        {$fil['leftJoinString']}

        WHERE o.byUser=?1 
        AND o.Value<(SELECT MAX(f.Value) FROM App\Entity\Offer f WHERE f.auction=o.auction)
        AND 0=(SELECT COUNT(b) FROM App\Entity\Offer e 
        LEFT JOIN App\Entity\Auction b WITH b=e.auction 
        WHERE b=a
        AND e.Value=(SELECT MAX(r.Value) FROM App\Entity\Offer r WHERE r.auction=e.auction) 
        AND e.byUser=?1)
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)
        AND a.isDeleted = false 
        {$fil['whereString']} 
        {$fil['havingString']}
        {$fil['orderByString']}";

        
        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $this->_em->getRepository(User::class)->findOneBy(['username' => $filters->oo_byuser])->getId());

        if($currentUser)
            $query = $query->setParameter(10, $currentUser);

        foreach($fil['paramsArray'] as $params)
        {
            $query = $query->setParameter($params[0],$params[1]);
        }

        return $query->getResult();
    }


///////////////////// FILTERS QUERYBUILDER DESIGN /////////////////////////////////

    private function processFilters(?User $user, $filtersJson, $queryBuilder)
    {
        if($filtersJson)
        {
            if(isset($filtersJson->f_favor) && $filtersJson->f_favor == 'true')
            {
                $queryBuilder->andWhere('l = :usr')
                ->setParameter('usr', $user);      
            }

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
                        $queryBuilder->addSelect('(SELECT COUNT(ofa.value) 
                        FROM App\Entity\Comment ofa WHERE ofa.auction = a) as commentCount')
                        ->orderBy('commentCount', $orderity);
                        break;

                }
            }
        }
        return $queryBuilder;
    }

///////////////////// FILTERS DQL DESIGN /////////////////////////////////

    private function processFiltersDQL($filtersJson)
    {
        $selectString = '';
        $leftJoinString = '';
        $whereString = '';
        $havingString = '';
        $orderBy = '';
        $paramsArray = [];
        if($filtersJson)
        {
            if(isset($filtersJson->f_favor) && $filtersJson->f_favor == 'true')
            {
                $whereString.= ' AND l = ?10 ';
            }

            if(isset($filtersJson->f_search))
            {
                if($filtersJson->fo_search == 1)
                    $byWhat = 'a.title';
                else
                    $byWhat = 'a.description';

                $whereString .= " AND $byWhat LIKE ?2 ";
                array_push($paramsArray,[2, '%'.$filtersJson->f_search.'%']);
            }

            if(isset($filtersJson->f_liveness))
            {
                switch($filtersJson->f_liveness)
                {
                    case 1:
                        $whereString .= " AND a.endsAt > ?3 ";
                        array_push($paramsArray,[3, new \DateTime()]);
                        break;

                    case 2:
                        $whereString .= " AND a.endsAt < ?3 ";
                        array_push($paramsArray,[3, new \DateTime()]);
                        break;

                    case 4:
                        $whereString .= " AND a.endsAt > ?3 ";
                        array_push($paramsArray,[3, new \DateTime()]);
                        $whereString .= " AND a.endsAt < ?4 ";
                        array_push($paramsArray,[4, new \DateTime('+1 day')]);
                        break;

                }
            }

            if(isset($filtersJson->f_prices) && $filtersJson->f_prices > 0)
            {
                $havingString = " HAVING hghst > ?5 ";
                array_push($paramsArray,[5, $filtersJson->f_prices*100]);
            }

            if(isset($filtersJson->f_pricee) && $filtersJson->f_pricee > 0)
            {
                if($havingString == "")
                {
                    $havingString = " HAVING hghst < ?6 ";
                    array_push($paramsArray,[6, $filtersJson->f_pricee*100]);
                }
                else
                {
                    $havingString .= " AND hghst < ?6 ";
                    array_push($paramsArray,[6, $filtersJson->f_pricee*100]);
                }
            }

            if(isset($filtersJson->f_byuser))
            {
                dump('GIVEN');
                $selectString = ' ,u.username ';
                $leftJoinString = ' LEFT JOIN a.byUser u ';
                $whereString .= ' AND u.username = ?7 ';
                array_push($paramsArray,[7, $filtersJson->f_byuser]);
            }



            // //////////////////// ORDERITY    //////////////////
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
                        $orderBy = " ORDER BY a.title $orderity ";
                        break;

                    case 2:
                        $orderBy = " ORDER BY a.createdAt $orderity ";
                        break;

                    case 3:
                        $orderBy = " ORDER BY a.endsAt $orderity ";
                        break;

                    case 4:
                        $orderBy = " ORDER BY hghst $orderity ";
                        break;

                        
                    // NESTED SOLUTION
                    case 5:
                        $selectString .= " ,(SELECT COUNT(ofa) 
                        FROM App\Entity\Offer ofa WHERE ofa.auction = a) as offerCount ";
                        $orderBy = " ORDER BY offerCount $orderity ";
                        break;

                    // JOINED - NEEDS FULL GROUP BY OFF
                   /* case 5:
                        $queryBuilder->orderBy('a.title', $orderity)
                        ->leftJoin('a.offers', 'ofe')
                        ->addSelect('ofe.Value');
                        break;*/

                    // NESTED SOLUTION
                    case 6:
                        $selectString .= " ,(SELECT COUNT(ofa.value) 
                        FROM App\Entity\Comment ofa WHERE ofa.auction = a) as commentCount";
                        $orderBy = " ORDER BY comment Count $orderity ";
                        break;

                }
            }

        }

        $assocObject['selectString'] = $selectString;
        $assocObject['leftJoinString'] = $leftJoinString;
        $assocObject['whereString'] = $whereString;
        $assocObject['havingString'] = $havingString;
        $assocObject['orderByString'] = $orderBy;
        $assocObject['paramsArray'] = $paramsArray;
        return $assocObject;
    }

}



   /* public function dqlWonAuctionsOfUser($filters = null)
    {
        $fil = $this->processFiltersDQL($filters);

        $dql = "SELECT a, i.filename, 
        
        (SELECT MAX(oa.Value) FROM
        App\Entity\Offer oa
        WHERE  a.id = oa.auction
        ) as hghst 
        {$fil['selectString']}

        FROM App\Entity\Offer o 


        LEFT JOIN App\Entity\Auction a WITH a=o.auction 
        LEFT JOIN a.images i
        {$fil['leftJoinString']}


        WHERE o.byUser=?1 
        AND o.Value=(SELECT MAX(f.Value) FROM App\Entity\Offer f WHERE f.auction=o.auction)
        AND (i.orderIndicator=0 OR i.orderIndicator IS NULL)
        {$fil['whereString']} 
        {$fil['havingString']}
        {$fil['orderByString']}";

        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $this->_em->getRepository(User::class)->findOneBy(['username' => $filters->oo_byuser])->getId());

        foreach($fil['paramsArray'] as $params)
        {
            $query = $query->setParameter($params[0],$params[1]);
        }

        return $query->getResult();
    }*/

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

/*public function findAllWithFirstImageAndHighestOfferWithOwner(?User $user, $filters = null)
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

        $query = $this->processFilters($filters, $query);

        return $query->getQuery()->getResult();
    }*/

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

    /*public function qBuilderAuctionsOfSpecificUser(?User $user, $filters = null)
    {
        $query = $this->createQueryBuilder('a')

        ->addSelect('(SELECT MAX(o.Value) 
        FROM App\Entity\Offer o
        WHERE a.id = o.auction ) as hghst')

        ->leftJoin('a.images', 'i')
        ->addSelect('i.filename')
        ->leftJoin('a.byUser', 'u')
        ->addSelect('u.username')

        ->where('i.orderIndicator = 0')
        ->orWhere('i.orderIndicator IS NULL');

        // IMPERFECT
        if($user)
        {
            $query->leftJoin('a.likedByUsers', 'l', Expr\Join::WITH, 'l.id = :user')
            ->setParameter('user', $user->getId())
            ->addSelect('l');
        }


        $query = $this->processFilters($filters, $query);

        return $query->getQuery()->getResult();
    }
    
        public function qBuilderOfferOriented(?User $user, $filters = null)
    {
        $query = $this->createQueryBuilder(null)

        ->select('(SELECT MAX(oa.Value) 
        FROM App\Entity\Offer oa
        WHERE a.id = oa.auction ) as hghst')

        ->from('App\Entity\Offer', 'o')

        ->leftJoin('App\Entity\Auction', 'a', Expr\Join::WITH, 'a = o.auction')
        ->addSelect('a')

        ->leftJoin('a.images', 'i')
        ->addSelect('i.filename')

        ->where('i.orderIndicator = 0 OR i.orderIndicator IS NULL')

        ->andWhere('o.byUser = :offerIssuer')
        ->setParameter('offerIssuer', $this->_em->getRepository(User::class)->findOneBy(['username' => $filters->oo_byuser])->getId())
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
    }*/

    /* public function dqlCurrentAuctionsOfUser($user)
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
    }*/