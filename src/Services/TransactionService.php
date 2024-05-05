<?php

namespace App\Service;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getTransactionsByAccountId(int $accountId): array
    {
        $repository = $this->entityManager->getRepository(Transaction::class);
        return $repository->createQueryBuilder('t')
            ->where('t.fromaccount = :accountId OR t.toaccount = :accountId')
            ->setParameter('accountId', $accountId)
            ->getQuery()
            ->getResult();
    }

    
    public function getAllTransactionsByWalletId(int $walletId): array
    {
        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->join('t.fromaccount', 'fromAccount')
            ->join('t.toaccount', 'toAccount')
            ->where('fromAccount.idwallet = :walletId OR toAccount.idwallet = :walletId')
            ->setParameter('walletId', $walletId)
            ->getQuery()
            ->getResult();

        return $transactions;
    }


    public function deleteTransaction(Transaction $transaction): void
    {
        $transactionType = $transaction->getType();

        switch ($transactionType) {
            case 'INCOME':
                $this->revertIncomeTransaction($transaction);
                break;
            case 'TRANSFER':
                $this->revertTransferTransaction($transaction);
                break;
            case 'Expense':
                $this->revertExpenseTransaction($transaction);
                break;
            default:
                // Handle unsupported transaction types or throw an exception
                break;
        }

        // Remove the transaction from the database

        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
    }

    private function revertIncomeTransaction(Transaction $transaction): void
    {
        $account = $transaction->getFromaccount();
        $wallet = $account->getIdwallet();
        $amount = $transaction->getAmount();

   
        $account->setBalance($account->getBalance() - $amount);

        $wallet->setTotalBalance($wallet->getTotalbalance() - $amount);

        $this->entityManager->flush();
    }

    private function revertTransferTransaction(Transaction $transaction): void
    {
      
        $fromAccount = $transaction->getFromaccount();
        $toAccount = $transaction->getToaccount();
        $amount = $transaction->getAmount();

        
        $fromAccount->setBalance($fromAccount->getBalance() + $amount);

    
        $toAccount->setBalance($toAccount->getBalance() - $amount);

      
        $this->entityManager->flush();
    }

    private function revertExpenseTransaction(Transaction $transaction): void
    {
        $account = $transaction->getFromaccount();
        $wallet = $account->getIdwallet();
        $subcategory = $transaction->getIdcategory();
        $amount = $transaction->getAmount();

        // Increase the account balance
        $account->setBalance($account->getBalance() + $amount);

        // Increase the wallet total balance
        $wallet->setTotalbalance($wallet->getTotalbalance() + $amount);

        // Reduce the monthly expense of the associated subcategory
        $subcategory->setmtdepense($subcategory->getmtdepense() - $amount);

        // Persist changes to the database
        $this->entityManager->flush();
    }
}



