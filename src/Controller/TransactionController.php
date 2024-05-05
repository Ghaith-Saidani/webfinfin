<?php

namespace App\Controller;

use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TransactionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Transaction;
use App\Form\IncomeTransactionType;
use App\Form\TransferTransactionType;
use App\Form\ExpenseTransactionType;
use App\Repository\SubCategoryRepository;
use App\Repository\WalletRepository;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use Symfony\Component\Form\FormError;
use Knp\Component\Pager\PaginatorInterface;
class TransactionController extends AbstractController
{
    private $transactionRepository;
    private $accountRepository;
    private $transactionService;
    private $walletRepository;
    private $subCategoryRepository;
    

    public function __construct(TransactionRepository $transactionRepository, AccountRepository $accountRepository, WalletRepository $walletRepository,SubCategoryRepository $subCategoryRepository,TransactionService $transactionService)
    {
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
        $this->transactionService = $transactionService;
        $this->walletRepository = $walletRepository;
        $this->subCategoryRepository = $subCategoryRepository;

    }

    #[Route('/transaction', name: 'app_transaction')]
    public function index(): Response
    {
        // Fetch transactions from the repository
        $transactions = $this->transactionRepository->findAll();

        // Fetch accounts from the repository
        $accounts = $this->accountRepository->findAll();
        $defaultAccountName = '';
        $defaultAccountBalance = 0;
        $defaultAccountCurrency ='';
        return $this->render('transaction/index.html.twig', [
            'controller_name' => 'TransactionController',
            'transactions' => $transactions,
            'accounts' => $accounts,
            'defaultAccountName' => $defaultAccountName,
            'defaultAccountCurrency' => $defaultAccountCurrency,
            'defaultAccountBalance' => $defaultAccountBalance

        ]);
    }



   

    #[Route('/get-transactions/account/{accountId}', name: 'get_accounts_transactions')]
    public function getTransactions(int $accountId, PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $transactions = $this->transactionService->getTransactionsByAccountId($accountId);
        
        // Paginate the fetched transactions
        $paginatedTransactions = $paginator->paginate(
            $transactions, // Query to paginate
            $request->query->getInt('page', 1), // Current page number
            10 // Items per page
        );
    
        // Serialize paginated transactions
        $serializedTransactions = [];
        foreach ($paginatedTransactions as $transaction) {
            $serializedTransactions[] = [
                'idtransaction' => $transaction->getIdtransaction(),
                'category' => $transaction->getIdCategory()->getName(),
                'date' => $transaction->getDate()->format('Y-m-d'),
                'description' => $transaction->getDescription(),
                'type' => $transaction->getType(),
                'amount' => $transaction->getAmount(),
                'toaccount' => $transaction->getToaccount()->getNameaccount(),
                'fromaccount' => $transaction->getFromaccount()->getNameaccount(),
                'payee' => $transaction->getIdpayee()->getNamepayee(),
                'currency_symbol' => $transaction->getToAccount()->getIdWallet()->getCurrencySymbol(),
            ];
        }
    
        // Return paginated transactions as JSON response
        return new JsonResponse($serializedTransactions);
    }
    
    #[Route('/get-transactions/all', name: 'get_all_transactions')]
    public function getAllTransactions(PaginatorInterface $paginator, Request $request): JsonResponse
    {    
        $userid = 2; 
    
        // Fetch all transactions for the user's wallet
        $idWallet = $this->walletRepository->getIdWalletByUserID($userid);
        $transactions = $this->transactionService->getAllTransactionsByWalletId($idWallet);
        
        // Paginate the fetched transactions
        $paginatedTransactions = $paginator->paginate(
            $transactions, // Query to paginate
            $request->query->getInt('page', 1), // Current page number
            10 // Items per page
        );
    
        // Serialize paginated transactions
        $serializedTransactions = [];
        foreach ($paginatedTransactions as $transaction) {
            $serializedTransactions[] = [
                'category' => $transaction->getIdCategory()->getName(),
                'date' => $transaction->getDate()->format('Y-m-d'),
                'description' => $transaction->getDescription(),
                'type' => $transaction->getType(),
                'amount' => $transaction->getAmount(),
                'toaccount' => $transaction->getToaccount()->getNameaccount(),
                'fromaccount' => $transaction->getFromaccount()->getNameaccount(),
                'payee' => $transaction->getIdpayee()->getNamepayee(),
                'currency_symbol' => $transaction->getToAccount()->getIdWallet()->getCurrencySymbol(),
            ];
        }
    
        // Return paginated transactions as JSON response
        return new JsonResponse($serializedTransactions);
    }
    

    #[Route('/transaction/add/income/{accountId}', name: 'transaction_add_income')]
    public function addIncome(Request $request,int $accountId): Response
    {    $userid = 2; 
    
        $Wallet = $this->walletRepository->getWalletByUserId($userid);
        $account = $this->accountRepository->find($accountId);
        $budgetSubcategory = $this->subCategoryRepository->findBudgetSubcategory();
        $transaction = new Transaction();
        $transaction->setFromaccount($account);
        $transaction->setToaccount($account);
        $transaction->setDate(new DateTime());
        $transaction->setIdcategory($budgetSubcategory);
        $transaction->setType('INCOME');

         
       
        
        $form = $this->createForm(IncomeTransactionType::class, $transaction, [
            'wallet_id' => $Wallet,
        ]);

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $amount = $transaction->getAmount();
        
            // Update the account balance
            $newAccountBalance = $account->getBalance() + $amount;
            $account->setBalance($newAccountBalance);
            
            // Update the wallet balance
            $newWalletBalance = $Wallet->getTotalbalance() + $amount;
            $Wallet->setTotalbalance($newWalletBalance);




            // Persist the transaction to the database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($transaction);
            $entityManager->flush();

            // Redirect to a success page or render a success message
            return $this->redirectToRoute('transaction_add_success');
        }

        // Render the form template
        return $this->render('transaction/add_income_transaction.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/transaction/added-success', name: 'transaction_add_success')]
    public function accountAddedSuccess(): Response
    {
        return $this->render('transaction/transaction-added-succ.html.twig');
    }

    #[Route('/get-account-details/{id}', name: 'get_account_details') ]

    public function getAccountDetails(int $id): JsonResponse
    {
        $account = $this->accountRepository->find($id);

        if (!$account) {
            return new JsonResponse(['error' => 'Account not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Assuming $account is an object with properties like name, balance, etc.
        $accountDetails = [
            'idaccount' => $account->getIdaccount(),
            'nameaccount' => $account->getNameaccount(),
            'balance' => $account->getBalance(),
            'currency_symbol' => $account->getIdWallet()->getCurrencySymbol(),
            // Add other properties as needed
        ];

        return new JsonResponse($accountDetails);
    }









    #[Route('/transaction/add/transfer/{accountId}', name: 'transaction_add_transfer')]
    public function addTransfer(Request $request, int $accountId): Response
    {
        // Fetch necessary entities
        $userId = 2; 
        $wallet = $this->walletRepository->getWalletByUserId($userId);
        $fromAccount = $this->accountRepository->find($accountId);
        $budgetSubcategory = $this->subCategoryRepository->findBudgetSubcategory();
    
        // Create a new transaction
        $transaction = new Transaction();
        $transaction->setFromaccount($fromAccount);
        $transaction->setDate(new \DateTime());
        $transaction->setIdcategory($budgetSubcategory);
        $transaction->setType('TRANSFER');
    
        // Create the form
        $form = $this->createForm(TransferTransactionType::class, $transaction, [
            'wallet_id' => $wallet,
        ]);
    
        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the data from the form
            $formData = $form->getData();
            $toAccount = $formData->getToAccount(); // Assuming there's a method to get the To Account from the form data
            $amount = $formData->getAmount(); // Assuming there's a method to get the Amount from the form data
    
            // Transfer logic
            $fromAccountBalance = $fromAccount->getBalance();
            $toAccountBalance = $toAccount->getBalance();
    
            // Check if the From Account has sufficient balance for the transfer
            if ($fromAccountBalance >= $amount) {
                // Update balances
                $fromAccount->setBalance($fromAccountBalance - $amount);
                $toAccount->setBalance($toAccountBalance + $amount);
    
                // Persist the updated accounts
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($fromAccount);
                $entityManager->persist($toAccount);
    
                // Persist the transaction to the database
                $entityManager->persist($transaction);
                $entityManager->flush();
    
                // Redirect to a success page or render a success message
                return $this->redirectToRoute('transaction_add_success');
                
            } else {
                // Redirect back with an error message indicating insufficient funds
                $insufficientFundsError = 'Insufficient funds in the From Account.';
            }
        }
    
        // Render the form template
        return $this->render('transaction/add_transfer_transaction.html.twig', [
            'form' => $form->createView(),
            'insufficientFundsError' => $insufficientFundsError ?? null, // Pass the error message to the Twig template
        ]);
    }
    


    #[Route('/transaction/add/expense/{accountId}', name: 'transaction_add_expense')]
    public function addExpense(Request $request, int $accountId): Response
    {
        // Fetch necessary entities
        $userId = 2; 
        $wallet = $this->walletRepository->getWalletByUserId($userId);
        $fromAccount = $this->accountRepository->find($accountId);
        
        // Create a new transaction
        $transaction = new Transaction();
        $transaction->setFromaccount($fromAccount);
        $transaction->setToaccount($fromAccount);
        $transaction->setDate(new \DateTime());
        $transaction->setType('Expense');
    
        // Create the form
        $form = $this->createForm(ExpenseTransactionType::class, $transaction, [
            'wallet_id' => $wallet,
        ]);
    
        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the data from the form
            $formData = $form->getData();
            $amount = $formData->getAmount();
            $subCategoryId = $formData->getIdcategory(); // Assuming you have a method to get the subcategory ID from the form data
            
            // Get the subcategory
            // Get the subcategory
            $subcategory = $this->subCategoryRepository->find($subCategoryId);
    
            // Expense logic
            $fromAccountBalance = $fromAccount->getBalance();
    
            // Check if the From Account has sufficient balance for the expense
            if ($fromAccountBalance >= $amount) {
                // Update wallet balance
                $walletBalance = $wallet->getTotalbalance();
                $wallet->setTotalbalance($walletBalance - $amount);
    
                // Update account balance
                $fromAccount->setBalance($fromAccountBalance - $amount);
    
                // Update mtdépensé in subcategory
                $currentMonthlyExpense = $subcategory->getMtassigne();
                $newMonthlyExpense = $currentMonthlyExpense + $amount;
                $subcategory->setmtdepense($newMonthlyExpense);
    
                // Persist the updated entities
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($wallet);
                $entityManager->persist($fromAccount);
                $entityManager->persist($subcategory);
    
                // Persist the transaction to the database
                $entityManager->persist($transaction);
                $entityManager->flush();
    
                // Redirect to a success page or render a success message
                return $this->redirectToRoute('transaction_add_success');
            } else {
                // Set error message for insufficient funds
                $insufficientFundsError = 'Insufficient funds in the From Account.';
            }
        }
    
        // Render the form template
        return $this->render('transaction/add_expense_transaction.html.twig', [
            'form' => $form->createView(),
            'insufficientFundsError' => $insufficientFundsError ?? null, // Pass the error message to the Twig template
        ]);
    }



    #[Route('/transaction/delete/{id}', name: 'transaction_delete')]
public function deleteTransaction(int $id): Response
{
    $transaction = $this->transactionRepository->find($id);
    if (!$transaction) {
        throw $this->createNotFoundException('Transaction not found');
    }

    $this->transactionService->deleteTransaction($transaction);

    // Optionally add a flash message or any other post-delete logic
        
    return $this->redirectToRoute('app_transaction');
}

#[Route('/transaction/export-transactions', name: 'export_transactions')]
public function exportTransactions(): Response
{
    // Fetch transactions data from your database or any other source
    $transactions = $this->getDoctrine()->getRepository(Transaction::class)->findAll();

    // Create a CSV file content
    $csvContent = "Category,Date,Type,Description,Amount,From Account,To Account,Payee\n";
    foreach ($transactions as $transaction) {
        $csvContent .= "{$transaction->getIdCategory()->getName()},{$transaction->getDate()->format('Y-m-d')},{$transaction->getType()},{$transaction->getDescription()},{$transaction->getAmount()},{$transaction->getFromaccount()->getNameaccount()},{$transaction->getToaccount()->getNameaccount()},{$transaction->getidPayee()->getNamepayee()}\n";
    }

    // Set response headers for CSV file download
    $response = new Response($csvContent);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

    return $response;
}

}
    
    

