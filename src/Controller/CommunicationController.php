<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GoogleApiService;

#[Route('/communication', name: 'communication_')]
class CommunicationController extends AbstractController
{
    private GoogleApiService $googleApiService;

    public function __construct(GoogleApiService $googleApiService)
    {
        $this->googleApiService = $googleApiService;
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
            // Récupérer les emails Gmail
            $emails = $this->googleApiService->getGmailMessages();
            
            // Récupérer les conversations Google Chat
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
}