<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Form\InvoiceTypeForm;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice')]
final class InvoiceController extends AbstractController
{
    #[Route(name: 'app_invoice_index', methods: ['GET'])]
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoiceRepository->findAll(),
        ]);
    }

    #[Route('/stats', name: 'app_invoice_stats', methods: ['GET'])]
    public function stats(InvoiceRepository $invoiceRepository): Response
    {
        return $this->render('invoice/stats.html.twig');
    }

    #[Route('/api/revenue-data', name: 'app_invoice_revenue_data', methods: ['GET'])]
    public function getRevenueData(InvoiceRepository $invoiceRepository): JsonResponse
    {
        try {
            // Récupération des données groupées par mois
            $revenueData = $invoiceRepository->getMonthlyRevenue();
            
            // Debug: voir la structure des données
            error_log('Revenue data: ' . json_encode($revenueData));
            
            // Formatage des données pour Chart.js
            $labels = [];
            $data = [];
            
            foreach ($revenueData as $item) {
                // Formatage du label avec nom du mois
                $monthNames = [
                    1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin',
                    7 => 'Juil', 8 => 'Août', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
                ];
                $monthName = $monthNames[$item['month']] ?? $item['month'];
                $labels[] = $monthName . ' ' . $item['year'];
                $data[] = (float) $item['total_amount'];
            }
            
            // Si pas de données, retourner des données de test
            if (empty($data)) {
                return new JsonResponse([
                    'labels' => ['Jan 2024', 'Fév 2024', 'Mar 2024'],
                    'data' => [1500, 2300, 1800],
                    'debug' => 'Aucune donnée trouvée, données de test utilisées'
                ]);
            }
            
            return new JsonResponse([
                'labels' => $labels,
                'data' => $data,
                'debug' => count($revenueData) . ' enregistrements trouvés'
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'labels' => ['Test'],
                'data' => [1000]
            ]);
        }
    }

    #[Route('/api/yearly-revenue-data', name: 'app_invoice_yearly_revenue_data', methods: ['GET'])]
    public function getYearlyRevenueData(InvoiceRepository $invoiceRepository): JsonResponse
    {
        try {
            $yearlyData = $invoiceRepository->getYearlyRevenue();
            
            $labels = [];
            $data = [];
            $backgroundColors = ['#007bff', '#28a745', '#17a2b8', '#ffc107', '#dc3545'];
            
            foreach ($yearlyData as $index => $item) {
                $labels[] = (string) $item['year'];
                $data[] = (float) $item['total_amount'];
            }
            
            return new JsonResponse([
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => array_slice($backgroundColors, 0, count($data))
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'labels' => ['2024'],
                'data' => [50000]
            ]);
        }
    }

    #[Route('/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceTypeForm::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setCreatedAt(new \DateTimeImmutable());
            $invoice->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceTypeForm::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->request->get('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
    }
}