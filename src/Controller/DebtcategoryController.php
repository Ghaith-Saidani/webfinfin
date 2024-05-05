<?php

namespace App\Controller;

use App\Entity\Debtcategory;
use App\Form\DebtcategoryType;
use App\Repository\DebtCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Constraints as Assert;
use Knp\Component\Pager\PaginatorInterface;

use Doctrine\DBAL\Connection;

#[Route('/debtcategory')]
class DebtcategoryController extends AbstractController
{
    #[Route('/', name: 'app_debtcategory_index', methods: ['GET'])]
    public function index(DebtCategoryRepository $debtCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $debtCategoryRepository->findAll();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

        return $this->render('debtcategory/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_debtcategory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Connection $connection): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $nameDebt = $request->request->get('NameDebt');

            if (empty($nameDebt)) {
                $errors[] = "NameDebt field cannot be empty.";
            } else {
                $sql = 'INSERT INTO debtcategory (NameDebt) VALUES (:nameDebt)';
                $statement = $connection->prepare($sql);
                $statement->bindValue('nameDebt', $nameDebt);
                $statement->execute();

                // Redirect to the index page
                return $this->redirectToRoute('app_debtcategory_index');
            }
        }

        return $this->render('debtcategory/new.html.twig', [
            'errors' => $errors,
        ]);
    }
    /*
        #[Route('/new', name: 'app_debtcategory_new', methods: ['GET', 'POST'])]
        public function new(Request $request, EntityManagerInterface $entityManager): Response
        {
            // Create a new Debtcategory entity
            $debtcategory = new Debtcategory();

            // Set the NameDebt property directly
            $debtcategory->setNameDebt('Your Desired Name Here');

            // Persist and flush the entity
            $entityManager->persist($debtcategory);
            $entityManager->flush();

            // Redirect to the index page
            return $this->redirectToRoute('app_debtcategory_index');
        }
    */ 












    #[Route('/{NameDebt}', name: 'app_debtcategory_show', methods: ['GET'])]
    public function show(Debtcategory $debtcategory): Response
    {
        return $this->render('debtcategory/show.html.twig', [
            'debtcategory' => $debtcategory,
        ]);
    }

    #[Route('/{NameDebt}/edit', name: 'app_debtcategory_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Debtcategory $debtcategory, Connection $connection): Response
{
    // Check if the request is a POST request
    if ($request->isMethod('POST')) {
        // Get the value of NameDebt from the form submission
        $nameDebt = $request->request->get('NameDebt');

        // Validate the submitted data
        $errors = $this->validateFormData($nameDebt);

        if (!empty($errors)) {
            // Render the edit form template with validation errors
            return $this->render('debtcategory/edit.html.twig', [
                'debtcategory' => $debtcategory,
                'currentNameDebt' => $debtcategory->getNameDebt(),
                'errors' => $errors,
            ]);
        }

        // Execute custom SQL query to update the debtcategory table
        $sql = '
            UPDATE debtcategory
            SET NameDebt = :nameDebt
            WHERE NameDebt = :currentNameDebt
        ';
        $statement = $connection->prepare($sql);
        $statement->bindValue('nameDebt', $nameDebt);
        $statement->bindValue('currentNameDebt', $debtcategory->getNameDebt());
        $statement->execute();

        // Redirect to the index page
        return $this->redirectToRoute('app_debtcategory_index');
    }

    // Render the edit form template and pass the current NameDebt value
    return $this->render('debtcategory/edit.html.twig', [
        'debtcategory' => $debtcategory,
        'currentNameDebt' => $debtcategory->getNameDebt(),
    ]);
}

private function validateFormData($nameDebt): array
{
    $errors = [];

    if (empty($nameDebt)) {
        $errors[] = 'NameDebt cannot be empty.';
    }

    return $errors;
}

    #[Route('/{NameDebt}', name: 'app_debtcategory_delete', methods: ['POST'])]
    public function delete(Request $request, Debtcategory $debtcategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $debtcategory->getNamedebt(), $request->request->get('_token'))) {
            $entityManager->remove($debtcategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_debtcategory_index', [], Response::HTTP_SEE_OTHER);
    }
    
}