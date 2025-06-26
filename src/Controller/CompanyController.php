<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyForm;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    #[Route(name: 'app_company_index', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository): Response
    {
        return $this->render('company/index.html.twig', [
            'companies' => $companyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyForm::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company->setCreatedAt(new \DateTimeImmutable());
            $company->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/import-csv', name: 'app_company_import_csv', methods: ['POST'])]
    public function importCsv(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('csv_file');
        
        if (!$file || $file->getClientOriginalExtension() !== 'csv') {
            return new JsonResponse(['error' => 'Veuillez sélectionner un fichier CSV valide.'], 400);
        }

        try {
            $csv = Reader::createFromPath($file->getPathname(), 'r');
            $csv->setHeaderOffset(0);
            
            $records = $csv->getRecords();
            $importedCount = 0;
            $errors = [];
            
            foreach ($records as $offset => $record) {
                try {
                    $company = new Company();
                    
                    // Mapping des colonnes selon l'ordre du CSV fourni
                    // id,name,city,state,zip_code,country,phone,email,website,created_at,updated_at,address
                    $company->setName($record['name'] ?? '');
                    $company->setCity($record['city'] ?? null);
                    $company->setState($record['state'] ?? null);
                    $company->setZipCode($record['zip_code'] ?? null);
                    $company->setCountry($record['country'] ?? null);
                    $company->setPhone($record['phone'] ?? null);
                    $company->setEmail($record['email'] ?? null);
                    $company->setWebsite($record['website'] ?? null);
                    
                    // Gestion de l'adresse (colonne "address" dans le CSV)
                    $company->setAdress($record['address'] ?? null);
                    
                    // Gestion des dates
                    $createdAt = null;
                    $updatedAt = null;
                    
                    if (!empty($record['created_at'])) {
                        try {
                            $createdAt = new \DateTimeImmutable($record['created_at']);
                        } catch (\Exception $e) {
                            $createdAt = new \DateTimeImmutable();
                        }
                    } else {
                        $createdAt = new \DateTimeImmutable();
                    }
                    
                    if (!empty($record['updated_at'])) {
                        try {
                            $updatedAt = new \DateTimeImmutable($record['updated_at']);
                        } catch (\Exception $e) {
                            $updatedAt = new \DateTimeImmutable();
                        }
                    } else {
                        $updatedAt = new \DateTimeImmutable();
                    }
                    
                    $company->setCreatedAt($createdAt);
                    $company->setUpdatedAt($updatedAt);
                    
                    $entityManager->persist($company);
                    $importedCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Ligne " . ($offset + 2) . ": " . $e->getMessage();
                }
            }
            
            $entityManager->flush();
            
            $response = [
                'success' => true,
                'message' => "$importedCount entreprises importées avec succès.",
                'imported' => $importedCount
            ];
            
            if (!empty($errors)) {
                $response['warnings'] = $errors;
            }
            
            return new JsonResponse($response);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de l\'importation : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show(Company $company): Response
    {
        return $this->render('company/show.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompanyForm::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_delete', methods: ['POST'])]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($company);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
    }
}