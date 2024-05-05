<?php

namespace App\Controller;

use DateTime;
use App\Entity\Wishlist;
use App\Entity\Wishlistitem;
use App\Entity\Itemcategory;
use App\Entity\Wallet;
use App\Form\WishlistType;
use App\Repository\WishListRepository;
use App\Repository\WishListItemRepository;
use App\Repository\WalletRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Psr\Log\LoggerInterface;


use Symfony\Component\Routing\Annotation\Route;

class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'app_wishlist_index', methods: ['GET'])]
    public function index(FlashyNotifier $flashyNotifier, PaginatorInterface $paginator, Request $request, FlashyNotifier $flashy): Response
    {
        $entityManager = $this->getDoctrine()->getManager();


        // Fetch wishlists from the database
        $wishlists = $entityManager->getRepository(Wishlist::class)->findAll();

        // For each wishlist, fetch its monthly and saved budgets
        foreach ($wishlists as $wishlist) {
            // Assuming you have a method in your Wishlist entity to get the monthly budget
            $monthlyBudget = $wishlist->getMonthlyBudget();

            // Assuming you have a method in your Wishlist entity to get the saved budget
            $savedBudget = $wishlist->getSavedBudget();

            // Store the budgets in the wishlist object for easy access in Twig
            $wishlist->monthlyBudget = $monthlyBudget;
            $wishlist->savedBudget = $savedBudget;
        }
        // Fetch wishlist items from the database
        $wishlistItems = $entityManager->getRepository(WishlistItem::class)->findAll();

        // Fetch item categories from the database
        $itemcategoriesQuery = $entityManager->getRepository(Itemcategory::class)->createQueryBuilder('c');

        // Filter categories based on the page number
        $page = $request->query->getInt('page', 1);
        $itemcategories = $paginator->paginate(
            $itemcategoriesQuery->getQuery(),
            $page,
            2
        );

        // If it's an Ajax request, return only the partial view
        if ($request->isXmlHttpRequest()) {
            return $this->render('itemcategory/index.html.twig', [
                'itemcategories' => $itemcategories,
            ]);
        }

        $wishlistItems = $paginator->paginate(
            $wishlistItems, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );


        // If it's not an Ajax request, return the full view
        return $this->render('wishlist/index.html.twig', [
            'wishlists' => $wishlists,
            'wishlistItems' => $wishlistItems,
            'itemcategories' => $itemcategories,
            'includeDeleteForm' => true, // or false based on your logic
        ]);
    }

    #[Route('/wishlist/new', name: 'app_wishlist_new', methods: ['GET', 'POST'])]
    public function new(WalletRepository $walletRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Fetch the logged-in user
        $userid = 2;
        // Get the current date and time
        $currentDate = new DateTime();
        // Fetch the idWallet of the logged-in user
        $wallet = $walletRepository->getWalletByUserId($userid);
        $wishlist = new Wishlist();
        $wishlist->setIdWallet($wallet);
        $wishlist->setCreationdate($currentDate);
        $wishlist->setSavedbudget(0);
        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($wishlist);
                $entityManager->flush();

                return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                // Handle unique constraint violation (wishlist name already exists)
                $this->addFlash('error', 'The wishlist name already exists.');
                dump($e->getMessage()); // Output the error message

                return $this->redirectToRoute('app_wishlist_new');
            }
        }

        return $this->renderForm('wishlist/new.html.twig', [
            'wishlist' => $wishlist,
            'form' => $form,
            'error' => $this->get('session')->getFlashBag()->get('error'), // Pass the flash message to the Twig template

        ]);
    }

    #[Route('/wishlist/{idwishlist}', name: 'app_wishlist_show', methods: ['GET'])]
    public function show(Wishlist $wishlist): Response
    {
        return $this->render('wishlist/show.html.twig', [
            'wishlist' => $wishlist,
        ]);
    }

    #[Route('/wishlist/{idwishlist}/edit', name: 'app_wishlist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wishlist $wishlist, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('wishlist/edit.html.twig', [
            'wishlist' => $wishlist,
            'form' => $form,
        ]);
    }

    #[Route('/wishlist/{idwishlist}/del', name: 'app_wishlist_delete', methods: ['POST'])]
    public function delete(Request $request, Wishlist $wishlist, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $wishlist->getIdwishlist(), $request->request->get('_token'))) {
            $entityManager->remove($wishlist);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wishlist_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/wishlist/{idwishlist}/deleteform', name: 'app_wishlist_deleteform', methods: ['GET'])]

    public function getDeleteFormContent(Request $request): Response
    {
        // Render the delete form template and return its content as a response
        $deleteForm = $this->renderView('wishlistitem/_delete_form.html.twig');
        return new Response($deleteForm, 200, ['Content-Type' => 'text/html']);
    }


    #[Route('/wishlist/{id}/items', name: 'wishlist_items_ajax')]
    public function getWishlistItemsAjax($id): JsonResponse
    {
        // Fetch wishlist items associated with the given wishlist ID
        $wishlistItems = $this->getDoctrine()->getRepository(WishlistItem::class)->findBy(['idwishlist' => $id]);

        // Serialize wishlist items
        $serializedItems = [];
        foreach ($wishlistItems as $item) {
            $serializedItems[] = [
                'idwishlistitem' => $item->getIdwishlistitem(),
                'namewishlistitem' => $item->getNamewishlistitem(),
                'price' => $item->getPrice(),
                'creationdate' => $item->getCreationDate()->format('Y-m-d'), // Format creation date as string
                'priority' => $item->getPriority(),
                'status' => $item->getStatus(),
                'progress' => $item->getProgress(),


                // Add more fields if needed
            ];
        }

        // Return JSON response with serialized wishlist items
        return new JsonResponse($serializedItems);
    }

    #[Route('/wishlist/items/{wishlistId}', name: 'wishlist_items')]
    public function fetchItems($wishlistId): JsonResponse
    {
        // Retrieve wishlist items from the database based on $wishlistId
        $entityManager = $this->getDoctrine()->getManager();
        $wishlistItems = $entityManager->getRepository(WishlistItem::class)->findBy(['wishlist' => $wishlistId]);

        // Convert wishlist items to an array (or adjust as needed)
        $wishlistItemsArray = [];

        foreach ($wishlistItems as $item) {
            $wishlistItemsArray[] = [
                'idwishlistitem' => $item->getIdwishlistitem(),
                'namewishlistitem' => $item->getNamewishlistitem(),
                'price' => $item->getPrice(),
                'creationDate' => $item->getCreationDate()->format('Y-m-d'), // Format creation date
                'priority' => $item->getPriority(),
                'status' => $item->getStatus(),
                'progress' => $item->getProgress(),
                // Add more fields as needed
            ];
        }

        // Return wishlist items as a JSON response
        return new JsonResponse($wishlistItemsArray);
    }

    #[Route('/wishlist/{wishlistId}/items_with_progress', name: 'app_wishlist_items_with_progress', methods: ['GET'])]
    public function getWishlistItemsWithProgress(int $wishlistId, MailerInterface $mailer, WishListRepository $wishlistRepository, WishListItemRepository $wishListItemRepository, \Twig\Environment $twig, PaginatorInterface $paginator, Request $request): JsonResponse
    {

        $wishlistItems = $wishListItemRepository->findBy(['idwishlist' => $wishlistId]);


        $wishlistItems = $paginator->paginate(
            $wishlistItems, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

        $wishlistItems = $wishlistItems->getItems();

        foreach ($wishlistItems as $wishlistItem) {
            $progress = $this->calculateProgress($wishlistItem, $wishlistRepository);
            $estimatedPurchaseDate = $this->calculateEstimatedPurchaseDate($wishlistItem);

            // Update progress in database
            $wishlistItem->setProgress($progress);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wishlistItem);
            $entityManager->flush();

            if ($progress < 100 && $wishlistItem->isEmailSent() == true) {
                // Update the emailSent attribute
                $wishlistItem->setEmailSent(false);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($wishlistItem);
                $entityManager->flush();
            }

            // Send email notification if item is completed
            if ($progress == 100 && $wishlistItem->isEmailSent() == false) {
                $email = (new Email())
                    ->from('farah.sayari@esprit.tn')
                    ->to('farah.sayari@esprit.tn')
                    ->subject('Wishlist Item Completed')
                    ->html($twig->render('email/wishlist_item_completed.html.twig', [
                        'wishlistItemName' => $wishlistItem->getNamewishlistitem(),
                    ]));
                $mailer->send($email);

                // Update the emailSent attribute
                $wishlistItem->setEmailSent(true);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($wishlistItem);
                $entityManager->flush();
            }
        }


        // Now fetch wishlist items with progress
        $wishlistItemsWithProgress = [];
        foreach ($wishlistItems as $wishlistItem) {
            $progress = $this->calculateProgress($wishlistItem, $wishlistRepository);
            $estimatedPurchaseDate = $this->calculateEstimatedPurchaseDate($wishlistItem);
            $deleteForm = $this->renderView('wishlistitem/_delete_form.html.twig', [
                'wishlistitem' => $wishlistItem,
            ]);

            $wishlistItemsWithProgress[] = [
                'idwishlistitem' => $wishlistItem->getIdwishlistitem(),
                'namewishlistitem' => $wishlistItem->getNamewishlistitem(),
                'price' => $wishlistItem->getPrice(),
                'creationdate' => $wishlistItem->getCreationdate()->format('Y-m-d'),
                'priority' => $wishlistItem->getPriority(),
                'status' => $wishlistItem->getStatus(),
                'progress' => $progress,
                'estimated_purchase_date' => $estimatedPurchaseDate->format('Y-m-d'),
                'deleteForm' => $deleteForm,
            ];
        }


        return new JsonResponse($wishlistItemsWithProgress);
    }


    private function calculateProgress(Wishlistitem $wishlistItem, WishListRepository $wishlistRepository): int
    {
        $savedBudget = $wishlistItem->getIdwishlist()->getSavedbudget();
        $monthlyBudget = $wishlistItem->getIdwishlist()->getMonthlyBudget();
        $price = $wishlistItem->getPrice();

        $progress = round(($savedBudget / $price) * 100);
        $progress = min($progress, 100);

        return $progress;
    }

    private function calculateEstimatedPurchaseDate(Wishlistitem $wishlistItem): \DateTime
    {
        $savedBudget = $wishlistItem->getIdwishlist()->getSavedbudget();
        $monthlyBudget = $wishlistItem->getIdwishlist()->getMonthlyBudget();
        $price = $wishlistItem->getPrice();

        // Calculate the remaining amount needed to purchase the item
        $remainingAmount = $price - $savedBudget;

        if ($remainingAmount <= 0) {
            return new \DateTime(); // Return the current date if the remaining amount is non-positive
        }

        // Calculate the number of months needed to save enough money
        $monthsNeeded = ceil($remainingAmount / $monthlyBudget);

        $currentDate = new \DateTime();
        $estimatedPurchaseDate = $currentDate->add(new \DateInterval("P{$monthsNeeded}M"));

        return $estimatedPurchaseDate;
    }

    #[Route('/wishlist/{idwishlist}/assign_monthly_budget', name: 'app_wishlist_assign_monthly_budget', methods: ['POST'])]
    public function assignMonthlyBudget(FlashyNotifier $flashyNotifier, int $idwishlist, WalletRepository $walletRepository, WishlistRepository $wishlistRepository): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Fetch wishlist
        $wishlist = $wishlistRepository->find($idwishlist);

        // Fetch the logged-in user
        $userid = 2;

        // Fetch the idWallet of the logged-in user
        $wallet = $walletRepository->getWalletByUserId($userid);

        // Fetch wallet
        // Get total balance and monthly budget
        $totalBalance = $wallet->getTotalbalance();
        $monthlyBudget = $wishlist->getMonthlyBudget();
        $savedBudget = $wishlist->getSavedbudget();

        // Output debug information
        dump("Total Balance: $totalBalance, Monthly Budget: $monthlyBudget");

        // Update total balance and saved budget
        if ($totalBalance >= $monthlyBudget) {
            $totalBalance -= $monthlyBudget;
            $savedBudget = $wishlist->getSavedBudget() + $monthlyBudget;
            $wishlist->setSavedBudget($savedBudget);
            $wallet->setTotalbalance($totalBalance);
            $entityManager->persist($wallet);
            $entityManager->flush();

            $flashyNotifier->success('Monthly budget assigned successfully!');
        } else {
            $flashyNotifier->warning('Your total balance is insufficient to cover the monthly budget.');
        }



        return $this->json([
            'savedBudget' => $savedBudget, 'monthlyBudget' => $monthlyBudget,
        ]);
    }


    #[Route('/statistics/{idwishlist}', name: 'app_statistics')]
    public function stat(WishlistItemRepository $wishlistItemRepository, int $idwishlist): Response
    {
        // Get the count of completed and remaining wishlist items
        $completedItemCount = $wishlistItemRepository->getCompletedItemCount($idwishlist);
        $remainingItemCount = $wishlistItemRepository->getRemainingItemCount($idwishlist);

        return $this->json([
            'completedItemCount' => $completedItemCount,
            'remainingItemCount' => $remainingItemCount,
        ]);
    }
}
