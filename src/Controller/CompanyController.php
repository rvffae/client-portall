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
                    // Vérifier si l'entreprise existe déjà (par email ou nom)
                    $existingCompany = null;
                    if (!empty($record['email'])) {
                        $existingCompany = $entityManager->getRepository(Company::class)
                            ->findOneBy(['email' => $record['email']]);
                    }
                    
                    if (!$existingCompany && !empty($record['name'])) {
                        $existingCompany = $entityManager->getRepository(Company::class)
                            ->findOneBy(['name' => $record['name']]);
                    }
                    
                    if ($existingCompany) {
                        $skippedCount++;
                        continue; // Skip si l'entreprise existe déjà
                    }

                    $company = new Company();
                    
                    // Mapper les champs du CSV vers l'entité (selon l'ordre du CSV fourni)
                    $company->setName($record['name'] ?? '');
                    $company->setCity(!empty($record['city']) ? $record['city'] : null);
                    $company->setState(!empty($record['state']) ? $record['state'] : null);
                    $company->setZipCode(!empty($record['zip_code']) ? $record['zip_code'] : null);
                    $company->setCountry(!empty($record['country']) ? $record['country'] : null);
                    $company->setPhone(!empty($record['phone']) ? $record['phone'] : null);
                    $company->setEmail(!empty($record['email']) ? $record['email'] : null);
                    $company->setWebsite(!empty($record['website']) ? $record['website'] : null);
                    $company->setAdress(!empty($record['address']) ? $record['address'] : null); // Note: address dans CSV, adress dans entité
                    
                    // Gérer les dates
                    if (!empty($record['created_at'])) {
                        try {
                            $company->setCreatedAt(new \DateTimeImmutable($record['created_at']));
                        } catch (\Exception $e) {
                            $company->setCreatedAt(new \DateTimeImmutable());
                        }
                    } else {
                        $company->setCreatedAt(new \DateTimeImmutable());
                    }
                    
                    if (!empty($record['updated_at'])) {
                        try {
                            $company->setUpdatedAt(new \DateTimeImmutable($record['updated_at']));
                        } catch (\Exception $e) {
                            $company->setUpdatedAt(new \DateTimeImmutable());
                        }
                    } else {
                        $company->setUpdatedAt(new \DateTimeImmutable());
                    }

                    // Validation basique
                    if (empty($company->getName())) {
                        $errors[] = "Ligne " . ($offset + 2) . ": Nom d'entreprise requis";
                        continue;
                    }

                    $entityManager->persist($company);
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
                'message' => "$importedCount entreprises importées avec succès" . 
                           ($skippedCount > 0 ? ", $skippedCount doublons ignorés" : "") .
                           (count($errors) > 0 ? ", " . count($errors) . " erreurs" : "")
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'import: ' . $e->getMessage()], 500);
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