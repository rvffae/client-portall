<?php

namespace App\Service;

use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;

class DashboardService
{
    private $clientRepository;
    private $invoiceRepository;
    private $entityManager;

    public function __construct(
        ClientRepository $clientRepository,
        InvoiceRepository $invoiceRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->clientRepository = $clientRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->entityManager = $entityManager;
    }

    public function getDashboardStats(): array
    {
        $currentYear = (int) date('Y');
        $lastYear = $currentYear - 1;

        return [
            'clients' => $this->getClientsStats($currentYear, $lastYear),
            'revenue' => $this->getRevenueStats($currentYear, $lastYear),
            'invoices' => $this->getInvoicesStats($currentYear, $lastYear),
            'conversion_rate' => $this->getConversionRateStats()
        ];
    }

    private function getClientsStats(int $currentYear, int $lastYear): array
    {
        $totalClients = $this->clientRepository->count([]);
        $clientsThisYear = $this->getClientsCountByYear($currentYear);
        $clientsLastYear = $this->getClientsCountByYear($lastYear);
        $growthPercent = $this->calculateGrowthPercent($clientsThisYear, $clientsLastYear);

        return [
            'count' => $totalClients,
            'growth_percent' => $growthPercent,
            'is_positive' => $growthPercent >= 0
        ];
    }

    private function getRevenueStats(int $currentYear, int $lastYear): array
    {
        $totalRevenue = $this->invoiceRepository->getTotalRevenue();
        $revenueThisYear = $this->invoiceRepository->getRevenueByYear($currentYear);
        $revenueLastYear = $this->invoiceRepository->getRevenueByYear($lastYear);
        $growthPercent = $this->calculateGrowthPercent($revenueThisYear, $revenueLastYear);

        return [
            'amount' => $totalRevenue,
            'growth_percent' => $growthPercent,
            'is_positive' => $growthPercent >= 0
        ];
    }

    private function getInvoicesStats(int $currentYear, int $lastYear): array
    {
        $totalInvoices = $this->invoiceRepository->count([]);
        $invoicesThisYear = $this->invoiceRepository->getInvoicesCountByYear($currentYear);
        $invoicesLastYear = $this->invoiceRepository->getInvoicesCountByYear($lastYear);
        $growthPercent = $this->calculateGrowthPercent($invoicesThisYear, $invoicesLastYear);

        return [
            'count' => $totalInvoices,
            'growth_percent' => $growthPercent,
            'is_positive' => $growthPercent >= 0
        ];
    }

    private function getConversionRateStats(): array
    {
        $totalClients = $this->clientRepository->count([]);
        $totalInvoices = $this->invoiceRepository->count([]);
        
        $conversionRate = $totalClients > 0 ? round(($totalInvoices / $totalClients) * 100, 1) : 0;
        
        // Pour la croissance du taux de conversion, vous pouvez implémenter une logique plus complexe
        // ici on utilise un exemple simple
        $conversionRateGrowth = 0.5;

        return [
            'rate' => $conversionRate,
            'growth_percent' => $conversionRateGrowth,
            'is_positive' => $conversionRateGrowth >= 0
        ];
    }

    private function getClientsCountByYear(int $year): int
    {
        $startDate = new \DateTime($year . '-01-01');
        $endDate = new \DateTime($year . '-12-31 23:59:59');

        return $this->clientRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.created_at BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function calculateGrowthPercent($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}