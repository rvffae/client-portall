<?php

namespace App\Command;

use App\Service\ScheduledEmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-scheduled-emails',
    description: 'Envoie les emails programmés qui sont dus',
)]
class SendScheduledEmailsCommand extends Command
{
    private ScheduledEmailService $scheduledEmailService;

    public function __construct(ScheduledEmailService $scheduledEmailService)
    {
        parent::__construct();
        $this->scheduledEmailService = $scheduledEmailService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Traitement des emails programmés');

        try {
            $results = $this->scheduledEmailService->processPendingEmails();
            
            if (empty($results)) {
                $io->success('Aucun email programmé à envoyer pour le moment.');
                return Command::SUCCESS;
            }

            $sentCount = 0;
            $failedCount = 0;

            foreach ($results as $result) {
                if ($result['status'] === 'sent') {
                    $sentCount++;
                    $io->writeln(sprintf(
                        '<info>✓ Email ID %d envoyé avec succès (Gmail ID: %s)</info>',
                        $result['id'],
                        $result['gmailId']
                    ));
                } else {
                    $failedCount++;
                    $io->writeln(sprintf(
                        '<error>✗ Échec de l\'envoi de l\'email ID %d: %s</error>',
                        $result['id'],
                        $result['error']
                    ));
                }
            }

            $io->newLine();
            $io->writeln(sprintf('Résumé:'));
            $io->writeln(sprintf('- Emails envoyés: <info>%d</info>', $sentCount));
            $io->writeln(sprintf('- Emails échoués: <error>%d</error>', $failedCount));
            $io->writeln(sprintf('- Total traité: %d', count($results)));

            if ($failedCount > 0) {
                $io->warning(sprintf('%d email(s) n\'ont pas pu être envoyés.', $failedCount));
                return Command::FAILURE;
            }

            $io->success(sprintf('Tous les emails programmés ont été traités avec succès (%d envoyés).', $sentCount));
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error(sprintf('Erreur lors du traitement des emails programmés: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}