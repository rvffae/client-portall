<?php

namespace App\Service;

use Google\Client;
use Google\Service\Calendar;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class GoogleCalendarService
{
    private Client $client;
    private RequestStack $requestStack;
    private LoggerInterface $logger;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct(
        RequestStack $requestStack,
        LoggerInterface $logger,
        string $googleClientId,
        string $googleClientSecret,
        string $googleRedirectUri
    ) {
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->clientId = $googleClientId;
        $this->clientSecret = $googleClientSecret;
        $this->redirectUri = $googleRedirectUri;
        
        $this->initializeClient();
    }

private function initializeClient(): void
{
    $this->client = new Client();
    $this->client->setClientId($this->clientId);
    $this->client->setClientSecret($this->clientSecret);
    $this->client->setRedirectUri($this->redirectUri);
    
    // IMPORTANT: Ajouter les scopes nécessaires pour créer/modifier/supprimer
    $this->client->addScope([
        Calendar::CALENDAR,           // Accès complet au calendrier
        Calendar::CALENDAR_EVENTS,    // Accès complet aux événements
        // Retire les scopes "READONLY" qui limitent les permissions
    ]);
    
    $this->client->setAccessType('offline');
    $this->client->setPrompt('select_account consent');
    $this->client->setApprovalPrompt('force'); // Force la re-autorisation
}

    private function getSession()
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request ? $request->getSession() : null;
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate(string $code): bool
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            
            if (isset($token['error'])) {
                $this->logger->error('Google Calendar authentication error: ' . $token['error_description']);
                return false;
            }

            $session = $this->getSession();
            if ($session) {
                $session->set('google_calendar_token', $token);
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Google Calendar authentication exception: ' . $e->getMessage());
            return false;
        }
    }

    public function isAuthenticated(): bool
    {
        $session = $this->getSession();
        if (!$session) {
            return false;
        }

        $token = $session->get('google_calendar_token');
        if (!$token) {
            return false;
        }

        $this->client->setAccessToken($token);
        
        if ($this->client->isAccessTokenExpired()) {
            if (isset($token['refresh_token'])) {
                try {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
                    $session->set('google_calendar_token', $newToken);
                    return true;
                } catch (\Exception $e) {
                    $this->logger->error('Token refresh failed: ' . $e->getMessage());
                    return false;
                }
            }
            return false;
        }

        return true;
    }

    public function getUpcomingEvents(int $maxResults = 10): array
    {
        if (!$this->isAuthenticated()) {
            return [];
        }

        try {
            $service = new Calendar($this->client);
            
            $optParams = [
                'maxResults' => $maxResults,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => date('c'),
            ];

            $results = $service->events->listEvents('primary', $optParams);
            $events = $results->getItems();

            $formattedEvents = [];
            foreach ($events as $event) {
                $start = $event->start->dateTime ?: $event->start->date;
                $end = $event->end->dateTime ?: $event->end->date;
                
                $formattedEvents[] = [
                    'id' => $event->id,
                    'title' => $event->summary ?: 'Sans titre',
                    'description' => $event->description,
                    'start' => $start,
                    'end' => $end,
                    'location' => $event->location,
                    'htmlLink' => $event->htmlLink,
                    'isAllDay' => !$event->start->dateTime,
                ];
            }

            return $formattedEvents;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching Google Calendar events: ' . $e->getMessage());
            return [];
        }
    }

    public function getTodayEvents(): array
    {
        if (!$this->isAuthenticated()) {
            return [];
        }

        try {
            $service = new Calendar($this->client);
            
            $timeMin = date('Y-m-d') . 'T00:00:00' . date('P');
            $timeMax = date('Y-m-d') . 'T23:59:59' . date('P');
            
            $optParams = [
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => $timeMin,
                'timeMax' => $timeMax,
            ];

            $results = $service->events->listEvents('primary', $optParams);
            $events = $results->getItems();

            $formattedEvents = [];
            foreach ($events as $event) {
                $start = $event->start->dateTime ?: $event->start->date;
                
                $formattedEvents[] = [
                    'id' => $event->id,
                    'title' => $event->summary ?: 'Sans titre',
                    'start' => $start,
                    'location' => $event->location,
                    'isAllDay' => !$event->start->dateTime,
                ];
            }

            return $formattedEvents;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching today\'s events: ' . $e->getMessage());
            return [];
        }
    }

    public function disconnect(): void
    {
        $session = $this->getSession();
        if ($session) {
            $session->remove('google_calendar_token');
        }
    }

    public function createEvent(array $eventData): array
{
    if (!$this->isAuthenticated()) {
        return ['success' => false, 'message' => 'Non authentifié'];
    }

    try {
        $service = new Calendar($this->client);
        
        $event = new \Google\Service\Calendar\Event();
        $event->setSummary($eventData['title']);
        
        if (!empty($eventData['description'])) {
            $event->setDescription($eventData['description']);
        }
        
        if (!empty($eventData['location'])) {
            $event->setLocation($eventData['location']);
        }
        
        // Gestion des dates
        $start = new \Google\Service\Calendar\EventDateTime();
        $end = new \Google\Service\Calendar\EventDateTime();
        
        if (isset($eventData['all_day']) && $eventData['all_day']) {
            // Événement toute la journée
            $start->setDate($eventData['start_date']);
            $end->setDate($eventData['end_date'] ?: $eventData['start_date']);
        } else {
            // Événement avec heure
            $startDateTime = $eventData['start_date'] . 'T' . ($eventData['start_time'] ?: '09:00:00');
            $endDateTime = ($eventData['end_date'] ?: $eventData['start_date']) . 'T' . ($eventData['end_time'] ?: '10:00:00');
            
            $start->setDateTime($startDateTime);
            $start->setTimeZone('Europe/Paris');
            
            $end->setDateTime($endDateTime);
            $end->setTimeZone('Europe/Paris');
        }
        
        $event->setStart($start);
        $event->setEnd($end);
        
        $createdEvent = $service->events->insert('primary', $event);
        
        return [
            'success' => true,
            'message' => 'Événement créé avec succès',
            'eventId' => $createdEvent->getId()
        ];
    } catch (\Exception $e) {
        $this->logger->error('Error creating Google Calendar event: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erreur lors de la création de l\'événement: ' . $e->getMessage()
        ];
    }
}

public function updateEvent(string $eventId, array $eventData): array
{
    if (!$this->isAuthenticated()) {
        return ['success' => false, 'message' => 'Non authentifié'];
    }

    try {
        $service = new Calendar($this->client);
        
        // Récupérer l'événement existant
        $event = $service->events->get('primary', $eventId);
        
        // Mettre à jour les champs
        if (isset($eventData['title'])) {
            $event->setSummary($eventData['title']);
        }
        
        if (isset($eventData['description'])) {
            $event->setDescription($eventData['description']);
        }
        
        if (isset($eventData['location'])) {
            $event->setLocation($eventData['location']);
        }
        
        // Mettre à jour les dates si fournies
        if (isset($eventData['start_date'])) {
            $start = new \Google\Service\Calendar\EventDateTime();
            $end = new \Google\Service\Calendar\EventDateTime();
            
            if (isset($eventData['all_day']) && $eventData['all_day']) {
                $start->setDate($eventData['start_date']);
                $end->setDate($eventData['end_date'] ?: $eventData['start_date']);
            } else {
                $startDateTime = $eventData['start_date'] . 'T' . ($eventData['start_time'] ?: '09:00:00');
                $endDateTime = ($eventData['end_date'] ?: $eventData['start_date']) . 'T' . ($eventData['end_time'] ?: '10:00:00');
                
                $start->setDateTime($startDateTime);
                $start->setTimeZone('Europe/Paris');
                
                $end->setDateTime($endDateTime);
                $end->setTimeZone('Europe/Paris');
            }
            
            $event->setStart($start);
            $event->setEnd($end);
        }
        
        $updatedEvent = $service->events->update('primary', $eventId, $event);
        
        return [
            'success' => true,
            'message' => 'Événement modifié avec succès',
            'eventId' => $updatedEvent->getId()
        ];
    } catch (\Exception $e) {
        $this->logger->error('Error updating Google Calendar event: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erreur lors de la modification de l\'événement: ' . $e->getMessage()
        ];
    }
}

public function deleteEvent(string $eventId): array
{
    if (!$this->isAuthenticated()) {
        return ['success' => false, 'message' => 'Non authentifié'];
    }

    try {
        $service = new Calendar($this->client);
        $service->events->delete('primary', $eventId);
        
        return [
            'success' => true,
            'message' => 'Événement supprimé avec succès'
        ];
    } catch (\Exception $e) {
        $this->logger->error('Error deleting Google Calendar event: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erreur lors de la suppression de l\'événement: ' . $e->getMessage()
        ];
    }
}
}