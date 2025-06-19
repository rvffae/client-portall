<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        // Récupérer l'utilisateur actuel
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Rendre la vue du tableau de bord
        return $this->render('dash/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/dashboard/clients', name: 'app_dashboard_clients')]
    public function clients(): Response
    {
        // Récupérer l'utilisateur actuel
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Rendre la vue des clients
        return $this->render('dashboard/clients.html.twig', [
            'user' => $user,
        ]);
    }

    // Ajoutez d'autres actions pour les différentes pages du tableau de bord
}
