<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GoogleApiService;
use App\Service\ScheduledEmailService;

#[Route('/communication', name: 'communication_')]
class CommunicationController extends AbstractController
{
    private GoogleApiService $googleApiService;
    private ScheduledEmailService $scheduledEmailService;

    public function __construct(
        GoogleApiService $googleApiService,
        ScheduledEmailService $scheduledEmailService
    ) {
        $this->googleApiService = $googleApiService;
        $this->scheduledEmailService = $scheduledEmailService;
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        if (!$this->googleApiService->isAuthenticated()) {
            $this->addFlash('warning', 'Vous devez vous connecter à Google pour accéder aux communications');
            return $this->render('home/communication.html.twig', [
                'emails' => [],
                'chatMessages' => [],
                'authenticated' => false,
                'controller_name' => 'CommunicationController',
            ]);
        }

        try {
            $emails = $this->googleApiService->getGmailMessages();
            $chatMessages = $this->googleApiService->getChatMessages();
            return $this->render('home/communication.html.twig', [
                'emails' => $emails,
                'chatMessages' => $chatMessages,
                'authenticated' => true,
                'controller_name' => 'CommunicationController',
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la récupération des données: ' . $e->getMessage());
            return $this->render('home/communication.html.twig', [
                'emails' => [],
                'chatMessages' => [],
                'authenticated' => true,
                'error' => $e->getMessage(),
                'controller_name' => 'CommunicationController',
            ]);
        }
    }

    #[Route('/auth', name: 'auth')]
    public function authenticate(): Response
    {
        $authUrl = $this->googleApiService->getAuthUrl();
        return $this->redirect($authUrl);
    }

    #[Route('/callback', name: 'callback')]
    public function callback(Request $request): Response
    {
        $code = $request->query->get('code');
        if (!$code) {
            $this->addFlash('error', 'Erreur lors de l\'authentification Google');
            return $this->redirectToRoute('communication_index');
        }

        if ($this->googleApiService->authenticate($code)) {
            $this->addFlash('success', 'Google API connecté avec succès !');
        } else {
            $this->addFlash('error', 'Erreur lors de la connexion à Google API');
        }

        return $this->redirectToRoute('communication_index');
    }

    #[Route('/compose', name: 'compose')]
    public function compose(): Response
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return $this->redirectToRoute('communication_auth');
        }
        return $this->render('communication/compose.html.twig');
    }

    #[Route('/send-email', name: 'send_email', methods: ['POST'])]
    public function sendEmail(Request $request): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $attachments = [];
            $uploadedFiles = $request->files->all();

            if (!empty($uploadedFiles)) {
                $attachments = $this->googleApiService->processAttachments($uploadedFiles);
            }

            $emailData = [
                'to' => $data['to'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'cc' => $data['cc'] ?? '',
                'bcc' => $data['bcc'] ?? '',
                'attachments' => $attachments
            ];

            if (isset($data['scheduledTime']) && !empty($data['scheduledTime'])) {
                $scheduledTime = new \DateTime($data['scheduledTime']);
                $result = $this->scheduledEmailService->scheduleEmail($emailData, $scheduledTime);

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Email programmé avec succès',
                    'scheduledId' => $result['id']
                ]);
            } else {
                $result = $this->googleApiService->sendEmail($emailData);

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Email envoyé avec succès',
                    'messageId' => $result['id']
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/drafts', name: 'drafts', methods: ['GET'])]
    public function getDrafts(): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        try {
            $drafts = $this->googleApiService->getDrafts();

            return new JsonResponse([
                'success' => true,
                'drafts' => $drafts
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/create-draft', name: 'create_draft', methods: ['POST'])]
    public function createDraft(Request $request): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $attachments = [];
            $uploadedFiles = $request->files->all();

            if (!empty($uploadedFiles)) {
                $attachments = $this->googleApiService->processAttachments($uploadedFiles);
            }

            $emailData = [
                'to' => $data['to'] ?? '',
                'subject' => $data['subject'] ?? '',
                'body' => $data['body'] ?? '',
                'cc' => $data['cc'] ?? '',
                'bcc' => $data['bcc'] ?? '',
                'attachments' => $attachments
            ];

            $result = $this->googleApiService->createDraft($emailData);

            return new JsonResponse([
                'success' => true,
                'message' => 'Brouillon créé avec succès',
                'draftId' => $result['id']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/update-draft/{draftId}', name: 'update_draft', methods: ['PUT'])]
    public function updateDraft(string $draftId, Request $request): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        try {
            $data = json_decode($request->getContent(), true);

            $emailData = [
                'to' => $data['to'] ?? '',
                'subject' => $data['subject'] ?? '',
                'body' => $data['body'] ?? '',
                'cc' => $data['cc'] ?? '',
                'bcc' => $data['bcc'] ?? ''
            ];

            $result = $this->googleApiService->updateDraft($draftId, $emailData);

            return new JsonResponse([
                'success' => true,
                'message' => 'Brouillon mis à jour avec succès',
                'draftId' => $result['id']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/delete-draft/{draftId}', name: 'delete_draft', methods: ['DELETE'])]
    public function deleteDraft(string $draftId): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        try {
            $this->googleApiService->deleteDraft($draftId);

            return new JsonResponse([
                'success' => true,
                'message' => 'Brouillon supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/emails', name: 'emails', methods: ['GET'])]
    public function getEmails(): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $emails = $this->googleApiService->getGmailMessages(20);
        return new JsonResponse([
            'success' => true,
            'emails' => $emails
        ]);
    }

    #[Route('/chat-messages', name: 'chat_messages', methods: ['GET'])]
    public function getChatMessages(): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $messages = $this->googleApiService->getChatMessages();
        return new JsonResponse([
            'success' => true,
            'messages' => $messages
        ]);
    }

    #[Route('/disconnect', name: 'disconnect')]
    public function disconnect(): Response
    {
        $this->googleApiService->disconnect();
        $this->addFlash('success', 'Google API déconnecté');
        return $this->redirectToRoute('communication_index');
    }

    #[Route('/status', name: 'status', methods: ['GET'])]
    public function getStatus(): JsonResponse
    {
        return new JsonResponse([
            'authenticated' => $this->googleApiService->isAuthenticated()
        ]);
    }

    #[Route('/send-draft/{draftId}', name: 'send_draft', methods: ['POST'])]
    public function sendDraft(string $draftId): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        try {
            $result = $this->googleApiService->sendDraft($draftId);

            return new JsonResponse([
                'success' => true,
                'message' => 'Email envoyé depuis le brouillon',
                'messageId' => $result['id']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/scheduled-emails', name: 'scheduled_emails', methods: ['GET'])]
    public function getScheduledEmails(): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        try {
            $scheduledEmails = $this->scheduledEmailService->getScheduledEmails();

            return new JsonResponse([
                'success' => true,
                'scheduledEmails' => $scheduledEmails
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/cancel-scheduled/{scheduledId}', name: 'cancel_scheduled', methods: ['DELETE'])]
    public function cancelScheduledEmail(int $scheduledId): JsonResponse
    {
        if (!$this->googleApiService->isAuthenticated()) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        try {
            $this->scheduledEmailService->cancelScheduledEmail($scheduledId);

            return new JsonResponse([
                'success' => true,
                'message' => 'Email programmé annulé'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
