<?php

namespace App\Tests\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InvoiceControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $invoiceRepository;
    private string $path = '/invoice/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->invoiceRepository = $this->manager->getRepository(Invoice::class);

        foreach ($this->invoiceRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Invoice index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'invoice[invoice_number]' => 'Testing',
            'invoice[issue_date]' => 'Testing',
            'invoice[due_date]' => 'Testing',
            'invoice[amount]' => 'Testing',
            'invoice[status]' => 'Testing',
            'invoice[created_at]' => 'Testing',
            'invoice[updated_at]' => 'Testing',
            'invoice[project_id]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->invoiceRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Invoice();
        $fixture->setInvoice_number('My Title');
        $fixture->setIssue_date('My Title');
        $fixture->setDue_date('My Title');
        $fixture->setAmount('My Title');
        $fixture->setStatus('My Title');
        $fixture->setCreated_at('My Title');
        $fixture->setUpdated_at('My Title');
        $fixture->setProject_id('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Invoice');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Invoice();
        $fixture->setInvoice_number('Value');
        $fixture->setIssue_date('Value');
        $fixture->setDue_date('Value');
        $fixture->setAmount('Value');
        $fixture->setStatus('Value');
        $fixture->setCreated_at('Value');
        $fixture->setUpdated_at('Value');
        $fixture->setProject_id('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'invoice[invoice_number]' => 'Something New',
            'invoice[issue_date]' => 'Something New',
            'invoice[due_date]' => 'Something New',
            'invoice[amount]' => 'Something New',
            'invoice[status]' => 'Something New',
            'invoice[created_at]' => 'Something New',
            'invoice[updated_at]' => 'Something New',
            'invoice[project_id]' => 'Something New',
        ]);

        self::assertResponseRedirects('/invoice/');

        $fixture = $this->invoiceRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getInvoice_number());
        self::assertSame('Something New', $fixture[0]->getIssue_date());
        self::assertSame('Something New', $fixture[0]->getDue_date());
        self::assertSame('Something New', $fixture[0]->getAmount());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getCreated_at());
        self::assertSame('Something New', $fixture[0]->getUpdated_at());
        self::assertSame('Something New', $fixture[0]->getProject_id());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Invoice();
        $fixture->setInvoice_number('Value');
        $fixture->setIssue_date('Value');
        $fixture->setDue_date('Value');
        $fixture->setAmount('Value');
        $fixture->setStatus('Value');
        $fixture->setCreated_at('Value');
        $fixture->setUpdated_at('Value');
        $fixture->setProject_id('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/invoice/');
        self::assertSame(0, $this->invoiceRepository->count([]));
    }
}
