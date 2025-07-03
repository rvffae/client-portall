<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use App\Service\GoogleCalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private GoogleCalendarService $calendarService;
    private ClientRepository $clientRepository;
    private InvoiceRepository $invoiceRepository;

    public function __construct(
        GoogleCalendarService $calendarService,
        ClientRepository $clientRepository,
        InvoiceRepository $invoiceRepository
    ) {
        $this->calendarService = $calendarService;
        $this->clientRepository = $clientRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        $isCalendarConnected = $this->calendarService->isAuthenticated();
        $todayEvents = [];
        $upcomingEvents = [];

        if ($isCalendarConnected) {
            $todayEvents = $this->calendarService->getTodayEvents();
            $upcomingEvents = $this->calendarService->getUpcomingEvents(5);
        }

        // Calcul des statistiques
        $stats = $this->calculateDashboardStats();

        return $this->render('home/dashboard.html.twig', [
            'isCalendarConnected' => $isCalendarConnected,
            'todayEvents' => $todayEvents,
            'upcomingEvents' => $upcomingEvents,
            'stats' => $stats,
        ]);
    }

    private function calculateDashboardStats(): array
    {
        // Nombre total de clients
        $totalClients = $this->clientRepository->count([]);
        
        // Nombre de clients l'année dernière (même date l'année précédente)
        $lastYear = new \DateTime('-1 year');
        $clientsLastYear = $this->clientRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.created_at <= :lastYear')
            ->setParameter('lastYear', $lastYear)
            ->getQuery()
            ->getSingleScalarResult();

        // Calcul du pourcentage d'augmentation des clients
        $clientGrowthPercent = $clientsLastYear > 0 
            ? round((($totalClients - $clientsLastYear) / $clientsLastYear) * 100, 1)
            : 100;

        // Chiffre d'affaires total (somme de tous les montants des factures)
        $totalRevenue = $this->invoiceRepository->createQueryBuilder('i')
            ->select('COALESCE(SUM(i.amount), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        // Chiffre d'affaires de l'année en cours
        $currentYearStart = new \DateTime('first day of January this year');
        $currentYearRevenue = $this->invoiceRepository->createQueryBuilder('i')
            ->select('COALESCE(SUM(i.amount), 0)')
            ->where('i.created_at >= :currentYearStart')
            ->setParameter('currentYearStart', $currentYearStart)
            ->getQuery()
            ->getSingleScalarResult();

        // Chiffre d'affaires de l'année dernière
        $lastYearStart = new \DateTime('first day of January last year');
        $lastYearEnd = new \DateTime('last day of December last year');
        $lastYearRevenue = $this->invoiceRepository->createQueryBuilder('i')
            ->select('COALESCE(SUM(i.amount), 0)')
            ->where('i.created_at >= :lastYearStart')
            ->andWhere('i.created_at <= :lastYearEnd')
            ->setParameter('lastYearStart', $lastYearStart)
            ->setParameter('lastYearEnd', $lastYearEnd)
            ->getQuery()
            ->getSingleScalarResult();

        // Calcul du pourcentage d'évolution du chiffre d'affaires
        $revenueGrowthPercent = $lastYearRevenue > 0 
            ? round((($currentYearRevenue - $lastYearRevenue) / $lastYearRevenue) * 100, 1)
            : ($currentYearRevenue > 0 ? 100 : 0);

        // Calculer l'évolution mensuelle pour la troisième carte
        $currentMonth = new \DateTime('first day of this month');
        $lastMonth = new \DateTime('first day of last month');
        $lastMonthEnd = new \DateTime('last day of last month');

        $currentMonthRevenue = $this->invoiceRepository->createQueryBuilder('i')
            ->select('COALESCE(SUM(i.amount), 0)')
            ->where('i.created_at >= :currentMonth')
            ->setParameter('currentMonth', $currentMonth)
            ->getQuery()
            ->getSingleScalarResult();

        $lastMonthRevenue = $this->invoiceRepository->createQueryBuilder('i')
            ->select('COALESCE(SUM(i.amount), 0)')
            ->where('i.created_at >= :lastMonth')
            ->andWhere('i.created_at <= :lastMonthEnd')
            ->setParameter('lastMonth', $lastMonth)
            ->setParameter('lastMonthEnd', $lastMonthEnd)
            ->getQuery()
            ->getSingleScalarResult();

        $monthlyGrowthPercent = $lastMonthRevenue > 0 
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($currentMonthRevenue > 0 ? 100 : 0);

        return [
            'totalClients' => $totalClients,
            'clientGrowthPercent' => $clientGrowthPercent,
            'totalRevenue' => $currentYearRevenue, // Chiffre d'affaires de l'année en cours
            'revenueGrowthPercent' => $revenueGrowthPercent,
            'monthlyGrowthPercent' => $monthlyGrowthPercent,
        ];
    }
}