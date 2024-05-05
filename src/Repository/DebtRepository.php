<?php

namespace App\Repository;

use App\Entity\Debt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Debt>
 *
 * @method Debt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Debt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Debt[]    findAll()
 * @method Debt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Debt::class);
    }

    public function findBySearchTerm($searchTerm)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.iddebt = :searchTerm')
            ->setParameter('searchTerm', $searchTerm)
            ->getQuery()
            ->getResult();
    }


    /**
     * Find debts that have about 7 days left until the payment date.
     *
     * @return Debt[] Returns an array of Debt objects
     */
    public function findDebtsDueInSevenDays(): array
    {
        // Get the current system date
        $currentDate = new \DateTime();

        // Calculate the end date by adding 7 days to the current date
        $endDate = clone $currentDate;
        $endDate->modify('+7 days');

        // Create a query builder
        $qb = $this->createQueryBuilder('d')
            ->andWhere('d.paymentdate BETWEEN :start AND :end')
            ->setParameter('start', $currentDate)
            ->setParameter('end', $endDate);

        // Execute the query and return the result
        return $qb->getQuery()->getResult();
    }

    public function findOverdueDebts(\DateTimeInterface $currentDate)
    {
        return $this->createQueryBuilder('d')
            ->where('d.paymentdate < :currentDate')
            ->setParameter('currentDate', $currentDate)
            ->getQuery()
            ->getResult();
    }

    public function getDebtDataForChart(): array
    {
        return $this->createQueryBuilder('d')
            ->select('dc.NameDebt as type, SUM(d.amounttopay) as totalAmount')
            ->leftJoin('d.type', 'dc')
            ->groupBy('d.type')
            ->orderBy('totalAmount', 'DESC')
            ->getQuery()
            ->getResult();
    }




    //    /**
    //     * @return Debt[] Returns an array of Debt objects
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

    //    public function findOneBySomeField($value): ?Debt
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}