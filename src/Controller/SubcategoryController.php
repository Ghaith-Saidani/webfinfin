<?php

namespace App\Controller;

use App\Entity\Subcategory;
use App\Form\SubcategoryType;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/subcategory')]
class SubcategoryController extends AbstractController
{

    #[Route('/', name: 'app_subcategory_index', methods: ['GET'])]
    public function index(SubCategoryRepository $subCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $subCategoryRepository->createQueryBuilder('s')
            ->getQuery();
    
        $subcategories = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            2 /*limit per page*/
        );
    
        return $this->render('subcategory/indexc.html.twig', [
            'subcategories' => $subcategories,
        ]);
    }

    #[Route('/new', name: 'app_subcategory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subcategory = new Subcategory();
        $form = $this->createForm(SubcategoryType::class, $subcategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subcategory);
            $entityManager->flush();

            return $this->redirectToRoute('app_subcategory_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('subcategory/new.html.twig', [
            'subcategory' => $subcategory,
            'form' => $form,
        ]);
    }

    #[Route('/{idsubcategory}', name: 'app_subcategory_show', methods: ['GET'])]
    public function show(Subcategory $subcategory): Response
    {    
        
        return $this->render('subcategory/show.html.twig', [
            'subcategory' => $subcategory,
            'mtDépensé' => $subcategory->getMtdepense(),
            'mtAssigné' => $subcategory->getMtassigne()
        ]);
    }

    #[Route('/{idsubcategory}/edit', name: 'app_subcategory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subcategory $subcategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubcategoryType::class, $subcategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_subcategory_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('subcategory/edit.html.twig', [
            'subcategory' => $subcategory,
            'form' => $form,
        ]);
    }

    #[Route('/{idsubcategory}', name: 'app_subcategory_delete', methods: ['POST'])]
    public function delete(Request $request, Subcategory $subcategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subcategory->getIdsubcategory(), $request->request->get('_token'))) {
            $entityManager->remove($subcategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_subcategory_index', [], Response::HTTP_SEE_OTHER);
    }
}
