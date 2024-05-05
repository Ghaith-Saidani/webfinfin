<?php

namespace App\Controller;

use App\Entity\Itemcategory;
use App\Form\ItemcategoryType;
use App\Repository\ItemCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemcategoryController extends AbstractController
{
    #[Route('/itemcategory', name: 'app_itemcategory_index', methods: ['GET'])]
    public function index(ItemCategoryRepository $itemCategoryRepository, Request $request, PaginatorInterface $paginator): Response
    {   
        $itemcategories = $itemCategoryRepository->findAll();
        $itemcategories = $paginator->paginate(
            $itemcategories,
            $request->query->getInt('page',1),
            2
        );

        return $this->render('itemcategory/index.html.twig', [
            'itemcategories' => $itemcategories,
        ]);
    }

    #[Route('/itemcategory/new', name: 'app_itemcategory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $itemcategory = new Itemcategory();
        $form = $this->createForm(ItemcategoryType::class, $itemcategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($itemcategory);
            $entityManager->flush();

            return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('itemcategory/new.html.twig', [
            'itemcategory' => $itemcategory,
            'form' => $form,
        ]);
    }

    #[Route('/itemcategory/{iditemcategory}', name: 'app_itemcategory_show', methods: ['GET'])]
    public function show(Itemcategory $itemcategory): Response
    {
        return $this->render('itemcategory/show.html.twig', [
            'itemcategory' => $itemcategory,
        ]);
    }

    #[Route('/itemcategory/{iditemcategory}/edit', name: 'app_itemcategory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Itemcategory $itemcategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ItemcategoryType::class, $itemcategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('itemcategory/edit.html.twig', [
            'itemcategory' => $itemcategory,
            'form' => $form,
        ]);
    }

    #[Route('/itemcategory/{iditemcategory}', name: 'app_itemcategory_delete', methods: ['POST'])]
    public function delete(Request $request, Itemcategory $itemcategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$itemcategory->getIditemcategory(), $request->request->get('_token'))) {
            $entityManager->remove($itemcategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_itemcategory_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
