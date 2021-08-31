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
            ->setParameter('val', $user)
            ->getQuery()
            ->getResult()
        ;
    }
}
