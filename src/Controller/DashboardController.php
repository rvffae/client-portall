<?php

namespace App\Controller;

use App\Service\GoogleCalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private GoogleCalendarService $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
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

        return $this->render('home/dashboard.html.twig', [
            'isCalendarConnected' => $isCalendarConnected,
            'todayEvents' => $todayEvents,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}