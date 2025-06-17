<?php

namespace App\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;

class ContactResolver implements QueryInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolveContacts(): array
    {
        return $this->em->getRepository(Contact::class)->findAll();
    }

    public function resolveContact($id) 
    {
        return $this->em->getRepository(Contact::class)->find($id);
    }

}
