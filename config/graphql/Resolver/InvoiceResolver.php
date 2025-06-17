<?php

namespace App\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceResolver implements QueryInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function resolveInvoices(): array
    {
        return $this->em->getRepository(Invoice::class)->findAll();
    }

    public function resolveInvoice($id)
    {
        return $this->em->getRepository(Invoice::class)->find($id);
    }
}