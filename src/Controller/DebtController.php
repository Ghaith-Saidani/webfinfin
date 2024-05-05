<?php

namespace App\Controller;

use App\Entity\Debt;
use App\Form\DebtType;
use App\Repository\DebtRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Wallet;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;

use Twilio\Rest\Client;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\BarChart;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;





#[Route('/debt')]
class DebtController extends AbstractController
{
    #[Route('/', name: 'app_debt_index', methods: ['GET'])]
    public function index(DebtRepository $debtRepository, PaginatorInterface $paginator, FlashyNotifier $flashy, Request $request): Response
    {
        // Fetch debts due within 7 days
        $dueInSevenDays = $debtRepository->findDebtsDueInSevenDays();

        // Set a warning flash message if there are debts due within 7 days
        if (!empty($dueInSevenDays)) {
            $notificationMessage = '';
            // Loop through each debt with a delay between notifications
            foreach ($dueInSevenDays as $debt) {
                // Create the notification message
                $notificationMessage .= sprintf(
                    '*Debt ID: %d *Amount Left to Pay: %s \n\n',
                    $debt->getIddebt(),
                    $debt->getAmounttopay()
                );

                // Flashy notification
                $flashy->error($notificationMessage);
            }
        }

        // Get all debts query from the repository
        $allDebtsQuery = $debtRepository->createQueryBuilder('d')
            ->getQuery();

        // Paginate the query
        $pagination = $paginator->paginate(
            $allDebtsQuery, // Query to paginate
            $request->query->getInt('page', 1), // Get current page from request, default to 1
            5 // Items per page
        );

        return $this->render('debt/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_debt_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $idWallet = 1; // Assuming you're always fetching the wallet with id = 1
        // Fetch the Wallet entity with id = 1
        $wallet = $entityManager->getRepository(Wallet::class)->find($idWallet);
        if (!$wallet) {
            throw $this->createNotFoundException('Wallet not found');
        }

        $debt = new Debt();
        $debt->setIdwallet($wallet);

        $form = $this->createForm(DebtType::class, $debt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($debt);
            $entityManager->flush();
            return $this->redirectToRoute('app_debt_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('debt/new.html.twig', [
            'debt' => $debt,
            'form' => $form,
        ]);
    }

    #[Route('/{iddebt}/edit', name: 'app_debt_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Debt $debt, EntityManagerInterface $entityManager): Response
    {
        // Fetch the Wallet entity with id = 1
        $wallet = $entityManager->getRepository(Wallet::class)->find(1);

        if (!$wallet) {
            throw $this->createNotFoundException('Wallet not found');
        }

        // Set the idwallet for the Debt entity
        $debt->setIdwallet($wallet);

        $form = $this->createForm(DebtType::class, $debt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_debt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('debt/edit.html.twig', [
            'debt' => $debt,
            'form' => $form,
        ]);
    }

    #[Route('/{iddebt}', name: 'app_debt_delete', methods: ['POST'])]
    public function delete(Request $request, Debt $debt, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $debt->getIddebt(), $request->request->get('_token'))) {
            $entityManager->remove($debt);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_debt_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search_debt', name: 'search_debt', methods: ['GET'])]
    public function searchByType(Request $request): JsonResponse
    {
        // Retrieve the search term from the query parameter
        $type = $request->query->get('iddebt');

        $debts = $this->getDoctrine()
            ->getRepository(Debt::class)
            ->findBy(['iddebt' => $type]);

        // Fetch debts by type from the database using the modified repository method
        //$debts = $debtRepository->findBySearchTerm($type);

        // Return the debts as JSON response
        return $this->json($debts);
    }

    #[Route('/send-sms', name: 'app_debt_send_sms', methods: ['GET'])]
    public function sendSms(ManagerRegistry $registry): Response
    {
        // Get the EntityManager
        $entityManager = $registry->getManager();

        // Fetch debts that have about 7 days left until the payment date
        $debts = $entityManager->getRepository(Debt::class)->findDebtsDueInSevenDays();

        // Initialize Twilio client
        $sid = 'ACcf0d7a71992b3d51ea8474bd04133b11';
        $token = '14e34801cd6c32a93849891d544ae504';
        $twilio = new Client($sid, $token);

        // Send SMS notifications for each debt
        foreach ($debts as $debt) {
            // Calculate remaining days until payment date
            $paymentDate = $debt->getPaymentDate();
            $now = new \DateTime();
            $diff = $paymentDate->diff($now)->days;

            // Prepare message body with debt details
            $messageBody = sprintf(
                'Reminder: Your debt with ID %d is due in %d days.',
                $debt->getIdDebt(),
                $diff
            );

            // Send SMS
            $message = $twilio->messages
                ->create(
                    "+21655289219", // recipient phone number
                    [
                        'from' => '+14122285739',
                        'body' => $messageBody
                    ]
                );
            // Log the message or handle any errors
        }

        // Redirect back to the debt index page
        return $this->redirectToRoute('app_debt_index');
    }

    #[Route('/deposit/{iddebt}', name: 'deposit_page', methods: ['GET', 'POST'])]
    public function deposit(Debt $debt, Request $request, EntityManagerInterface $entityManager,FlashyNotifier $flashy): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $amount = $request->request->get('amount');

            // Validate amount
            $validator = Validation::createValidator();
            $violations = $validator->validate($amount, [
                new Assert\NotBlank(),
                new Assert\Type('numeric'),
                new Assert\GreaterThan(0)
            ]);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
            } else {
                // Your logic to check if wallet has sufficient funds
                $walletBalance = $debt->getIdwallet()->getTotalbalance();
                if ($amount > $walletBalance) {
                    $errors[] = 'Insufficient funds in wallet.';
                }

                // Your logic to check if deposit amount exceeds amount to pay
                $amountToPay = $debt->getAmounttopay();
                if ($amount > $amountToPay) {
                    $errors[] = 'Deposit amount exceeds amount to pay for this debt.';
                }

                // If no errors, update the amount to pay
                if (empty($errors)) {
                    $newAmountToPay = $amountToPay - $amount;
                    $newWalletBalance = $walletBalance - $amount;
                    $debt->setAmounttopay($newAmountToPay);
                    $debt->getIdwallet()->setTotalbalance($newWalletBalance);
                    $entityManager->flush();

                    $flashy->success('Deposit successful!');
                    //$this->addFlash('success', 'Deposit successful!');
                    return $this->redirectToRoute('app_debt_priority');
                }
            }
        }

        return $this->render('debt/show.html.twig', [
            'debt' => $debt,
            'errors' => $errors,
        ]);
    }

    /*
    #[Route('/Stat', name: 'app_debt_stat')]
    public function stat(DebtRepository $debtRepository): Response
    {
        // Get data for the chart
        $debtData = $debtRepository->getDebtDataForChart();

        // Format the data for Chart.js
        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Amount',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                    'data' => [],
                ],
            ],
        ];

        foreach ($debtData as $row) {
            $chartData['labels'][] = $row['type'];
            $chartData['datasets'][0]['data'][] = $row['totalAmount'];
        }

        // Render the template with the chart data
        return $this->render('debt/stat.html.twig', [
            'debtChartData' => json_encode($chartData),
        ]);
    }
    */
    #[Route('/Stat', name: 'app_debt_stat')]
    public function stat(DebtRepository $debtRepository): Response
    {
        // Get data for the chart
        $debtData = $debtRepository->getDebtDataForChart();

        // Create a BarChart object
        $barChart = new BarChart();

        // Set chart options
        $barChart->getOptions()->setTitle('Debt Distribution');
        $barChart->getOptions()->getChartArea()->setWidth('50%'); // Adjust as needed

        // Add data to the chart
        $barChart->getData()->setArrayToDataTable([
            ['Type', 'Total Amount'],
            // Add data rows
            ...array_map(fn ($row) => [$row['type'], $row['totalAmount']], $debtData)
        ]);

        // Render the chart
        return $this->render('debt/stat.html.twig', [
            'barChart' => $barChart,
        ]);
    }

    #[Route('/Excel', name: 'app_debt_excel')]
    public function excel(): Response
    {
    // Create a new XLSX writer object
    $writer = WriterEntityFactory::createXLSXWriter();

    // Specify the directory where you want to save the Excel file
    //$directoryPath = 'C:\\Users\\User\\OneDrive\\Bureau\\ProjetPiDevWeb\\templates\\debt\\';
    $filePath = __DIR__ . '\DebtData.xlsx';

    // Open the file for writing
    $writer->openToFile($filePath);

    // Add headers to the Excel file
    $writer->addRow(WriterEntityFactory::createRowFromArray(['Iddebt', 'Amount', 'Paymentdate', 'Amounttopay', 'Interestrate', 'Creationdate', 'Type']));

    // Get debt data from your repository or any other source
    $debtData = $this->getDoctrine()->getRepository(Debt::class)->findAll();

    // Add data rows to the Excel file
    foreach ($debtData as $debt) {
        $row = WriterEntityFactory::createRowFromArray([
            $debt->getIddebt(),
            $debt->getAmount(),
            $debt->getPaymentdate()->format('Y-m-d'),
            $debt->getAmounttopay(),
            $debt->getInterestrate(),
            $debt->getCreationdate()->format('Y-m-d'),
            $debt->getType()->getNameDebt(), // Assuming you want the name of the type
        ]);
        $writer->addRow($row);
    }

    // Close the writer
    $writer->close();

    // Create a Symfony Response with the Excel file content
    $response = new Response(file_get_contents($filePath));


    // Set headers for Excel file download
    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="debt_data.xlsx"');

    return $response;
    }
    
    #[Route('/priority', name: 'app_debt_priority')]
    public function prioritizeDebt(PaginatorInterface $paginator,Request $request): Response
    {
        // Fetch debt data from repository
        $debtData = $this->getDoctrine()->getRepository(Debt::class)->findAll();

        // Sort debts by priority score in descending order
        usort($debtData, function($a, $b) {
            // Calculate priority score for $a and $b
            $priorityScoreA = $this->calculatePriorityScore($a);
            $priorityScoreB = $this->calculatePriorityScore($b);

            // Compare priority scores
            return $priorityScoreB - $priorityScoreA;
        });

        // Paginate the sorted debt data
        $pagination = $paginator->paginate(
            $debtData, // Query to paginate
            $request->query->getInt('page', 1), // Get current page from request, default to 1
            5 // Items per page
        );

        // Return the prioritized list of debts with pagination
        return $this->render('debt/priority.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    private function calculatePriorityScore(Debt $debt): float
    {
        // Get current date
        $currentDate = new \DateTime();

        // Calculate the number of days left until the payment date
        $paymentDate = $debt->getPaymentDate();
        $daysLeft = $currentDate->diff($paymentDate)->days;

        // Define weights for different factors
        $interestRateWeight = 0.5;
        $amountToPayWeight = 0.3;
        $timeLeftWeight = 0.2;

        // Calculate priority score based on factors
        $priorityScore = $debt->getInterestRate() * $interestRateWeight + 
                        $debt->getAmounttopay() * $amountToPayWeight +
                        $daysLeft * $timeLeftWeight;

        return $priorityScore;
    }

}