<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\JsonResponse;



class BaseController extends AbstractController
{
    #[Route('/', name: 'app_base')]
    public function index(): Response
    {
        return $this->render('html/index.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }
    #[Route('/calendar', name: 'app_calendar', methods: ['GET'])]
    public function calendarView(TaskRepository $Task): Response
    {
        return $this->render('calendar/index.html.twig',['task' => $Task->findAll(),]);
    }
}
