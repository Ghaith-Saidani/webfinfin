<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Account>
 *
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function findAccountsByWalletId(int $idWallet): array
    {
        return $this->createQueryBuilder('a')
        ->where('a.idwallet = :idWallet')
        ->setParameter('idWallet', $idWallet)
        ->getQuery()
        ->getResult();
    }

    public function findByWalletId( $walletId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.idwallet = :walletId')
            ->setParameter('walletId', $walletId)
            ->getQuery()
            ->getResult();
    }

    public function findAccountsByWalletIdWithSearch($idWallet, $searchTerm)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.idwallet = :idWallet')
            ->andWhere('LOWER(a.nameaccount) LIKE :searchTerm 
                OR LOWER(a.typeaccount) LIKE :searchTerm 
                OR LOWER(a.description) LIKE :searchTerm 
                OR CONCAT(a.balance, \'\') LIKE :searchTerm ')
            ->setParameter('idWallet', $idWallet)
            ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%')
            ->getQuery()
            ->getResult();
    }
    



//    /**
//     * @return Account[] Returns an array of Account objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Account
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
