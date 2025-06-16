<?php

namespace App\Tests\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $projectRepository;
    private string $path = '/project/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->projectRepository = $this->manager->getRepository(Project::class);

        foreach ($this->projectRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Project index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'project[name]' => 'Testing',
            'project[description]' => 'Testing',
            'project[start_date]' => 'Testing',
            'project[end_date]' => 'Testing',
            'project[status]' => 'Testing',
            'project[budget]' => 'Testing',
            'project[created_at]' => 'Testing',
            'project[updated_at]' => 'Testing',
            'project[company_id]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->projectRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Project();
        $fixture->setName('My Title');
        $fixture->setDescription('My Title');
        $fixture->setStart_date('My Title');
        $fixture->setEnd_date('My Title');
        $fixture->setStatus('My Title');
        $fixture->setBudget('My Title');
        $fixture->setCreated_at('My Title');
        $fixture->setUpdated_at('My Title');
        $fixture->setCompany_id('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Project');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Project();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setStart_date('Value');
        $fixture->setEnd_date('Value');
        $fixture->setStatus('Value');
        $fixture->setBudget('Value');
        $fixture->setCreated_at('Value');
        $fixture->setUpdated_at('Value');
        $fixture->setCompany_id('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'project[name]' => 'Something New',
            'project[description]' => 'Something New',
            'project[start_date]' => 'Something New',
            'project[end_date]' => 'Something New',
            'project[status]' => 'Something New',
            'project[budget]' => 'Something New',
            'project[created_at]' => 'Something New',
            'project[updated_at]' => 'Something New',
            'project[company_id]' => 'Something New',
        ]);

        self::assertResponseRedirects('/project/');

        $fixture = $this->projectRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getStart_date());
        self::assertSame('Something New', $fixture[0]->getEnd_date());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getBudget());
        self::assertSame('Something New', $fixture[0]->getCreated_at());
        self::assertSame('Something New', $fixture[0]->getUpdated_at());
        self::assertSame('Something New', $fixture[0]->getCompany_id());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Project();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setStart_date('Value');
        $fixture->setEnd_date('Value');
        $fixture->setStatus('Value');
        $fixture->setBudget('Value');
        $fixture->setCreated_at('Value');
        $fixture->setUpdated_at('Value');
        $fixture->setCompany_id('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/project/');
        self::assertSame(0, $this->projectRepository->count([]));
    }
}
