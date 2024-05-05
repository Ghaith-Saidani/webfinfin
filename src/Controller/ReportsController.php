<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Repository\SubCategoryRepository;
use App\Repository\WalletRepository;
use App\Service\TransactionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Transaction;
use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use DateTime;

class ReportsController extends AbstractController
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
    #[Route('/reports', name: 'app_reports')]
    public function index(): Response
    {
        return $this->render('reports/index.html.twig', [
            'controller_name' => 'ReportsController',
        ]);
    }
    
    #[Route('/reports/incomevsexpense', name: 'app_reports_income_vs_expense')]
    public function incomevsexpense(): Response
    {
        
        return $this->render('reports/incomevsexpense.html.twig', [
            'controller_name' => 'ReportsController',
        ]);
    }

    #[Route('/reports/income-vs-expense-data', name: 'app_reports_income_vs_expense_data')]
    public function getIncomeVsExpenseData(): JsonResponse
    {     
        $userid = 2;
            
        $wallet = $this->walletRepository->getIdWalletByUserId($userid);
        
        // Fetch transactions for all accounts
        $transactions = $this->transactionService->getAllTransactionsByWalletId($wallet);
    
        // Map to store income and expense totals for each date
        $incomeVsExpenseData = [];
    
        // Iterate through transactions to calculate income vs. expense totals for each date
        foreach ($transactions as $transaction) {
            $transactionDate = $transaction->getDate()->format('Y-m-d');
    
            // Initialize income and expense totals for the current date
            if (!isset($incomeVsExpenseData[$transactionDate])) {
                $incomeVsExpenseData[$transactionDate] = [
                    'income' => 0.0,
                    'expense' => 0.0
                ];
            }
    
            // Update income and expense totals based on transaction type
            if ($transaction->getType() === 'INCOME') {
                $incomeVsExpenseData[$transactionDate]['income'] += $transaction->getAmount();
            } else if ($transaction->getType() === 'EXPENSE') {
                $incomeVsExpenseData[$transactionDate]['expense'] += $transaction->getAmount();
            }
        }

        uksort($incomeVsExpenseData, function($a, $b) {
            return strtotime($a) - strtotime($b);
        });
    
        // Return the response as JSON
        return new JsonResponse($incomeVsExpenseData);
    }
    

}
