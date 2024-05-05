<?php

namespace App\Controller;
use App\Repository\AccountRepository;
use App\Repository\WalletRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\AccountType;
use App\Form\AccountEditType;
use App\Form\EditAcc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Account;
use App\Repository\SubCategoryRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Transaction;
use App\Repository\PayeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TransactionService;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Knp\Component\Pager\PaginatorInterface;

class AccountsController extends AbstractController
{  
    private $transactionRepository;
    private $accountRepository;
    private $transactionService;
    private $walletRepository;
    private $subCategoryRepository;
    private $payeeRepository;
    

    public function __construct(PayeeRepository $payeeRepository ,TransactionRepository $transactionRepository, AccountRepository $accountRepository, WalletRepository $walletRepository,SubCategoryRepository $subCategoryRepository,TransactionService $transactionService)
    {
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
        $this->transactionService = $transactionService;
        $this->walletRepository = $walletRepository;
        $this->subCategoryRepository = $subCategoryRepository;
        $this->payeeRepository = $payeeRepository;

    }
    
    
    #[Route('/accounts', name: 'app_accounts')]
    public function index(UserRepository $userRepository,AccountRepository $accountRepository, WalletRepository $walletRepository): Response
    {
        // Fetch the logged-in user
        $userid=2;
       // $user = $userRepository->find(2);
        
        // Fetch the idWallet of the logged-in user
        $idWallet = $walletRepository->getIdWalletByUserID($userid);

        
        $defaultBankName = '';

        // Fetch the accounts associated with the retrieved idWallet
        $accounts = $accountRepository->findAccountsByWalletId($idWallet);

        return $this->render('accounts/index.html.twig', [
            'controller_name' => 'AccountsController',
            'accounts' => $accounts,
            'wallet' => $idWallet,
            'defaultBankName' => $defaultBankName,

        ]);
    }


    #[Route('/accounts/add', name: 'account_add')]
    public function addAccount( WalletRepository $walletRepository,Request $request): Response
    {    
          // Fetch the logged-in user
          $userid=2;
        
          // Fetch the idWallet of the logged-in user
          $wallet = $walletRepository->getWalletByUserId($userid);
          $account = new Account(); 
          $account->setIdWallet($wallet);
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $newAccountBalance = $account->getBalance();
            $currentTotalBalance = $wallet->getTotalBalance();
            $newTotalBalance = $currentTotalBalance + $newAccountBalance;
            $wallet->setTotalBalance($newTotalBalance);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($account);
            $entityManager->flush();
    
            $this->addFlash('success', 'Account added successfully!');
    
            return $this->redirectToRoute('account_added_success');
        }
    
        return $this->render('accounts/add-account.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/accounts/added-success', name: 'account_added_success')]
    public function accountAddedSuccess(): Response
    {
        return $this->render('accounts/account-added-succ.html.twig');
    }


    #[Route('/accounts/manage', name: 'account_manager')]
    public function manage(Request $request,UserRepository $userRepository,AccountRepository $accountRepository, WalletRepository $walletRepository,PaginatorInterface $paginator): Response
    {
        // Fetch the logged-in user
    $userid = 2;

    // Fetch the idWallet of the logged-in user
    $idWallet = $walletRepository->getIdWalletByUserID($userid);
    $wallet = $walletRepository->getWalletByUserId($userid);

    // Fetch the accounts associated with the retrieved idWallet
    $query = $accountRepository->findByWalletId($idWallet);

    // If search query is submitted
    $searchTerm = $request->query->get('search');
    if ($searchTerm) {
        // If there's a search term, use the modified query to filter accounts
        $query = $accountRepository->findAccountsByWalletIdWithSearch($idWallet, $searchTerm);
    }

    // Paginate the results
    $accounts = $paginator->paginate(
        $query, // Query to paginate
        $request->query->getInt('page', 1), // Current page number, default is 1
        10 // Items per page
    );

    return $this->render('accounts/account-manager.html.twig', [
        'accounts' => $accounts,
        'wallet' => $idWallet,
        'searchTerm' => $searchTerm, // Pass the search term to the Twig template
    ]);
    }



    #[Route('/accounts/delete/{id}', name: 'delete_account')]
    public function deleteAccount(int $id, AccountRepository $accountRepository, WalletRepository $walletRepository, EntityManagerInterface $entityManager): Response
    {
        $account = $accountRepository->find($id);
    
        if (!$account) {
            throw $this->createNotFoundException('Account not found');
        }
    
        // Retrieve the associated wallet
        $wallet = $account->getIdWallet();
    
        // Subtract the account balance from the wallet total balance
        $wallet->setTotalbalance($wallet->getTotalbalance() - $account->getBalance());
    
        // Retrieve related transactions (both from and to account)
        $transactions = $entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->where('t.fromaccount = :idaccount OR t.toaccount = :idaccount')
            ->setParameter('idaccount', $id)
            ->getQuery()
            ->getResult();
    
        // Delete the related transactions
        foreach ($transactions as $transaction) {
            $entityManager->remove($transaction);
        }
    
        // Flush the changes to delete the transactions
        $entityManager->flush();
    
        // Now, delete the account
        $entityManager->remove($account);
        $entityManager->flush();
    
        $this->addFlash('success', 'Account and related transactions deleted successfully!');
    
        return $this->redirectToRoute('account_manager');
    }


   

    #[Route('/accounts/get-transactions/{accountId}', name: 'get_transactions')]
    public function getTransactions(int $accountId): JsonResponse
    {
        $transactions = $this->transactionService->getTransactionsByAccountId($accountId);

        $serializedTransactions = [];
        foreach ($transactions as $transaction) {
            $serializedTransactions[] = [
                'category' => $transaction->getIdCategory()->getName(),
                'date' => $transaction->getDate()->format('Y-m-d'),
                'description' => $transaction->getDescription(),
                'type' => $transaction->getType(),
                'amount' => $transaction->getAmount(),
                'currency_symbol' => $transaction->getToAccount()->getIdWallet()->getCurrencySymbol(),
            ];
        }

        return new JsonResponse($serializedTransactions);
    }


    #[Route('/account/edit/{id}', name: 'account_edit')]
    public function editAccount(int $id, Request $request, AccountRepository $accountRepository, EntityManagerInterface $entityManager): Response
    {
         $userid=2;
        
        
        $wallet = $this->walletRepository->getWalletByUserId($userid);
        $payees = $this->payeeRepository->findByWalletId($wallet);
        $account = $accountRepository->find($id);
    
        // Check if the account exists
        if (!$account) {
            throw $this->createNotFoundException('Account not found');
        }
    
        // Get the old balance
        $oldBalance = $account->getBalance();
    
        // Create the form for editing the account
        $form = $this->createForm(AccountEditType::class, $account);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the new balance
            $newBalance = $account->getBalance();
    
            // Calculate the difference between old and new balances
            $difference = $newBalance - $oldBalance;
            $budgetSubcategory = $this->subCategoryRepository->findBudgetSubcategory();

               // Update wallet balance
            $walletBalance = $wallet->getTotalbalance();
            $wallet->setTotalbalance($walletBalance + $difference);
            $entityManager->persist($wallet);
    
            // Create a new transaction based on the difference
            if ($difference != 0) {
                // Create a transaction entity
                $transaction = new Transaction();
                $transaction->setFromaccount($account);
                $transaction->setToaccount($account);
                $transaction->setDate(new \DateTime());
                $transaction->setIdPayee($payees[0]);
    
                if ($difference > 0) {
                    // Income transaction
                    $transaction->setType('INCOME');
                    $transaction->setAmount($difference);
                    $transaction->setIdcategory($budgetSubcategory);
                    $transaction->setDescription('INCOME Transaction after Editing account');

                } else {
                    // Expense transaction
                    $transaction->setType('EXPENSE');
                    $transaction->setAmount(abs($difference));
                    $transaction->setIdcategory($budgetSubcategory);
                    $transaction->setDescription('Expense Transaction after Editing account');

                }
    
                // Persist the transaction entity
                $entityManager->persist($transaction);
            }
    
            // Update the account entity
            $entityManager->persist($account);
            $entityManager->flush();
    
            $this->addFlash('success', 'Account updated successfully!');
            return $this->redirectToRoute('account_manager');
        }
    
        // Render the edit account form template
        return $this->render('accounts/edit-account.html.twig', [
            'form' => $form->createView(),
            'account' => $account,
            // Other variables to pass to the Twig template, if any...
        ]);
    }




    #[Route('/accounts/get-balance-over-time/{accountId}', name: 'get_balance_over_time')]
public function getAccountBalanceOverTime($accountId): JsonResponse
{
    // Fetch the transactions for the specified account ID
    $transactions = $this->transactionService->getTransactionsByAccountId($accountId);
    
    // Sort transactions by date
    usort($transactions, function($a, $b) {
        return $a->getDate() <=> $b->getDate();
    });
    
    // Initialize balances
    $incomeBalance = 0.0;
    $expenseBalance = 0.0;
    
    // Map to store the combined balance for each date
    $combinedBalanceMap = [];

    // Iterate through transactions to calculate balance over time
    foreach ($transactions as $transaction) {
        $transactionDate = $transaction->getDate()->format('Y-m-d');

        // Update the balance based on transaction type
        if ($transaction->getType() == 'INCOME') {
            $incomeBalance += $transaction->getAmount();
        } else if ($transaction->getType() == 'EXPENSE') {
            $expenseBalance += $transaction->getAmount();
        }

        // Calculate the net balance
        $netBalance = $incomeBalance - $expenseBalance;

        // Store the net balance for the date
        $combinedBalanceMap[$transactionDate] = round($netBalance, 2); // Round to 2 decimal places
    }

    // Return the response as JSON
    return new JsonResponse($combinedBalanceMap);
}

}


