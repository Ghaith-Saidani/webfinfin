<?php

namespace App\Test\Controller;

use App\Entity\Wishlistitem;
use App\Repository\WishListItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WishlistitemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private WishListItemRepository $repository;
    private string $path = '/wishlistitem/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Wishlistitem::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wishlistitem index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'wishlistitem[namewishlistitem]' => 'Testing',
            'wishlistitem[price]' => 'Testing',
            'wishlistitem[creationdate]' => 'Testing',
            'wishlistitem[priority]' => 'Testing',
            'wishlistitem[progress]' => 'Testing',
            'wishlistitem[status]' => 'Testing',
            'wishlistitem[emailSent]' => 'Testing',
            'wishlistitem[iditemcategory]' => 'Testing',
            'wishlistitem[idwishlist]' => 'Testing',
        ]);

        self::assertResponseRedirects('/wishlistitem/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Wishlistitem();
        $fixture->setNamewishlistitem('My Title');
        $fixture->setPrice('My Title');
        $fixture->setCreationdate('My Title');
        $fixture->setPriority('My Title');
        $fixture->setProgress('My Title');
        $fixture->setStatus('My Title');
        $fixture->setEmailSent('My Title');
        $fixture->setIditemcategory('My Title');
        $fixture->setIdwishlist('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wishlistitem');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Wishlistitem();
        $fixture->setNamewishlistitem('My Title');
        $fixture->setPrice('My Title');
        $fixture->setCreationdate('My Title');
        $fixture->setPriority('My Title');
        $fixture->setProgress('My Title');
        $fixture->setStatus('My Title');
        $fixture->setEmailSent('My Title');
        $fixture->setIditemcategory('My Title');
        $fixture->setIdwishlist('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'wishlistitem[namewishlistitem]' => 'Something New',
            'wishlistitem[price]' => 'Something New',
            'wishlistitem[creationdate]' => 'Something New',
            'wishlistitem[priority]' => 'Something New',
            'wishlistitem[progress]' => 'Something New',
            'wishlistitem[status]' => 'Something New',
            'wishlistitem[emailSent]' => 'Something New',
            'wishlistitem[iditemcategory]' => 'Something New',
            'wishlistitem[idwishlist]' => 'Something New',
        ]);

        self::assertResponseRedirects('/wishlistitem/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNamewishlistitem());
        self::assertSame('Something New', $fixture[0]->getPrice());
        self::assertSame('Something New', $fixture[0]->getCreationdate());
        self::assertSame('Something New', $fixture[0]->getPriority());
        self::assertSame('Something New', $fixture[0]->getProgress());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getEmailSent());
        self::assertSame('Something New', $fixture[0]->getIditemcategory());
        self::assertSame('Something New', $fixture[0]->getIdwishlist());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Wishlistitem();
        $fixture->setNamewishlistitem('My Title');
        $fixture->setPrice('My Title');
        $fixture->setCreationdate('My Title');
        $fixture->setPriority('My Title');
        $fixture->setProgress('My Title');
        $fixture->setStatus('My Title');
        $fixture->setEmailSent('My Title');
        $fixture->setIditemcategory('My Title');
        $fixture->setIdwishlist('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/wishlistitem/');
    }
}
