<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Subcategory;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/task')]
class TaskController extends AbstractController
{
    #[Route('/', name: 'app_task_index', methods: ['GET'])]
public function index(TaskRepository $taskRepository, MailerInterface $mailer, PaginatorInterface $paginator, Request $request): Response
{
    // Retrieve all tasks
    $tasks = $taskRepository->findAll();

    // Paginate the results
    $pagination = $paginator->paginate(
        $tasks, // Query results
        $request->query->getInt('page', 1), // Current page number, default to 1
        5 // Number of items per page
    );

    // Send reminder emails for tasks due in two days
    $tasksDueInTwoDays = $taskRepository->findTasksDueInTwoDays();
    foreach ($tasksDueInTwoDays as $task) {
        $email = (new TemplatedEmail())
            ->from('rabie.zerrim@esprit.tn')
            ->to('rabiezerrim@gmail.com')
            ->subject('Task Due Reminder')
            ->htmlTemplate('task/task_due_reminder.html.twig') // Set the HTML template!!
            ->context([
                'task' => $task,
                'listName' => $task->getIdtodo()->getTitletodo(), // Add list name to the context
            ]);

        $mailer->send($email);
    }

    return $this->render('task/index.html.twig', [
        'pagination' => $pagination,
    ]);
    }


    #[Route('/get-mtassigne/{id}', name: 'get_mtassigne')]
    public function getMtAssigné($id, SubCategoryRepository $subcategoryRepository): Response
    {
        $subcategory = $subcategoryRepository->find($id);
            if ($subcategory) {
            $mtAssigné = $subcategory->getMtAssigne();
                return $this->json(['mtAssigne' => $mtAssigné]);
        }
            return $this->json(['error' => 'Subcategory not found'], Response::HTTP_NOT_FOUND);
    }
    
    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getIdtask(), $request->request->get('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }




}