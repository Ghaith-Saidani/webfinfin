<?php

namespace App\Controller;

use App\Repository\DebtRepository;
use App\Repository\TaskRepository;
use App\Repository\TodolistRepository;
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

    #[Route('/front/office/debt', name: 'app_front_office_Debt')]
    public function indexDebt(DebtRepository $debtRepository): Response
    {
        $debts = $debtRepository->findAll(); // Fetch all debts from the database

        return $this->render('front_office/indexDebt.html.twig', [
            'debts' => $debts, // Pass the debts to the template
            'controller_name' => 'FrontOfficeController',
        ]);
    }
    #[Route('/front/office/Wishlist', name: 'app_front_office_Wishlist')]
    public function indexWishlist(): Response
    {
         // Fetch all debts from the database

        return $this->render('front_office/indexWishlist.html.twig', [
           
            'controller_name' => 'FrontOfficeController',
        ]);
    }
    #[Route('/front/office/task', name: 'app_front_office_Task')]
    public function indexTask(TaskRepository $taskRepository): Response
    {
        return $this->render('front_office/indexTask.html.twig', [
            'tasks' => $taskRepository->findAll(),
            'controller_name' => 'FrontOfficeController',
        ]);
    }

    #[Route('/front/office/todo', name: 'app_front_office_Todo')]
    public function indexTodo(TodolistRepository $todolistRepository): Response
    {
        return $this->render('front_office/indexTodo.html.twig', [
            'todolists' => $todolistRepository->findAll(),
            'controller_name' => 'FrontOfficeController',
        ]);
    }

}
