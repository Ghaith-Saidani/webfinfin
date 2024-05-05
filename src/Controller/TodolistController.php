<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Todolist;
use App\Form\TodolistType;
use App\Repository\TodolistRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\QrCode;
use Symfony\Component\Templating\EngineInterface;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/todolist')]
class TodolistController extends AbstractController
{
    #[Route('/fetch_task_info', name: 'fetch_task_info', methods: ['POST'])]
    public function fetchTaskInfo(Request $request, EntityManagerInterface $entityManager, TodolistRepository $todolistRepository): JsonResponse
    {
        $todoId = $request->request->getInt('idtodo');
        $taskDates = $todolistRepository->findTaskDatesForTodolist($todoId);
    
        if (!$taskDates) {
            return new JsonResponse(['error' => 'Todolist or task dates not found'], 404);
        }
    
        // Convert the date strings to DateTime objects
        $firstTaskCreationDate = new \DateTime($taskDates['firstTaskCreationDate']);
        $lastTaskDueDate = new \DateTime($taskDates['lastTaskDueDate']);
    
        // Calculate the difference between the due date and the creation date!!
        $duration = $lastTaskDueDate->diff($firstTaskCreationDate);
        $durationString = $duration->format('Task done in %a days, %h hours, %i minutes, %s seconds');
    
        // Prepare the response data
        $responseData = [
            'duration' => $durationString,
        ];
    
        return new JsonResponse($responseData);
    }
    

    #[Route('/', name: 'app_todolist_index', methods: ['GET'])]
public function index(TodolistRepository $todolistRepository, UrlGeneratorInterface $urlGenerator, PaginatorInterface $paginator, Request $request): Response
{
    // Retrieve all todolists
    $todolists = $todolistRepository->findAll();

    // Paginate the results
    $pagination = $paginator->paginate(
        $todolists, // Query results
        $request->query->getInt('page', 1), // Current page number, default to 1
        1 // Number of items per page
    );

    // Generate URLs for each todolist
    $urls = [];
    foreach ($todolists as $todolist) {
        $urls[$todolist->getIdtodo()] = $urlGenerator->generate('get_tasks_by_todolist', ['id' => $todolist->getIdtodo()], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    return $this->render('todolist/index.html.twig', [
        'pagination' => $pagination,
        'urls' => $urls,
    ]);
}


    #[Route('/new', name: 'app_todolist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $todolist = new Todolist();
        $form = $this->createForm(TodolistType::class, $todolist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($todolist);
            $entityManager->flush();
            $this->addFlash('success','your to do list added !');

            return $this->redirectToRoute('app_todolist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('todolist/new.html.twig', [
            'todolist' => $todolist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_todolist_show', methods: ['GET'])]
    public function show(Todolist $todolist, EntityManagerInterface $entityManager): Response
    {
        $progress = $todolist->calculateProgress();

        $todolist->setProgress($progress);
        $entityManager->flush();

        $averageTime = $todolist->calculateAverageTimeBetweenCreationAndDueDate(); // Average time between creation and due date
        $completedTaskCount = $todolist->countCompletedTasks();
        $totalTaskCount = $todolist->getTasks()->count();

        return $this->render('todolist/show.html.twig', [
            'todolist' => $todolist,
            'averageTime' => $averageTime,
            'completedTaskCount' => $completedTaskCount,
            'totalTaskCount' => $totalTaskCount,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_todolist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Todolist $todolist, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TodolistType::class, $todolist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success','your to do list edited !');

            return $this->redirectToRoute('app_todolist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('todolist/edit.html.twig', [
            'todolist' => $todolist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_todolist_delete', methods: ['POST'])]
    public function delete(Request $request, Todolist $todolist, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$todolist->getIdtodo(), $request->request->get('_token'))) {
            $entityManager->remove($todolist);
            $entityManager->flush();
        }
        $this->addFlash('success','your to do list removed !');

        return $this->redirectToRoute('app_todolist_index', [], Response::HTTP_SEE_OTHER);
    }

        #[Route('/get-tasks-by-todolist/{id}', name: 'get_tasks_by_todolist')]
        public function getTasksByTodoList($id, TaskRepository $taskRepository): Response
        {
            $tasks = $taskRepository->findBy(['idtodo' => $id]);
            if ($tasks) {
                $taskData = array_map(function($task) {
                    return [
                        'id' => $task->getIdtask(),
                        'descriptiontask' => $task->getDescriptiontask(),
                        'mtapayer' => $task->getMtapayer(),
                        'priority' => $task->getPriority(),
                        'duedate' => $task->getDuedate()->format('Y-m-d H:i:s'),
                        'statustask' => $task->getStatustask(),
                    ];
                }, $tasks);
                
                return $this->render('todolist/qr.html.twig', ['tasks' => $taskData]);
            }
            return $this->json(['error' => 'No tasks found for the specified TodoList ID'], Response::HTTP_NOT_FOUND);
        }
    
}
