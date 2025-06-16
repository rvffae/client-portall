<?php

namespace App\Tests\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContactControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $contactRepository;
    private string $path = '/contact/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->contactRepository = $this->manager->getRepository(Contact::class);

        foreach ($this->contactRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Contact index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'contact[contact_type]' => 'Testing',
            'contact[contact_detail]' => 'Testing',
            'contact[created_at]' => 'Testing',
            'contact[updated_at]' => 'Testing',
            'contact[client_id]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->contactRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Contact();
        $fixture->setContact_type('My Title');
        $fixture->setContact_detail('My Title');
        $fixture->setCreated_at('My Title');
        $fixture->setUpdated_at('My Title');
        $fixture->setClient_id('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Contact');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Contact();
        $fixture->setContact_type('Value');
        $fixture->setContact_detail('Value');
        $fixture->setCreated_at('Value');
        $fixture->setUpdated_at('Value');
        $fixture->setClient_id('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'contact[contact_type]' => 'Something New',
            'contact[contact_detail]' => 'Something New',
            'contact[created_at]' => 'Something New',
            'contact[updated_at]' => 'Something New',
            'contact[client_id]' => 'Something New',
        ]);

        self::assertResponseRedirects('/contact/');

        $fixture = $this->contactRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getContact_type());
        self::assertSame('Something New', $fixture[0]->getContact_detail());
        self::assertSame('Something New', $fixture[0]->getCreated_at());
        self::assertSame('Something New', $fixture[0]->getUpdated_at());
        self::assertSame('Something New', $fixture[0]->getClient_id());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Contact();
        $fixture->setContact_type('Value');
        $fixture->setContact_detail('Value');
        $fixture->setCreated_at('Value');
        $fixture->setUpdated_at('Value');
        $fixture->setClient_id('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/contact/');
        self::assertSame(0, $this->contactRepository->count([]));
    }
}
