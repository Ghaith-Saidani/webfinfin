<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    public function save(User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findOneByCode(string $code): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.resetCode = :resetCode')
            ->setParameter('resetCode', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findByFullName($searchQuery)
    {
        return $this->createQueryBuilder('u')
            ->where('CONCAT(u.firstName, \' \', u.lastName) LIKE :query')
            ->setParameter('query', '%' . $searchQuery . '%')
            ->getQuery()
            ->getResult();
    }
    public function updateUserValues(int $iduser, array $newValues): void
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
    
        // Build the query to update the user entity
        $qb->update('App\Entity\User', 'u')
           ->where('u.iduser = :iduser')
           ->setParameter('iduser', $iduser);
    
        // Update the user entity with the new values
        foreach ($newValues as $propertyName => $value) {
            $qb->set("u.$propertyName", ":$propertyName")
               ->setParameter($propertyName, $value);
        }
    
        // Execute the query
        $qb->getQuery()->execute();
    }
}
