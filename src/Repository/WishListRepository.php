<?php

namespace App\Repository;

use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wishlist>
 *
 * @method Wishlist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wishlist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wishlist[]    findAll()
 * @method Wishlist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WishListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    public function getWishlistIdByName(Wishlist $name): ?Wishlist
    {
        return $this->createQueryBuilder('wl')
            ->select('wl.idWishlist')
            ->andWhere('wl.namewishlist = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getWishlistByName(String $name): ?Wishlist
{
    return $this->createQueryBuilder('wl')
        ->andWhere('wl.namewishlist = :name')
        ->setParameter('name', $name)
        ->getQuery()
        ->getOneOrNullResult();
}

public function getWishlist(Wishlist $name): ?Wishlist
{
    return $this->createQueryBuilder('wl')
        ->andWhere('wl.namewishlist = :name')
        ->setParameter('name', $name)
        ->getQuery()
        ->getOneOrNullResult();
}

//    /**
//     * @return Wishlist[] Returns an array of Wishlist objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Wishlist
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
