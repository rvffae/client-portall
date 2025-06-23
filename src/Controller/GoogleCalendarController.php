<?php

namespace App\Controller;

use App\Service\GoogleCalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/google-calendar', name: 'google_calendar_')]
class GoogleCalendarController extends AbstractController
{
    private GoogleCalendarService $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    #[Route('/auth', name: 'auth')]
    public function authenticate(): Response
    {
        $authUrl = $this->calendarService->getAuthUrl();
        return $this->redirect($authUrl);
    }

    #[Route('/callback', name: 'callback')]
    public function callback(Request $request): Response
    {
        $code = $request->query->get('code');
        
        if (!$code) {
            $this->addFlash('error', 'Erreur lors de l\'authentification Google Calendar');
            return $this->redirectToRoute('app_dashboard');
        }

        if ($this->calendarService->authenticate($code)) {
            $this->addFlash('success', 'Google Calendar connecté avec succès !');
        } else {
            $this->addFlash('error', 'Erreur lors de la connexion à Google Calendar');
        }

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/events', name: 'events', methods: ['GET'])]
    public function getEvents(): JsonResponse
    {
        if (!$this->calendarService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $events = $this->calendarService->getUpcomingEvents(20);
        
        return new JsonResponse([
            'success' => true,
            'events' => $events
        ]);
    }

    #[Route('/today-events', name: 'today_events', methods: ['GET'])]
    public function getTodayEvents(): JsonResponse
    {
        if (!$this->calendarService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $events = $this->calendarService->getTodayEvents();
        
        return new JsonResponse([
            'success' => true,
            'events' => $events
        ]);
    }

    #[Route('/disconnect', name: 'disconnect')]
    public function disconnect(): Response
    {
        $this->calendarService->disconnect();
        $this->addFlash('success', 'Google Calendar déconnecté');
        
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/status', name: 'status', methods: ['GET'])]
    public function getStatus(): JsonResponse
    {
        return new JsonResponse([
            'authenticated' => $this->calendarService->isAuthenticated()
        ]);
    }

    #[Route('/add-event', name: 'add_event', methods: ['POST'])]
public function addEvent(Request $request): JsonResponse
{
    if (!$this->calendarService->isAuthenticated()) {
        return new JsonResponse(['success' => false, 'message' => 'Non authentifié'], 401);
    }

    $data = json_decode($request->getContent(), true);
    
    if (!$data) {
        return new JsonResponse(['success' => false, 'message' => 'Données invalides'], 400);
    }

    // Validation des champs requis
    if (empty($data['title']) || empty($data['start_date'])) {
        return new JsonResponse(['success' => false, 'message' => 'Titre et date de début requis'], 400);
    }

    $result = $this->calendarService->createEvent($data);
    
    return new JsonResponse($result);
}

#[Route('/update-event/{eventId}', name: 'update_event', methods: ['PUT'])]
public function updateEvent(string $eventId, Request $request): JsonResponse
{
    if (!$this->calendarService->isAuthenticated()) {
        return new JsonResponse(['success' => false, 'message' => 'Non authentifié'], 401);
    }

    $data = json_decode($request->getContent(), true);
    
    if (!$data) {
        return new JsonResponse(['success' => false, 'message' => 'Données invalides'], 400);
    }

    $result = $this->calendarService->updateEvent($eventId, $data);
    
    return new JsonResponse($result);
}

#[Route('/delete-event/{eventId}', name: 'delete_event', methods: ['DELETE'])]
public function deleteEvent(string $eventId): JsonResponse
{
    if (!$this->calendarService->isAuthenticated()) {
        return new JsonResponse(['success' => false, 'message' => 'Non authentifié'], 401);
    }

    $result = $this->calendarService->deleteEvent($eventId);
    
    return new JsonResponse($result);
}

}