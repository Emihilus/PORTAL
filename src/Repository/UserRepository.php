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

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
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


    /*SELECT u0_.id AS id_0, u0_.username AS username_1, u0_.roles AS roles_2, u0_.password AS password_3, u0_.email AS email_4, u0_.is_verified AS is_verified_5, u0_.is_banned AS is_banned_6, (SELECT COUNT(DISTINCT a1_.id) AS sclr_8 FROM users u2_, auctions a1_ WHERE a1_.by_user_id = ?) AS sclr_7, (SELECT COUNT(DISTINCT o3_.id) AS sclr_10 FROM users u4_, offers o3_ WHERE o3_.by_user_id = ?) AS sclr_9, (SELECT COUNT(DISTINCT o5_.auction_id) AS sclr_12 FROM users u6_, offers o5_ WHERE o5_.by_user_id = ?) AS sclr_11, (SELECT COUNT(DISTINCT o7_.id) AS sclr_14 FROM users u8_, offers o7_ WHERE o7_.value = (SELECT MAX(o9_.value) AS sclr_15 FROM users u10_, offers o9_ WHERE o9_.auction_id = o7_.auction_id) AND o7_.by_user_id = ?) AS sclr_13 FROM users u0_ WHERE u0_.id = ? 
    
    NEED TO ELIMINATE USERS SELECT FROM EACH SELECE, CAUSED BY CREATEQUERYBUILDER 'LITERAL'*/

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


    public function dqlcollection($user)
    {
        $dql = "SELECT u, 

        (SELECT COUNT(DISTINCT a.id) FROM App\Entity\User z, App\Entity\Auction a WHERE a.byUser =  ?1) as Auctions_Issued, (SELECT COUNT(DISTINCT o.id) FROM App\Entity\User x, App\Entity\Offer o WHERE o.byUser =  ?1) as All_Offers,

        (SELECT COUNT(DISTINCT f.auction) FROM  App\Entity\Offer f 
        LEFT JOIN App\Entity\Auction asss WITH asss=f.auction
        WHERE asss.endsAt>CURRENT_TIMESTAMP()
        AND f.byUser =  ?1) as  Participating_In,

        (SELECT COUNT(DISTINCT zf.auction) FROM  App\Entity\Offer zf 
        LEFT JOIN App\Entity\Auction zasss WITH zasss=zf.auction
        WHERE zasss.endsAt<CURRENT_TIMESTAMP()
        AND zf.byUser =  ?1) as  Participated_In,


        (SELECT COUNT(DISTINCT ce.id) FROM  App\Entity\Offer ce 
        LEFT JOIN App\Entity\Auction cc WITH ce.auction=cc
        WHERE ce.Value=(SELECT MAX(cr.Value) FROM App\Entity\Offer cr  WHERE cr.auction=ce.auction) 
        AND cc.endsAt>CURRENT_TIMESTAMP()
        AND ce.byUser =  ?1) as Leading_In,

        (SELECT COUNT(DISTINCT ee.id) FROM  App\Entity\Offer ee 
        LEFT JOIN App\Entity\Auction ec WITH ee.auction=ec
        WHERE ee.Value=(SELECT MAX(er.Value) FROM App\Entity\Offer er  WHERE er.auction=ee.auction) 
        AND ec.endsAt<CURRENT_TIMESTAMP()
        AND ee.byUser =  ?1) as Won,

        (SELECT SUM(DISTINCT fe.Value) FROM  App\Entity\Offer fe 
        LEFT JOIN App\Entity\Auction fc WITH fe.auction=fc
        WHERE fe.Value=(SELECT MAX(fr.Value) FROM App\Entity\Offer fr  WHERE fr.auction=fe.auction) 
        AND fc.endsAt<CURRENT_TIMESTAMP()
        AND fe.byUser =  ?1) as Sum_Won,


        (SELECT SUM(s.Value) FROM App\Entity\Offer s 
        LEFT JOIN App\Entity\Auction c WITH s.auction=c
        WHERE c.byUser=?1
        AND s.Value=(SELECT MAX(d.Value) FROM App\Entity\Offer d WHERE d.auction=s.auction)
        ) AS Sum_Sold_Selling,

        (SELECT SUM(sa.Value) FROM App\Entity\Offer sa
        LEFT JOIN App\Entity\Auction ca WITH sa.auction=ca
        WHERE ca.byUser=?1
        AND sa.Value=(SELECT MAX(da.Value) FROM App\Entity\Offer da WHERE da.auction=sa.auction)
        AND ca.endsAt<CURRENT_TIMESTAMP()
        ) AS Sum_Sold,

        (SELECT SUM(sb.Value) FROM App\Entity\Offer sb
        LEFT JOIN App\Entity\Auction cb WITH sb.auction=cb
        WHERE cb.byUser=?1
        AND sb.Value=(SELECT MAX(db.Value) FROM App\Entity\Offer db WHERE db.auction=sb.auction)
        AND cb.endsAt>CURRENT_TIMESTAMP()
        ) AS Sum_Selling,

        (SELECT COUNT(xb.Value) FROM App\Entity\Offer xb
        LEFT JOIN App\Entity\Auction zb WITH xb.auction=zb
        WHERE zb.byUser=?1
        AND xb.Value=(SELECT MAX(xxb.Value) FROM App\Entity\Offer xxb WHERE xxb.auction=xb.auction)
        AND zb.endsAt>CURRENT_TIMESTAMP()
        ) AS Selling,

        (SELECT COUNT(axb.Value) FROM App\Entity\Offer axb
        LEFT JOIN App\Entity\Auction azb WITH axb.auction=azb
        WHERE azb.byUser=?1
        AND axb.Value=(SELECT MAX(axxb.Value) FROM App\Entity\Offer axxb WHERE axxb.auction=axb.auction)
        AND azb.endsAt<CURRENT_TIMESTAMP()
        ) AS Sold,

        (SELECT SUM(sd.Value) FROM App\Entity\Offer sd
        LEFT JOIN App\Entity\Auction cd WITH sd.auction=cd
        WHERE sd.byUser=?1
        AND sd.Value=(SELECT MAX(dd.Value) FROM App\Entity\Offer dd WHERE dd.auction=sd.auction)
        AND cd.endsAt>CURRENT_TIMESTAMP()
        ) AS Sum_In_Leading,


        (SELECT COUNT(aba) FROM App\Entity\Offer abo 
        LEFT JOIN App\Entity\Auction aba WITH aba=abo.auction 
        WHERE abo.byUser=?1 
        AND abo.Value<(SELECT MAX(abf.Value) FROM App\Entity\Offer abf WHERE abf.auction=abo.auction)
        AND 0=(SELECT COUNT(abb) FROM App\Entity\Offer abe 
        LEFT JOIN App\Entity\Auction abb WITH abb=abe.auction 
        WHERE abb=aba
        AND abe.Value=(SELECT MAX(abr.Value) FROM App\Entity\Offer abr WHERE abr.auction=abe.auction) 
        AND abe.byUser=?1)

        AND aba.endsAt>CURRENT_TIMESTAMP()) AS Partipating_Not_Leading




        (SELECT COUNT(zba) FROM App\Entity\Offer zbo 
        LEFT JOIN App\Entity\Auction zba WITH zba=zbo.auction 
        WHERE zbo.byUser=?1 
        AND zbo.Value<(SELECT MAX(zbf.Value) FROM App\Entity\Offer abf WHERE abf.auction=abo.auction)
        AND 0=(SELECT COUNT(abb) FROM App\Entity\Offer abe 
        LEFT JOIN App\Entity\Auction abb WITH abb=abe.auction 
        WHERE abb=aba
        AND abe.Value=(SELECT MAX(abr.Value) FROM App\Entity\Offer abr WHERE abr.auction=abe.auction) 
        AND abe.byUser=?1)

        AND aba.endsAt>CURRENT_TIMESTAMP()) AS Participated_Not_Leading




          
        FROM App\Entity\User u WHERE u = ?1";


        $query = $this->_em->createQuery($dql)
        ->setParameter(1, $user);
        return $query->getResult();
    }

}


/*SELECT SUM(offers.value) FROM auctions
        LEFT JOIN offers ON offers.auction_id=auctions.id
        AND auctions.by_user_id=1
        GROUP BY auctions.id*/


        /*(SELECT COUNT(DISTINCT e.id) FROM App\Entity\User g, App\Entity\Offer e WHERE e.Value=(SELECT MAX(r.Value) FROM App\Entity\User b, App\Entity\Offer r WHERE r.auction=e.auction) AND e.byUser =  ?1) as Leading_In_All,*/