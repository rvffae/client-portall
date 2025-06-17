<?php

namespace App\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;

class CompanyResolver implements QueryInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolveCompanies()
    {
        return $this->em->getRepository(Company::class)->findAll();
    }

    public function resolveCompany($id)
    {
        return $this->em->getRepository(Company::class)->find($id);
    }
}
