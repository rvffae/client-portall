<?php

namespace App\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserResolver implements QueryInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolveUsers()
    {
        return $this->em->getRepository(User::class)->findAll();
    }

    public function resolveUser($id)
    {
        return $this->em->getRepository(User::class)->find($id);
    }
}
