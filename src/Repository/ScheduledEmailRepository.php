<?php

namespace App\Repository;

use App\Entity\ScheduledEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduledEmail>
 */
class ScheduledEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduledEmail::class);
    }

    /**
     * Récupérer les emails programmés en attente
     */
    public function findPendingEmails(): array
    {
        return $this->createQueryBuilder('se')
            ->where('se.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('se.scheduledTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les emails à envoyer maintenant
     */
    public function findEmailsToSend(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('se')
            ->where('se.status = :status')
            ->andWhere('se.scheduledTime <= :now')
            ->setParameter('status', 'pending')
            ->setParameter('now', $now)
            ->orderBy('se.scheduledTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter les emails programmés par statut
     */
    public function countByStatus(): array
    {
        $result = $this->createQueryBuilder('se')
            ->select('se.status, COUNT(se.id) as count')
            ->groupBy('se.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Récupérer les emails programmés récents
     */
    public function findRecentScheduled(int $limit = 10): array
    {
        return $this->createQueryBuilder('se')
            ->orderBy('se.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}