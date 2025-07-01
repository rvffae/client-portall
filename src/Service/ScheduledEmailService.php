<?php

namespace App\Service;

use App\Entity\ScheduledEmail;
use App\Repository\ScheduledEmailRepository;
use Doctrine\ORM\EntityManagerInterface;

class ScheduledEmailService
{
    private EntityManagerInterface $entityManager;
    private ScheduledEmailRepository $scheduledEmailRepository;
    private GoogleApiService $googleApiService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScheduledEmailRepository $scheduledEmailRepository,
        GoogleApiService $googleApiService
    ) {
        $this->entityManager = $entityManager;
        $this->scheduledEmailRepository = $scheduledEmailRepository;
        $this->googleApiService = $googleApiService;
    }

    /**
     * Programmer un email
     */
    public function scheduleEmail(array $emailData, \DateTime $scheduledTime): array
    {
        $scheduledEmail = new ScheduledEmail();
        $scheduledEmail->setToEmail($emailData['to']);
        $scheduledEmail->setSubject($emailData['subject']);
        $scheduledEmail->setBody($emailData['body']);
        $scheduledEmail->setCcEmail($emailData['cc'] ?? '');
        $scheduledEmail->setBccEmail($emailData['bcc'] ?? '');
        $scheduledEmail->setAttachments($emailData['attachments'] ?? []);
        $scheduledEmail->setScheduledTime($scheduledTime);
        $scheduledEmail->setStatus('pending');
        $scheduledEmail->setCreatedAt(new \DateTime());

        $this->entityManager->persist($scheduledEmail);
        $this->entityManager->flush();

        return [
            'id' => $scheduledEmail->getId(),
            'scheduledTime' => $scheduledTime
        ];
    }

    /**
     * Récupérer les emails programmés
     */
    public function getScheduledEmails(): array
    {
        $scheduledEmails = $this->scheduledEmailRepository->findBy(
            ['status' => 'pending'],
            ['scheduledTime' => 'ASC']
        );

        $result = [];
        foreach ($scheduledEmails as $email) {
            $result[] = [
                'id' => $email->getId(),
                'to' => $email->getToEmail(),
                'subject' => $email->getSubject(),
                'scheduledTime' => $email->getScheduledTime()->format('Y-m-d H:i:s'),
                'status' => $email->getStatus(),
                'createdAt' => $email->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $result;
    }

    /**
     * Annuler un email programmé
     */
    public function cancelScheduledEmail(int $scheduledId): bool
    {
        $scheduledEmail = $this->scheduledEmailRepository->find($scheduledId);
        
        if (!$scheduledEmail || $scheduledEmail->getStatus() !== 'pending') {
            throw new \Exception('Email programmé introuvable ou déjà traité');
        }

        $scheduledEmail->setStatus('cancelled');
        $this->entityManager->flush();

        return true;
    }

    /**
     * Traiter les emails programmés (à exécuter via une commande cron)
     */
    public function processPendingEmails(): array
    {
        $now = new \DateTime();
        $pendingEmails = $this->scheduledEmailRepository->createQueryBuilder('se')
            ->where('se.status = :status')
            ->andWhere('se.scheduledTime <= :now')
            ->setParameter('status', 'pending')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        $results = [];
        
        foreach ($pendingEmails as $scheduledEmail) {
            try {
                $emailData = [
                    'to' => $scheduledEmail->getToEmail(),
                    'subject' => $scheduledEmail->getSubject(),
                    'body' => $scheduledEmail->getBody(),
                    'cc' => $scheduledEmail->getCcEmail(),
                    'bcc' => $scheduledEmail->getBccEmail(),
                    'attachments' => $scheduledEmail->getAttachments() ?? []
                ];

                $result = $this->googleApiService->sendEmail($emailData);
                
                $scheduledEmail->setStatus('sent');
                $scheduledEmail->setSentAt(new \DateTime());
                $scheduledEmail->setGmailMessageId($result['id']);
                
                $results[] = [
                    'id' => $scheduledEmail->getId(),
                    'status' => 'sent',
                    'gmailId' => $result['id']
                ];
                
            } catch (\Exception $e) {
                $scheduledEmail->setStatus('failed');
                $scheduledEmail->setErrorMessage($e->getMessage());
                
                $results[] = [
                    'id' => $scheduledEmail->getId(),
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
            
            $this->entityManager->flush();
        }

        return $results;
    }
}