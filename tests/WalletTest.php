<?php

use PHPUnit\Framework\TestCase;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;




class WalletRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::$container->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(Wallet::class);
    }

    public function testAddWallet()
    {
        $wallet = new Wallet();
        $wallet->setName('My Wallet');
        $wallet->setCurrency('USD');
        $wallet->setTotalBalance(100.0);
        $wallet->setIdUser(1); // Assuming user id is 1

        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        // Retrieve the wallet from the database to ensure it was successfully saved
        $savedWallet = $this->repository->findOneBy(['name' => 'My Wallet']);

        $this->assertInstanceOf(Wallet::class, $savedWallet);
        $this->assertEquals('My Wallet', $savedWallet->getName());
        $this->assertEquals('USD', $savedWallet->getCurrency());
        $this->assertEquals(100.0, $savedWallet->getTotalBalance());
        $this->assertEquals(1, $savedWallet->getIdUser());
    }
}



