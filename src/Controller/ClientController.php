<?php
namespace App\Controller;

use App\Entity\Client;
use App\Entity\Company;
use App\Form\ClientForm;
use App\Repository\ClientRepository;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client')]
final class ClientController extends AbstractController
{
    #[Route(name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientForm::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setCreatedAt(new \DateTime());
            $client->setUpdatedAt(new \DateTime());
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/import-csv', name: 'app_client_import_csv', methods: ['POST'])]
    public function importCsv(Request $request, EntityManagerInterface $entityManager, CompanyRepository $companyRepository): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('csv_file');
        
        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier fourni'], 400);
        }

        if ($file->getClientOriginalExtension() !== 'csv') {
            return new JsonResponse(['error' => 'Le fichier doit être au format CSV'], 400);
        }

        try {
            // Lire le fichier CSV
            $csv = Reader::createFromPath($file->getPathname(), 'r');
            $csv->setHeaderOffset(0); // La première ligne contient les en-têtes
            
            $records = $csv->getRecords();
            $importedCount = 0;
            $errors = [];
            $skippedCount = 0;

            foreach ($records as $offset => $record) {
                try {
                    // Vérifier si le client existe déjà (par email si présent)
                    if (!empty($record['email'])) {
                        $existingClient = $entityManager->getRepository(Client::class)
                            ->findOneBy(['email' => $record['email']]);
                        
                        if ($existingClient) {
                            $skippedCount++;
                            continue; // Skip si le client existe déjà
                        }
                    }

                    $client = new Client();
                    
                    // Récupérer la company si l'ID est fourni
                    if (!empty($record['company_id_id'])) {
                        $company = $companyRepository->find((int)$record['company_id_id']);
                        if ($company) {
                            $client->setCompanyId($company);
                        }
                    }

                    // Mapper les champs du CSV vers l'entité
                    $client->setFirstName($record['first_name'] ?? '');
                    $client->setLastName($record['last_name'] ?? '');
                    $client->setEmail(!empty($record['email']) ? $record['email'] : null);
                    $client->setPhone(!empty($record['phone']) ? $record['phone'] : null);
                    $client->setAdress(!empty($record['address']) ? $record['address'] : null); // Note: address dans CSV, adress dans entité
                    $client->setCity(!empty($record['city']) ? $record['city'] : null);
                    $client->setState(!empty($record['state']) ? $record['state'] : null);
                    $client->setZipCode(!empty($record['zip_code']) ? $record['zip_code'] : null);
                    $client->setCountry(!empty($record['country']) ? $record['country'] : null);
                    
                    // Gérer les dates
                    if (!empty($record['created_at'])) {
                        try {
                            $client->setCreatedAt(new \DateTime($record['created_at']));
                        } catch (\Exception $e) {
                            $client->setCreatedAt(new \DateTime());
                        }
                    } else {
                        $client->setCreatedAt(new \DateTime());
                    }
                    
                    if (!empty($record['updated_at'])) {
                        try {
                            $client->setUpdatedAt(new \DateTime($record['updated_at']));
                        } catch (\Exception $e) {
                            $client->setUpdatedAt(new \DateTime());
                        }
                    } else {
                        $client->setUpdatedAt(new \DateTime());
                    }

                    // Validation basique
                    if (empty($client->getFirstName()) || empty($client->getLastName())) {
                        $errors[] = "Ligne " . ($offset + 2) . ": Prénom et nom requis";
                        continue;
                    }

                    $entityManager->persist($client);
                    $importedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Ligne " . ($offset + 2) . ": " . $e->getMessage();
                }
            }

            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors,
                'message' => "$importedCount clients importés avec succès" . 
                           ($skippedCount > 0 ? ", $skippedCount doublons ignorés" : "") .
                           (count($errors) > 0 ? ", " . count($errors) . " erreurs" : "")
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'import: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientForm::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
    }
}