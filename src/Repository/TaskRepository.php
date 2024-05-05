<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

   /**
    * @return Task[] Returns an array of Task objects
    */

    public function findTasksDueInTwoDays(): array
    {
        // Get the current date and time
        $now = new \DateTime();
        
        // Calculate the date two days from now
        $twoDaysLater = (new \DateTime())->modify('+2 days');
        
        // Query tasks due in two days
        return $this->createQueryBuilder('t')
            ->andWhere('t.duedate >= :now')
            ->andWhere('t.duedate <= :twoDaysLater')
            ->setParameter('now', $now)
            ->setParameter('twoDaysLater', $twoDaysLater)
            ->getQuery()
            ->getResult();
    }
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
