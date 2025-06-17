<?php

namespace App\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class ProjectResolver implements QueryInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolveProjects()
    {
        return $this->em->getRepository(Project::class)->findAll();
    }

    public function resolveProject($id)
    {
        return $this->em->getRepository(Project::class)->find($id);
    }
}
