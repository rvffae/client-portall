<?php

namespace App\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;

class ClientResolver implements QueryInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolveClients()
    {
        return $this->em->getRepository(Client::class)->findAll();
    }

    public function resolveClient($id)
    {
        return $this->em->getRepository(Client::class)->find($id);
    }
}
