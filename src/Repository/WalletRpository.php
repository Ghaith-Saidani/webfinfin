<?php

namespace App\Repository;

use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wallet>
 *
 * @method Wallet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wallet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wallet[]    findAll()
 * @method Wallet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }


    public function getIdWalletByUserID($idUser)
    {
        return $this->createQueryBuilder('w')
            ->select('w.idwallet')
            ->andWhere('w.iduser = :idUser')
            ->setParameter('idUser', $idUser)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getWalletByUserId(int $userId): ?Wallet
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.iduser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

 /* public function addWallet(string $name, string $currency, float $totalBalance, int $idUser): Wallet
    {
        $wallet = new Wallet();
        $wallet->setName($name);
        $wallet->setCurrency($currency);
        $wallet->setTotalBalance($totalBalance);
        $wallet->setIdUser($idUser);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($wallet);
        $entityManager->flush();

        return $wallet;
    }

*/
   

//    /**

//     * @return Wallet[] Returns an array of Wallet objects
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

//    public function findOneBySomeField($value): ?Wallet
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
