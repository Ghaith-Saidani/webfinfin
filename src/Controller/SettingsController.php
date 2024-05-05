<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SubCategoryRepository;
use App\Repository\TransactionRepository;
use App\Repository\PayeeRepository;
use App\Repository\AccountRepository;
use App\Repository\WalletRepository;
use App\Repository\UserRepository;
use App\Service\TransactionService;
use App\Entity\Wallet;
use App\Entity\Account;
use App\Entity\Transaction;
use App\Entity\Subcategory;
use Symfony\Component\HttpFoundation\Request;
use App\Service\OpenExchangeRatesService;
use Money\Money;
use Money\Currency;


class SettingsController extends AbstractController
{    
    private $transactionRepository;
    private $accountRepository;
    private $transactionService;
    private $walletRepository;
    private $subCategoryRepository;
    private $payeeRepository;
    private $exchangeService;
   
    

    public function __construct(PayeeRepository $payeeRepository ,TransactionRepository $transactionRepository, AccountRepository $accountRepository, WalletRepository $walletRepository,SubCategoryRepository $subCategoryRepository,TransactionService $transactionService)
    {
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
        $this->transactionService = $transactionService;
        $this->walletRepository = $walletRepository;
        $this->subCategoryRepository = $subCategoryRepository;
        $this->payeeRepository = $payeeRepository;
      
        

    }



    #[Route('/settings', name: 'app_settings')]
    public function index(): Response
    {   $userid=2;
        // $user = $userRepository->find(2);
         
         // Fetch the idWallet of the logged-in user
         $idWallet = $this->walletRepository->getWalletByUserId($userid);
         
         $totalBalance = $idWallet->getTotalBalance();

         // Create a Money object with the total balance and currency code
         $money = new Money((int) ($totalBalance * 100), new Currency($idWallet->getCurrency()));
        return $this->render('settings/index.html.twig', [
            'controller_name' => 'SettingsController',
            'wallet' => $idWallet,
            'money' => $money,

        ]);
    }


    #[Route('/settings/exchange-rate/{fromCurrency}/{toCurrency}', name: 'exchange_rate')]
    public function getExchangeRate($fromCurrency, $toCurrency): Response
    {
        $apiKey = 'a5ebce9159e44a29bd13b12497391d8b';
        $apiUrl = "https://open.er-api.com/v6/latest/$fromCurrency?apikey=$apiKey";

        try {
            $response = file_get_contents($apiUrl);
            $data = json_decode($response, true);

            if (isset($data['rates'][$toCurrency])) {
                return $this->json(['exchangeRate' => $data['rates'][$toCurrency]]);
            } else {
                return $this->json(['error' => 'Exchange rate not found'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




    #[Route('/settings/change-currency/{newCurrency}', name: 'change_currency')]
    public function changeCurrency(string $newCurrency,Request $request): Response
    {
        

        // Fetch the current user's wallet (assuming you have a way to identify the user)
        $userId = 2;
        
        $wallet = $this->walletRepository->getWalletByUserId($userId);
    
        if (!$wallet) {
            throw $this->createNotFoundException('Wallet not found for the current user');
        }
    
        // Get the old currency
        $oldCurrency = $wallet->getCurrency();
    
        // Update the currency of the wallet
        $wallet->setCurrency($newCurrency);
        // Fetch and update related entities
        $entityManager = $this->getDoctrine()->getManager();
        $oldTotalBalance = $wallet->getTotalbalance();
            $newTotalBalance = $this->convertCurrency($oldTotalBalance, $oldCurrency, $newCurrency);
            $wallet->setTotalbalance($newTotalBalance);
            $entityManager->persist($wallet);
    
        // Update Account balances
        $accounts = $this->accountRepository->findByWalletId($wallet);
        foreach ($accounts as $account) {
            $oldBalance = $account->getBalance();
            $newBalance = $this->convertCurrency($oldBalance, $oldCurrency, $newCurrency);
            $account->setBalance($newBalance);
            $entityManager->persist($account);
        }
    
        // Update Transaction amounts
        $transactions =  $this->transactionService->getAllTransactionsByWalletId($wallet->getIdWallet());

        foreach ($transactions as $transaction) {
            $oldAmount = $transaction->getAmount();
            $newAmount = $this->convertCurrency($oldAmount, $oldCurrency, $newCurrency);
            $transaction->setAmount($newAmount);
            $entityManager->persist($transaction);
        }
    
       /* // Update Subcategory amounts
        $subcategories = $this->subCategoryRepository->findByWalletId($wallet);
        foreach ($subcategories as $subcategory) {
         $oldSpentAmount = $subcategory->getMtdepense();
        $newSpentAmount = $this->convertCurrency($oldSpentAmount, $oldCurrency, $newCurrency);
        $subcategory->setMtdepense($newSpentAmount);
       $entityManager->persist($subcategory);
      } */
    
        // Flush changes to the database
        $entityManager->flush();
    
        // Optionally, return a response indicating success
        return $this->json(['message' => 'Currency changed successfully']);
    }
    
    private function convertCurrency($amount, $fromCurrency, $toCurrency)
{
    // Fetch the exchange rate between the two currencies
    $response = $this->getExchangeRate($fromCurrency, $toCurrency);

    // If exchange rate is not available, return null or handle the error accordingly
    if ($response->getStatusCode() !== 200) {
        throw new \Exception('Failed to fetch exchange rate');
    }

    $data = json_decode($response->getContent(), true);

    // If exchange rate is not available, return null or handle the error accordingly
    if (!isset($data['exchangeRate'])) {
        throw new \Exception('Exchange rate not available');
    }

    $exchangeRate = $data['exchangeRate'];

    // Convert the amount to the new currency
    return $amount * $exchangeRate;
}



}




