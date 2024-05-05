<?php

namespace App\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Form\Category1Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Subcategory;
use App\Service\QrCodeGenerator;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(CategoryRepository $categoryRepository): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('category/pdf.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="mypdf.pdf"',
            ]
        );
    }

    #[Route('/stats/{idcategory}', name: 'expense_stats')]
    public function expenseStats(CategoryRepository $categoryRepository, int $idcategory): Response
    {
        $category = $categoryRepository->find($idcategory);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas.');
        }

        $expenseStats = $categoryRepository->getTotalExpensesByCategoryId($idcategory);

        return $this->render('category/stats.html.twig', [
            'category' => $category,
            'expenseStats' => $expenseStats,
        ]);
    }



    #[Route('/stats1/{idcategory}', name: 'expense_stats1')]
    public function categoryStats(CategoryRepository $categoryRepository, int $idcategory): Response
    {
        $category = $categoryRepository->find($idcategory);
    
        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas.');
        }
    
        $subCategoriesData = $categoryRepository->getSubCategoriesExpensesAndBudgetLimit($idcategory);
    
        return $this->render('category/stat.html.twig', [
            'category' => $category,
            'subCategoriesData' => $subCategoriesData,
        ]);
    }
  

    #[Route('/', name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $categoriesQuery = $categoryRepository->createQueryBuilder('c')
            ->getQuery();
    
        $categories = $paginator->paginate(
            $categoriesQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            4 /*limit per page*/
        );
    
        return $this->render('category/indexc.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/c', name: 'app_category_indexc', methods: ['GET'])]
    public function indexc(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/indexc.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/budget', name: 'app_budget')]
    public function budget(): Response
    {
        return $this->render('category/budget.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }
    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(Category1Type::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }
/*
    #[Route('/{idcategory}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }
*/
#[Route('/{idcategory}', name: 'app_category_show', methods: ['GET'])]
public function show(Category $category , CategoryRepository $CategoryRepository , Subcategory $subCategories): Response
{
    // Récupérer les sous-catégories associées à cette catégorie
    $subCategories = $this->getDoctrine()->getRepository(SubCategory::class)->findBy(['idcategory' => $category]);

    return $this->render('category/show.html.twig', [
        'category' => $category,
        'subCategories' => $subCategories,

    ]);
}

    #[Route('/{idcategory}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Category1Type::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{idcategory}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getIdcategory(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
