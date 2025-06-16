<?php

namespace App\Tests\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ClientControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $clientRepository;
    private string $path = '/client/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->clientRepository = $this->manager->getRepository(Client::class);

        foreach ($this->clientRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Client index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'client[first_name]' => 'Testing',
            'client[last_name]' => 'Testing',
            'client[email]' => 'Testing',
            'client[phone]' => 'Testing',
            'client[adress]' => 'Testing',
            'client[city]' => 'Testing',
            'client[state]' => 'Testing',
            'client[zip_code]' => 'Testing',
            'client[country]' => 'Testing',
            'client[created_at]' => 'Testing',
            'client[updated_at]' => 'Testing',
            'client[company_id]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->clientRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setFirst_name('My Title');
        $fixture->setLast_name('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPhone('My Title');
        $fixture->setAdress('My Title');
        $fixture->setCity('My Title');
        $fixture->setState('My Title');
        $fixture->setZip_code('My Title');
        $fixture->setCountry('My Title');
        $fixture->setCreated_at('My Title');
        $fixture->setUpdated_at('My Title');
        $fixture->setCompany_id('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Client');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setFirst_name('Value');
        $fixture->setLast_name('Value');
        $fixture->setEmail('Value');
        $fixture->setPhone('Value');
        $fixture->setAdress('Value');
        $fixture->setCity('Value');
        $fixture->setState('Value');
        $fixture->setZip_code('Value');
        $fixture->setCountry('Value');
        $fixture->setCreated_at('Value');
        $fixture->setUpdated_at('Value');
        $fixture->setCompany_id('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'client[first_name]' => 'Something New',
            'client[last_name]' => 'Something New',
            'client[email]' => 'Something New',
            'client[phone]' => 'Something New',
            'client[adress]' => 'Something New',
            'client[city]' => 'Something New',
            'client[state]' => 'Something New',
            'client[zip_code]' => 'Something New',
            'client[country]' => 'Something New',
            'client[created_at]' => 'Something New',
            'client[updated_at]' => 'Something New',
            'client[company_id]' => 'Something New',
        ]);

        self::assertResponseRedirects('/client/');

        $fixture = $this->clientRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getFirst_name());
        self::assertSame('Something New', $fixture[0]->getLast_name());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPhone());
        self::assertSame('Something New', $fixture[0]->getAdress());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getState());
        self::assertSame('Something New', $fixture[0]->getZip_code());
        self::assertSame('Something New', $fixture[0]->getCountry());
        self::assertSame('Something New', $fixture[0]->getCreated_at());
        self::assertSame('Something New', $fixture[0]->getUpdated_at());
        self::assertSame('Something New', $fixture[0]->getCompany_id());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setFirst_name('Value');
        $fixture->setLast_name('Value');
        $fixture->setEmail('Value');
        $fixture->setPhone('Value');
        $fixture->setAdress('Value');
        $fixture->setCity('Value');
        $fixture->setState('Value');
        $fixture->setZip_code('Value');
        $fixture->setCountry('Value');
        $fixture->setCreated_at('Value');
        $fixture->setUpdated_at('Value');
        $fixture->setCompany_id('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/client/');
        self::assertSame(0, $this->clientRepository->count([]));
    }
}
