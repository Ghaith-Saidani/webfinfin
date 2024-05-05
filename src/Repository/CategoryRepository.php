<?php

namespace App\Repository;


use App\Entity\Category;
use App\Entity\Subcategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function getTotalExpensesByCategoryId(int $idcategory): array
{
    $entityManager = $this->getEntityManager();

   
        // Create a ResultSetMappingBuilder
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('mt_dépensé', 'mtdepense');

        $nativeQuery = $entityManager->createNativeQuery('
            SELECT s.name, s.mt_dépensé
            FROM subcategory s
            WHERE s.idCategory = :idcategory
        ', $rsm);

        $nativeQuery->setParameter('idcategory', $idcategory);

        $result = $nativeQuery->getResult();

        return $result;
    
}
   
    
    public function getSubCategoriesExpensesAndBudgetLimit(int $idcategory): array
{
    $entityManager = $this->getEntityManager();

   
        // Create a ResultSetMappingBuilder
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addScalarResult('name', 'subCategoryName');
        $rsm->addScalarResult('mt_dépensé', 'subCategoryExpense');
        $rsm->addScalarResult('mt_assigné', 'categoryBudgetLimit');

        $nativeQuery = $entityManager->createNativeQuery('
            SELECT s.name, s.mt_dépensé, s.mt_assigné
            FROM subcategory s
            JOIN category c ON s.idCategory = c.idCategory
            WHERE c.idCategory = :idcategory
        ', $rsm);

        $nativeQuery->setParameter('idcategory', $idcategory);

        $result = $nativeQuery->getResult();

        return $result;
    }
    
   
//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
