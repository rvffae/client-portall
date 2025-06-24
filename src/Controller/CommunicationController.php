<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GoogleApiService;

class CommunicationController extends AbstractController
{
    #[Route('/communication', name: 'app_communication')]
    public function index(GoogleApiService $googleApi): Response
    {
        try {
            // Récupérer les emails Gmail
            $emails = $googleApi->getGmailMessages();
            
            // Récupérer les conversations Google Chat
            $chatMessages = $googleApi->getChatMessages();
            
            return $this->render('communication/index.html.twig', [
                'emails' => $emails,
                'chatMessages' => $chatMessages,
                'controller_name' => 'CommunicationController',
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la récupération des données: ' . $e->getMessage());
            return $this->redirectToRoute('app_dashboard');
        }
    }
}