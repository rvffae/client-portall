<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * Récupère le chiffre d'affaires mensuel
     */
    public function getMonthlyRevenue(): array
    {
        // Récupérer toutes les factures avec date et montant
        $invoices = $this->createQueryBuilder('i')
            ->select('i.issue_date, i.amount')
            ->where('i.issue_date IS NOT NULL')
            ->andWhere('i.amount IS NOT NULL')
            ->andWhere('i.amount > 0')
            ->orderBy('i.issue_date', 'ASC')
            ->getQuery()
            ->getResult();

        // Grouper par mois manuellement en PHP
        $monthlyData = [];
        foreach ($invoices as $invoice) {
            $date = $invoice['issue_date'];
            if ($date instanceof \DateTime) {
                $year = (int) $date->format('Y');
                $month = (int) $date->format('n');
                $key = $year . '-' . $month;
                
                if (!isset($monthlyData[$key])) {
                    $monthlyData[$key] = [
                        'year' => $year,
                        'month' => $month,
                        'total_amount' => 0
                    ];
                }
                $monthlyData[$key]['total_amount'] += $invoice['amount'];
            }
        }

        // Trier par année puis mois
        ksort($monthlyData);
        
        return array_values($monthlyData);
    }

    /**
     * Récupère le chiffre d'affaires par année
     */
    public function getYearlyRevenue(): array
    {
        // Récupérer toutes les factures avec date et montant
        $invoices = $this->createQueryBuilder('i')
            ->select('i.issue_date, i.amount')
            ->where('i.issue_date IS NOT NULL')
            ->andWhere('i.amount IS NOT NULL')
            ->andWhere('i.amount > 0')
            ->orderBy('i.issue_date', 'ASC')
            ->getQuery()
            ->getResult();

        // Grouper par année manuellement en PHP
        $yearlyData = [];
        foreach ($invoices as $invoice) {
            $date = $invoice['issue_date'];
            if ($date instanceof \DateTime) {
                $year = (int) $date->format('Y');
                
                if (!isset($yearlyData[$year])) {
                    $yearlyData[$year] = [
                        'year' => $year,
                        'total_amount' => 0
                    ];
                }
                $yearlyData[$year]['total_amount'] += $invoice['amount'];
            }
        }

        // Trier par année
        ksort($yearlyData);
        
        return array_values($yearlyData);
    }

    /**
     * NOUVELLE MÉTHODE : Récupère le chiffre d'affaires par entreprise
     */
    public function getCompanyRevenue(): array
    {
        $qb = $this->createQueryBuilder('i')
            ->select('c.name as company_name, SUM(i.amount) as total_amount')
            ->innerJoin('i.project', 'p')
            ->innerJoin('p.companies', 'c')
            ->where('i.amount IS NOT NULL')
            ->andWhere('i.amount > 0')
            ->groupBy('c.id', 'c.name')
            ->orderBy('total_amount', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les statistiques générales
     */
    public function getRevenueStats(): array
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id) as total_invoices, SUM(i.amount) as total_revenue, AVG(i.amount) as avg_amount')
            ->where('i.amount IS NOT NULL')
            ->andWhere('i.amount > 0');

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Récupère le chiffre d'affaires des N derniers mois
     */
    public function getRevenueLastMonths(int $months = 12): array
    {
        $date = new \DateTime();
        $date->modify("-{$months} months");

        // Récupérer toutes les factures des derniers mois
        $invoices = $this->createQueryBuilder('i')
            ->select('i.issue_date, i.amount')
            ->where('i.issue_date >= :date')
            ->andWhere('i.issue_date IS NOT NULL')
            ->andWhere('i.amount IS NOT NULL')
            ->andWhere('i.amount > 0')
            ->setParameter('date', $date)
            ->orderBy('i.issue_date', 'ASC')
            ->getQuery()
            ->getResult();

        // Grouper par mois manuellement en PHP
        $monthlyData = [];
        foreach ($invoices as $invoice) {
            $invoiceDate = $invoice['issue_date'];
            if ($invoiceDate instanceof \DateTime) {
                $year = (int) $invoiceDate->format('Y');
                $month = (int) $invoiceDate->format('n');
                $key = $year . '-' . $month;
                
                if (!isset($monthlyData[$key])) {
                    $monthlyData[$key] = [
                        'year' => $year,
                        'month' => $month,
                        'total_amount' => 0
                    ];
                }
                $monthlyData[$key]['total_amount'] += $invoice['amount'];
            }
        }

        // Trier par année puis mois
        ksort($monthlyData);
        
        return array_values($monthlyData);
    }
}