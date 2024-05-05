<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;
use App\Repository\UserRepository;

class UserRegistrationTest extends KernelTestCase

{
    private $entityManager;
    private $userRepository;

   
    public function testRegistration()
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->entityManager->getRepository(User::class);

        // Simulate a POST request to the registration endpoint with form data
       
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john@example.com');
        $user->setPassword('password123'); // Assuming user id is 1

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Assert that the response is a redirect to the expected success page
        
        // Follow the redirect and check if the user was successfully registered
        

        // Retrieve the user from the database to ensure it was successfully saved
        $savedUser = $this->userRepository->findOneBy(['email' => 'john@example.com']);

        $this->assertInstanceOf(User::class, $savedUser);
        $this->assertEquals('John', $savedUser->getFirstName());
        $this->assertEquals('Doe', $savedUser->getLastName());
        $this->assertEquals('john@example.com', $savedUser->getEmail());
        // Add more assertions as necessary
    }
}
