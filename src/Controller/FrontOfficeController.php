<?php

namespace App\Controller;

use App\Repository\DebtRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontOfficeController extends AbstractController
{
    #[Route('/front/office', name: 'app_front_office')]
    public function index(): Response
    {
        return $this->render('front_office/index.html.twig', [
            'controller_name' => 'FrontOfficeController',
        ]);
    }
    #[Route('/front/office/Debt', name: 'app_front_office_Debt')]
    public function indexDebt(DebtRepository $debtRepository): Response
    {
        $debts = $debtRepository->findAll(); // Fetch all debts from the database

        return $this->render('front_office/indexDebt.html.twig', [
            'debts' => $debts, // Pass the debts to the template
            'controller_name' => 'FrontOfficeController',
        ]);
    }
}
