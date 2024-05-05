<?php

namespace App\Command;

use App\Repository\DebtRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateInterestCommand extends Command
{
    protected static $defaultName = 'app:calculate-interest';

    private $entityManager;
    private $debtRepository;

    public function __construct(EntityManagerInterface $entityManager, DebtRepository $debtRepository)
    {
        $this->entityManager = $entityManager;
        $this->debtRepository = $debtRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Calculates interest for overdue debts.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get the current date
        $currentDate = new \DateTime();

        // Get the debts from the repository
        $debts = $this->debtRepository->findOverdueDebts($currentDate);

        // Calculate interest for each overdue debt
        foreach ($debts as $debt) {
            $interestRate = $debt->getInterestRate();
            $amountToPay = $debt->getAmountToPay();

            // Calculate the new amount to pay after applying the interest rate
            $newAmountToPay = $amountToPay * (1 + $interestRate / 100);
            $debt->setAmountToPay($newAmountToPay);

            // Get the current payment date
            $currentPaymentDate = clone $debt->getPaymentDate(); // Clone the current payment date

            // Calculate the new payment date after applying the interest rate
            $newPaymentDate = $currentPaymentDate->add(new \DateInterval('P1M')); // Add one month
            $debt->setPaymentDate($newPaymentDate);
        }

        // Persist all changes to the database
        $this->entityManager->flush();

        // Log the success message
        $output->writeln('Interest calculated for overdue debts.');

        return Command::SUCCESS;
    }


}