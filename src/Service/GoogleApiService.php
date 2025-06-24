<?php

namespace App\Service;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\HangoutsChat;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class GoogleApiService
{
    private Client $client;
    private RequestStack $requestStack;
    private LoggerInterface $logger;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    
    private const SCOPES = [
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/gmail.modify',
        'https://www.googleapis.com/auth/chat.messages',
        'https://www.googleapis.com/auth/chat.spaces'
    ];

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
        $this->client->addScope(self::SCOPES);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        $this->client->setApprovalPrompt('force');
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
                $this->logger->error('Google API authentication error: ' . $token['error_description']);
                return false;
            }

            $session = $this->getSession();
            if ($session) {
                $session->set('google_api_token', $token);
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Google API authentication exception: ' . $e->getMessage());
            return false;
        }
    }

    public function isAuthenticated(): bool
    {
        $session = $this->getSession();
        if (!$session) {
            return false;
        }

        $token = $session->get('google_api_token');
        if (!$token) {
            return false;
        }

        $this->client->setAccessToken($token);
        
        if ($this->client->isAccessTokenExpired()) {
            if (isset($token['refresh_token'])) {
                try {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
                    $session->set('google_api_token', $newToken);
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

    public function getGmailMessages(int $maxResults = 10): array
    {
        if (!$this->isAuthenticated()) {
            return [];
        }

        try {
            $service = new Gmail($this->client);
            
            // Récupérer la liste des messages
            $messages = $service->users_messages->listUsersMessages('me', [
                'maxResults' => $maxResults,
                'q' => 'is:unread' // Optionnel: seulement les non-lus
            ]);

            $formattedMessages = [];
            
            if ($messages->getMessages()) {
                foreach ($messages->getMessages() as $message) {
                    $messageDetails = $service->users_messages->get('me', $message->getId());
                    
                    $headers = $messageDetails->getPayload()->getHeaders();
                    $subject = '';
                    $from = '';
                    $date = '';
                    
                    foreach ($headers as $header) {
                        switch ($header->getName()) {
                            case 'Subject':
                                $subject = $header->getValue();
                                break;
                            case 'From':
                                $from = $header->getValue();
                                break;
                            case 'Date':
                                $date = $header->getValue();
                                break;
                        }
                    }
                    
                    $formattedMessages[] = [
                        'id' => $message->getId(),
                        'subject' => $subject,
                        'from' => $from,
                        'date' => $date,
                        'snippet' => $messageDetails->getSnippet()
                    ];
                }
            }

            return $formattedMessages;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching Gmail messages: ' . $e->getMessage());
            return [];
        }
    }

    public function getChatMessages(): array
    {
        if (!$this->isAuthenticated()) {
            return [];
        }

        try {
            // Note: Google Chat API nécessite une configuration spéciale
            // Pour une application, il faut généralement être dans un workspace Google
            $service = new HangoutsChat($this->client);
            
            // Récupérer les espaces (conversations)
            $spaces = $service->spaces->listSpaces();
            
            $formattedMessages = [];
            
            if ($spaces->getSpaces()) {
                foreach ($spaces->getSpaces() as $space) {
                    try {
                        $messages = $service->spaces_messages->listSpacesMessages($space->getName(), [
                            'pageSize' => 5
                        ]);
                        
                        if ($messages->getMessages()) {
                            foreach ($messages->getMessages() as $message) {
                                $formattedMessages[] = [
                                    'id' => $message->getName(),
                                    'text' => $message->getText(),
                                    'sender' => $message->getSender() ? $message->getSender()->getDisplayName() : 'Inconnu',
                                    'createTime' => $message->getCreateTime(),
                                    'space' => $space->getDisplayName()
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignorer les erreurs pour des espaces spécifiques
                        continue;
                    }
                }
            }

            return $formattedMessages;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching Chat messages: ' . $e->getMessage());
            return [];
        }
    }

    public function disconnect(): void
    {
        $session = $this->getSession();
        if ($session) {
            $session->remove('google_api_token');
        }
    }
}