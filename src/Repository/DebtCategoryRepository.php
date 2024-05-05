<?php

namespace App\Repository;

use App\Entity\Debtcategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Debtcategory>
 *
 * @method Debtcategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Debtcategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Debtcategory[]    findAll()
 * @method Debtcategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Debtcategory::class);
    }

//    /**
//     * @return Debtcategory[] Returns an array of Debtcategory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Debtcategory
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
