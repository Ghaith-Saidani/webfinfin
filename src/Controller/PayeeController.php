<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Payee;
use App\Repository\PayeeRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\WalletRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class PayeeController extends AbstractController
{ 
    
    private $transactionRepository;

    private $walletRepository;
    private $payeeRepository;
    public function __construct(WalletRepository $walletRepository,TransactionRepository $transactionRepository,PayeeRepository $payeeRepository)
    {
       
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
        $this->payeeRepository = $payeeRepository;
      

    }





    #[Route('/payee', name: 'payees_index')]
    public function index(): Response
    {
        $payees = $this->getDoctrine()->getRepository(Payee::class)->findAll();

        return $this->render('payee/index.html.twig', [
            'payees' => $payees,
        ]);
    }

    #[Route('/payee/add', name: 'add_payee')]
    public function addPayee(Request $request): Response
    {   
        $userid = 2; 
        $idWallet = $this->walletRepository->getWalletByUserId($userid);
        $requestData = json_decode($request->getContent(), true);
    
        // Check if the 'payeeName' key exists in the decoded JSON data
        if (!isset($requestData['payeeName'])) {
            return new Response('Payee name is required', Response::HTTP_BAD_REQUEST);
        }
    
        $payeeName = $requestData['payeeName'];
    
        // Check if the payee name already exists
        $existingPayee = $this->payeeRepository->findOneBy(['namepayee' => $payeeName]);
        if ($existingPayee) {
            return new Response('Payee name already exists', Response::HTTP_BAD_REQUEST);
        }
    
        // Validate input if necessary
        
        $payee = new Payee();
        $payee->setNamepayee($payeeName);
        $payee->setIdwallet($idWallet);
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($payee);
        $entityManager->flush();
    
        // Return a success response
        return new Response('Payee added successfully', Response::HTTP_OK);
    }
    

    
    #[Route('/payee/delete/{id}', name: 'delete_payee')]
    public function deletePayee(int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $payee = $this->payeeRepository->find($id);
    
        if (!$payee) {
            return new Response('Payee not found', Response::HTTP_NOT_FOUND);
        }
    
        // Find transactions with the deleted payee's ID
        $transactionsToUpdate = $this->transactionRepository->findBy(['idpayee' => $id]);
    
        // Get the ID of another payee to replace the deleted payee's ID
        $otherPayee = $this->payeeRepository->findOneBy([], ['idpayee' => 'ASC']);
    
        // Update transactions with the ID of another payee
        foreach ($transactionsToUpdate as $transaction) {
            $transaction->setIdpayee($otherPayee);
            $entityManager->persist($transaction);
        }
    
        // Remove the payee
        $entityManager->remove($payee);
        $entityManager->flush();
    
        return new Response('Payee deleted successfully', Response::HTTP_OK);
    }
    
    #[Route('/payee/update/{id}', name: 'update_payee')]
    public function updatePayee(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $payee = $this->payeeRepository->find($id);
    
        if (!$payee) {
            return new Response('Payee not found', Response::HTTP_NOT_FOUND);
        }
    
        $requestData = json_decode($request->getContent(), true);
    
        // Check if the 'payeeName' key exists in the decoded JSON data
        if (!isset($requestData['payeeName']) || empty($requestData['payeeName'])) {
            return new Response('Payee name is required', Response::HTTP_BAD_REQUEST);
        }
    
        $newPayeeName = $requestData['payeeName'];
    
        // Check if the payee name already exists
        $existingPayee = $this->payeeRepository->findOneBy(['namepayee' => $newPayeeName]);
        if ($existingPayee && $existingPayee->getIdpayee() !== $payee->getIdpayee()) {
            return new Response('Payee name already exists', Response::HTTP_BAD_REQUEST);
        }
    
        try {
            // Update the payee name
            $payee->setNamepayee($newPayeeName);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new Response('Payee name must be unique', Response::HTTP_BAD_REQUEST);
        }
    
        return new Response('Payee updated successfully', Response::HTTP_OK);
    }

}
