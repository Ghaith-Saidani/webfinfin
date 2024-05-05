<?php

namespace App\Controller;
use App\Entity\Wishlist;
use DateTime;
use App\Entity\Wishlistitem;
use App\Form\WishlistitemType;
use App\Repository\WishListItemRepository;
use App\Repository\WishListRepository;
use App\Service\PriceAPIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/wishlistitem')]
class WishlistitemController extends AbstractController
{
    #[Route('/', name: 'app_wishlistitem_index', methods: ['GET'])]
    public function index(WishlistItemRepository $wishListItemRepository, MailerInterface $mailer): Response
    {
        $wishlistItems = $wishListItemRepository->findAll();

        foreach ($wishlistItems as $wishlistItem) {
            if ($wishlistItem->getProgress() == 0 && $wishlistItem->isEmailSent()==true)  {
                $email = (new Email())
                    ->from('farah.sayari@esprit.tn')
                    ->to('farah.sayari@esprit.tn')
                    ->subject('Wishlist Item Completed')
                    ->text(sprintf('Wishlist Item "%s" has been completed.', $wishlistItem->getIdwishlistitem()));

                $mailer->send($email);

                // Mettre à jour l'attribut emailSent
                $wishlistItem->setEmailSent(false);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($wishlistItem);
                $entityManager->flush();
            }
        }

        return $this->render('wishlist/index.html.twig', [
            'wishlistItems' => $wishlistItems,
        ]);
    
    }

    #[Route('/new', name: 'app_wishlistitem_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, WishListRepository $wishlistRepository): Response
    {
        // Get the current date and time
        $currentDate = new DateTime();
        $wishlistitem = new Wishlistitem();
        $wishlistitem->setCreationdate($currentDate);
        $wishlistitem->setProgress(0);
        $wishlistitem->setEmailSent(0);
        $form = $this->createForm(WishlistitemType::class, $wishlistitem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($wishlistitem);
            $entityManager->flush();
        
            return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
        }
        

        return $this->renderForm('wishlistitem/new.html.twig', [
            'wishlistitem' => $wishlistitem,
            'form' => $form,
        ]);
    }

    #[Route('/{idwishlistitem}', name: 'app_wishlistitem_show', methods: ['GET'])]
    public function show(Wishlistitem $wishlistitem, WishListRepository $wishlistRepository): Response
    {
        $progress = $this->calculateProgress($wishlistitem, $wishlistRepository);
        $wishlistitem->setProgress($progress);


        return $this->render('wishlistitem/show.html.twig', [
            'wishlistitem' => $wishlistitem,
            'progress' => $progress,

        ]);
    }

    #[Route('/{idwishlistitem}/edit', name: 'app_wishlistitem_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wishlistitem $wishlistitem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WishlistitemType::class, $wishlistitem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // Check if the status is changed to 'DONE'
            if ($wishlistitem->getStatus() === 'DONE') {
                // Subtract item price from the saved budget of the wishlist
                $wishlist = $wishlistitem->getIdwishlist();
                $wishlist->setSavedBudget($wishlist->getSavedBudget() - $wishlistitem->getPrice());
                $this->getDoctrine()->getManager()->flush();
            }

            return $this->redirectToRoute('app_wishlist_index');
        }


        return $this->renderForm('wishlistitem/edit.html.twig', [
            'wishlistitem' => $wishlistitem,
            'form' => $form,
        ]);
    }

    #[Route('/{idwishlistitem}/del', name: 'app_wishlistitem_delete', methods: ['POST'])]
    public function delete(Request $request, Wishlistitem $wishlistitem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wishlistitem->getIdwishlistitem(), $request->request->get('_token'))) {
            $entityManager->remove($wishlistitem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
    }

    

    private function calculateProgress(Wishlistitem $wishlistItem, WishListRepository $wishlistRepository): JsonResponse
{
    $savedBudget = $wishlistItem->getIdwishlist()->getSavedbudget();
    $monthlyBudget = $wishlistItem->getIdwishlist()->getMonthlyBudget();
    $price = $wishlistItem->getPrice();

    $progress = round(($savedBudget / $price) * 100);
    $progress = min($progress, 100);

    return new JsonResponse(['progress' => $progress]);
}

#[Route('/{idwishlistitem}/progress', name: 'app_wishlistitem_progress', methods: ['GET'])]
public function getProgress(Wishlistitem $wishlistItem, WishListRepository $wishlistRepository): JsonResponse
{
    $progress = $this->calculateProgress($wishlistItem, $wishlistRepository);
    return $progress;
}

private function calculateEstimatedPurchaseDate(Wishlistitem $wishlistItem): \DateTime
{
    $savedBudget = $wishlistItem->getIdwishlist()->getSavedbudget();
    $monthlyBudget = $wishlistItem->getIdwishlist()->getMonthlyBudget();
    $price = $wishlistItem->getPrice();

    // Calculate the remaining amount needed to purchase the item
    $remainingAmount = $price - $savedBudget;
    // Calculate the number of months needed to save enough money
    $monthsNeeded = ceil($remainingAmount / $monthlyBudget);
    $currentDate = new \DateTime();
    $estimatedPurchaseDate = $currentDate->add(new \DateInterval("P{$monthsNeeded}M"));

    return $estimatedPurchaseDate;
}
#[Route('/{idwishlistitem}/purchase_date', name: 'app_wishlistitem_purchase_date', methods: ['GET'])]
public function getEstimatedPurchaseDate(Wishlistitem $wishlistItem, WishListRepository $wishlistRepository): JsonResponse
{
    $estimatedPurchaseDate = $this->calculateEstimatedPurchaseDate($wishlistItem);
    return new JsonResponse(['estimated_purchase_date' => $estimatedPurchaseDate->format('Y-m-d')]);
}

#[Route('/{idwishlistitem}/item/price', name: 'wishlist_item_price', methods: ['GET'])]
    public function getPrice(PriceAPIService $priceAPIService, WishlistItem $wishlistItem): JsonResponse
    {
        $term = $wishlistItem->getNamewishlistitem();
        $jobResponse = $priceAPIService->postJob($term);


        if (!$jobResponse) {
            return $this->json(['error' => 'Failed to submit job.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $jobId = json_decode($jobResponse, true)['job_id'];

        // Wait for 6 seconds before fetching the results
        sleep(8);

        $priceAndAvailability = $priceAPIService->fetchPriceAndAvailability($jobId);

        if (!$priceAndAvailability) {
            return $this->json(['error' => 'Failed to fetch price and availability.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return $this->json(['price_and_availability' => $priceAndAvailability]);
    }



}
