<?php
namespace App\Service;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Google\Service\Gmail\Draft;
use Google\Service\HangoutsChat;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    private ?Gmail $gmailService = null;

    private const SCOPES = [
        Gmail::GMAIL_READONLY,
        Gmail::GMAIL_COMPOSE,
        Gmail::GMAIL_MODIFY,
        Gmail::GMAIL_SEND,
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
        $this->client->setScopes(self::SCOPES);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        $this->client->setApprovalPrompt('force');
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

    public function disconnect(): void
    {
        $session = $this->getSession();
        if ($session) {
            $session->remove('google_api_token');
        }
    }

    public function createDraft(array $emailData): ?array
    {
        if (!$this->isAuthenticated()) {
            throw new \Exception('Non authentifié');
        }

        try {
            $message = $this->createMessage($emailData);
            $draft = new Draft();
            $draft->setMessage($message);
            $result = $this->getGmailService()->users_drafts->create('me', $draft);

            return [
                'id' => $result->getId(),
                'message' => $result->getMessage()
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la création du brouillon: ' . $e->getMessage());
        }
    }

    public function sendEmail(array $emailData): ?array
    {
        if (!$this->isAuthenticated()) {
            throw new \Exception('Non authentifié');
        }

        try {
            $message = $this->createMessage($emailData);
            $result = $this->getGmailService()->users_messages->send('me', $message);

            return [
                'id' => $result->getId(),
                'threadId' => $result->getThreadId()
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de l\'envoi: ' . $e->getMessage());
        }
    }

    public function updateDraft(string $draftId, array $emailData): ?array
    {
        if (!$this->isAuthenticated()) {
            throw new \Exception('Non authentifié');
        }

        try {
            $message = $this->createMessage($emailData);
            $draft = new Draft();
            $draft->setMessage($message);
            $result = $this->getGmailService()->users_drafts->update('me', $draftId, $draft);

            return [
                'id' => $result->getId(),
                'message' => $result->getMessage()
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la mise à jour du brouillon: ' . $e->getMessage());
        }
    }

    public function deleteDraft(string $draftId): bool
    {
        if (!$this->isAuthenticated()) {
            throw new \Exception('Non authentifié');
        }

        try {
            $this->getGmailService()->users_drafts->delete('me', $draftId);
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la suppression du brouillon: ' . $e->getMessage());
        }
    }

    public function getDrafts(int $maxResults = 20): array
    {
        if (!$this->isAuthenticated()) {
            throw new \Exception('Non authentifié');
        }

        try {
            $draftsResponse = $this->getGmailService()->users_drafts->listUsersDrafts('me', [
                'maxResults' => $maxResults
            ]);

            $drafts = [];
            if ($draftsResponse->getDrafts()) {
                foreach ($draftsResponse->getDrafts() as $draft) {
                    $draftDetail = $this->getGmailService()->users_drafts->get('me', $draft->getId());
                    $message = $draftDetail->getMessage();

                    $headers = $message->getPayload()->getHeaders();
                    $subject = '';
                    $to = '';
                    $from = '';

                    foreach ($headers as $header) {
                        switch ($header->getName()) {
                            case 'Subject':
                                $subject = $header->getValue();
                                break;
                            case 'To':
                                $to = $header->getValue();
                                break;
                            case 'From':
                                $from = $header->getValue();
                                break;
                        }
                    }

                    $drafts[] = [
                        'id' => $draft->getId(),
                        'messageId' => $message->getId(),
                        'subject' => $subject,
                        'to' => $to,
                        'from' => $from,
                        'snippet' => $message->getSnippet(),
                        'date' => new \DateTime('@' . ($message->getInternalDate() / 1000))
                    ];
                }
            }
            return $drafts;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la récupération des brouillons: ' . $e->getMessage());
        }
    }

    public function getGmailMessages(int $maxResults = 10): array
    {
        if (!$this->isAuthenticated()) {
            return [];
        }

        try {
            $service = $this->getGmailService();
            $messages = $service->users_messages->listUsersMessages('me', [
                'maxResults' => $maxResults,
                'q' => 'is:unread'
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
            $service = new HangoutsChat($this->client);
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

    public function processAttachments(array $files): array
    {
        $attachments = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $attachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'mimeType' => $file->getMimeType(),
                    'data' => file_get_contents($file->getPathname())
                ];
            }
        }

        return $attachments;
    }

    private function getGmailService(): Gmail
    {
        if ($this->gmailService === null) {
            $this->gmailService = new Gmail($this->client);
        }
        return $this->gmailService;
    }

    private function createMessage(array $emailData): Message
    {
        $boundary = uniqid(rand(), true);

        $rawMessage = "To: " . $emailData['to'] . "\r\n";
        $rawMessage .= "Subject: " . $emailData['subject'] . "\r\n";

        if (isset($emailData['cc']) && !empty($emailData['cc'])) {
            $rawMessage .= "Cc: " . $emailData['cc'] . "\r\n";
        }

        if (isset($emailData['bcc']) && !empty($emailData['bcc'])) {
            $rawMessage .= "Bcc: " . $emailData['bcc'] . "\r\n";
        }

        if (isset($emailData['attachments']) && !empty($emailData['attachments'])) {
            $rawMessage .= "MIME-Version: 1.0\r\n";
            $rawMessage .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n\r\n";

            $rawMessage .= "--$boundary\r\n";
            $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n";
            $rawMessage .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
            $rawMessage .= quoted_printable_encode($emailData['body']) . "\r\n\r\n";

            foreach ($emailData['attachments'] as $attachment) {
                $rawMessage .= "--$boundary\r\n";
                $rawMessage .= "Content-Type: " . $attachment['mimeType'] . "; name=\"" . $attachment['filename'] . "\"\r\n";
                $rawMessage .= "Content-Disposition: attachment; filename=\"" . $attachment['filename'] . "\"\r\n";
                $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $rawMessage .= chunk_split(base64_encode($attachment['data'])) . "\r\n";
            }

            $rawMessage .= "--$boundary--";
        } else {
            $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $rawMessage .= $emailData['body'];
        }

        $message = new Message();
        $message->setRaw(base64url_encode($rawMessage));

        return $message;
    }

    private function getSession()
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request ? $request->getSession() : null;
    }

    public function getGmailMessageById(string $messageId): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $service = $this->getGmailService();
            $messageDetails = $service->users_messages->get('me', $messageId);
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

            return [
                'id' => $messageDetails->getId(),
                'subject' => $subject,
                'from' => $from,
                'date' => $date,
                'snippet' => $messageDetails->getSnippet()
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error fetching Gmail message by ID: ' . $e->getMessage());
            return null;
        }
    }
}

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
