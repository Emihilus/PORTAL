<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findOneWithAuctions($user)
    {
        //$em = $this->getEntityManager();
       return $this->createQueryBuilder('u')
            ->leftJoin('u.Auctions', 'a')
            ->addSelect('a')
            ->andWhere('a.byUser = :val')
            ->setParameter('val', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        //return $em->createQuery('SELECT a, i FROM auction a LEFT JOIN a.auction_image i')->getResult();
    }


    public function queryUserprofileInfoCollection($user)
    {
    //$expr = $this->_em->getExpressionBuilder();
       $exxp =  $this->createQueryBuilder('u')

            ->addSelect('('.$this->createQueryBuilder('z')

            ->select('COUNT(DISTINCT a.id)')
            ->from('App\Entity\Auction', 'a')
            ->where('a.byUser =  :val')
            ->setParameter('val', $user)
            ->getDQL(). ') as Auctions_Issued')

            ->addSelect('('.$this->createQueryBuilder('x')
            ->select('COUNT(DISTINCT o.id)')
            ->from('App\Entity\Offer', 'o')
            ->where('o.byUser =  :val')
            ->setParameter('val', $user)
            ->getDQL(). ') as All_Offers')

            ->addSelect('('.$this->createQueryBuilder('y')
            ->select('COUNT(DISTINCT f.auction)')
            ->from('App\Entity\Offer', 'f')
            ->where('f.byUser =  :val')
            ->setParameter('val', $user)
            ->getDQL(). ') as  Participating_In')


            ->addSelect('('.$this->createQueryBuilder('g')
            ->select('COUNT(DISTINCT e.id)')
            ->from('App\Entity\Offer', 'e')
            ->where('e.Value=('.$this->createQueryBuilder('b')
                ->select('MAX(r.Value)')
                ->from('App\Entity\Offer', 'r')
                ->where('r.auction=e.auction')
                ->getDQL().')')

            ->andWhere('e.byUser =  :val')
            ->setParameter('val', $user)
            ->getDQL(). ') as Leading_In')

            
            ->where('u = :val')
            ->setParameter('val', $user);
            $DQL = $exxp->getDQL();
            $exxp = $exxp->getQuery()
            ->getResult();
        return [$exxp, $DQL];
    }


    public function dqlUserInfoCollection($user)
    {
        $dql = "SELECT u, 

        (SELECT COUNT(DISTINCT a.id) FROM App\Entity\User z, App\Entity\Auction a WHERE a.byUser =  ?1) as Auctions_Issued, (SELECT COUNT(DISTINCT o.id) FROM App\Entity\User x, App\Entity\Offer o WHERE o.byUser = ?1 
        AND a.isDeleted = false) as All_Offers,

        (SELECT COUNT(DISTINCT f.auction) FROM  App\Entity\Offer f 
        LEFT JOIN App\Entity\Auction asss WITH asss=f.auction
        WHERE asss.endsAt>CURRENT_TIMESTAMP()
        AND f.byUser =  ?1 
        AND asss.isDeleted = false) as  Participating_In,

        (SELECT COUNT(DISTINCT zf.auction) FROM  App\Entity\Offer zf 
        LEFT JOIN App\Entity\Auction zasss WITH zasss=zf.auction
        WHERE zasss.endsAt<CURRENT_TIMESTAMP()
        AND zf.byUser =  ?1 
        AND zasss.isDeleted = false) as  Participated_In,


        (SELECT COUNT(DISTINCT ce.id) FROM  App\Entity\Offer ce 
        LEFT JOIN App\Entity\Auction cc WITH ce.auction=cc
        WHERE ce.Value=(SELECT MAX(cr.Value) FROM App\Entity\Offer cr  WHERE cr.auction=ce.auction) 
        AND cc.endsAt>CURRENT_TIMESTAMP()
        AND ce.byUser =  ?1 
        AND cc.isDeleted = false) as Leading_In,

        (SELECT COUNT(DISTINCT ee.id) FROM  App\Entity\Offer ee 
        LEFT JOIN App\Entity\Auction ec WITH ee.auction=ec
        WHERE ee.Value=(SELECT MAX(er.Value) FROM App\Entity\Offer er  WHERE er.auction=ee.auction) 
        AND ec.endsAt<CURRENT_TIMESTAMP()
        AND ee.byUser =  ?1 
        AND a.isDeleted = false) as Won,

        (SELECT SUM(DISTINCT fe.Value) FROM  App\Entity\Offer fe 
        LEFT JOIN App\Entity\Auction fc WITH fe.auction=fc
        WHERE fe.Value=(SELECT MAX(fr.Value) FROM App\Entity\Offer fr  WHERE fr.auction=fe.auction) 
        AND fc.endsAt<CURRENT_TIMESTAMP()
        AND fe.byUser =  ?1 
        AND a.isDeleted = false) as Sum_Won,


        (SELECT SUM(s.Value) FROM App\Entity\Offer s 
        LEFT JOIN App\Entity\Auction c WITH s.auction=c
        WHERE c.byUser=?1
        AND s.Value=(SELECT MAX(d.Value) FROM App\Entity\Offer d WHERE d.auction=s.auction) 
        AND a.isDeleted = false
        ) AS Sum_Sold_Selling,

        (SELECT SUM(sa.Value) FROM App\Entity\Offer sa
        LEFT JOIN App\Entity\Auction ca WITH sa.auction=ca
        WHERE ca.byUser=?1
        AND sa.Value=(SELECT MAX(da.Value) FROM App\Entity\Offer da WHERE da.auction=sa.auction)
        AND ca.endsAt<CURRENT_TIMESTAMP() 
        AND a.isDeleted = false
        ) AS Sum_Sold,

        (SELECT SUM(sb.Value) FROM App\Entity\Offer sb
        LEFT JOIN App\Entity\Auction cb WITH sb.auction=cb
        WHERE cb.byUser=?1
        AND sb.Value=(SELECT MAX(db.Value) FROM App\Entity\Offer db WHERE db.auction=sb.auction)
        AND cb.endsAt>CURRENT_TIMESTAMP() 
        AND a.isDeleted = false
        ) AS Sum_Selling,

        (SELECT COUNT(xb.Value) FROM App\Entity\Offer xb
        LEFT JOIN App\Entity\Auction zb WITH xb.auction=zb
        WHERE zb.byUser=?1
        AND xb.Value=(SELECT MAX(xxb.Value) FROM App\Entity\Offer xxb WHERE xxb.auction=xb.auction)
        AND zb.endsAt>CURRENT_TIMESTAMP() 
        AND a.isDeleted = false
        ) AS Selling,

        (SELECT COUNT(axb.Value) FROM App\Entity\Offer axb
        LEFT JOIN App\Entity\Auction azb WITH axb.auction=azb
        WHERE azb.byUser=?1
        AND axb.Value=(SELECT MAX(axxb.Value) FROM App\Entity\Offer axxb WHERE axxb.auction=axb.auction)
        AND azb.endsAt<CURRENT_TIMESTAMP() 
        AND a.isDeleted = false
        ) AS Sold,

        (SELECT SUM(sd.Value) FROM App\Entity\Offer sd
        LEFT JOIN App\Entity\Auction cd WITH sd.auction=cd
        WHERE sd.byUser=?1
        AND sd.Value=(SELECT MAX(dd.Value) FROM App\Entity\Offer dd WHERE dd.auction=sd.auction)
        AND cd.endsAt>CURRENT_TIMESTAMP() 
        AND a.isDeleted = false
        ) AS Sum_In_Leading,


        (SELECT COUNT(DISTINCT aba) FROM App\Entity\Offer abo 
        LEFT JOIN App\Entity\Auction aba WITH aba=abo.auction 
        WHERE abo.byUser=?1 
        AND abo.Value<(SELECT MAX(abf.Value) FROM App\Entity\Offer abf WHERE abf.auction=abo.auction)
        AND 0=(SELECT COUNT(abb) FROM App\Entity\Offer abe 
        LEFT JOIN App\Entity\Auction abb WITH abb=abe.auction 
        WHERE abb=aba
        AND abe.Value=(SELECT MAX(abr.Value) FROM App\Entity\Offer abr WHERE abr.auction=abe.auction) 
        AND abe.byUser=?1)

        AND aba.endsAt>CURRENT_TIMESTAMP() 
        AND a.isDeleted = false) AS Participating_In_Not_Leading,



        (SELECT COUNT(DISTINCT zba) FROM App\Entity\Offer zbo 
        LEFT JOIN App\Entity\Auction zba WITH zba=zbo.auction 
        WHERE zbo.byUser=?1 
        AND zbo.Value<(SELECT MAX(zbf.Value) FROM App\Entity\Offer zbf WHERE zbf.auction=zbo.auction)
        AND 0=(SELECT COUNT(zbb) FROM App\Entity\Offer zbe 
        LEFT JOIN App\Entity\Auction zbb WITH zbb=zbe.auction 
        WHERE zbb=zba
        AND zbe.Value=(SELECT MAX(zbr.Value) FROM App\Entity\Offer zbr WHERE zbr.auction=zbe.auction) 
        AND zbe.byUser=?1)

        AND zba.endsAt<CURRENT_TIMESTAMP() 
        AND a.isDeleted = false) AS Participated_In_Not_Leading


        FROM App\Entity\User u WHERE u = ?1";


        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }

}