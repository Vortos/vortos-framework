<?php

declare(strict_types=1);

namespace Vortos\Messaging\Command;

use Vortos\Messaging\Contract\ProducerInterface;
use Vortos\Messaging\DeadLetter\DeadLetterRepository;
use Vortos\Messaging\Serializer\SerializerLocator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Replays permanently failed outbox messages by re-producing them to their
 * original transport. Use --dry-run to inspect failed messages first.
 *
 * A message is eligible for replay when its status is 'failed' — meaning
 * it has exhausted all automatic retry attempts. Replaying re-produces the
 * message and marks it as published on success.
 */
#[AsCommand(
    name: 'vortos:dlq:replay',
    description: 'Replay failed outbox messages back into the relay pipeline'
)]
final class ReplayDeadLetterCommand extends Command
{
    public function __construct(
        private DeadLetterRepository $repository,
        private ProducerInterface $producer,
        private SerializerLocator $serializerLocator,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max messages to replay', 50)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List messages that would be replayed without replaying them');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit  = (int) $input->getOption('limit');
        $dryRun = (bool) $input->getOption('dry-run');

        $rows = $this->repository->fetchFailed($limit);

        if (empty($rows)) {
            $output->writeln('<info>No failed messages found.</info>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Found %d failed message(s).</info>', count($rows)));
        $output->writeln('');

        if ($dryRun) {
            foreach ($rows as $row) {
                $output->writeln(sprintf(
                    '  • %s  |  %s  →  %s',
                    $row['id'],
                    $row['event_class'],
                    $row['transport_name']
                ));
            }
            $output->writeln('<comment>Dry run complete. No messages replayed.</comment>');
            return Command::SUCCESS;
        }

        $replayed = 0;
        $failed   = 0;

        foreach ($rows as $row) {
            try {
                $serializer = $this->serializerLocator->locate('json');
                $event      = $serializer->deserialize($row['payload'], $row['event_class']);
                $headers    = json_decode($row['headers'], true) ?? [];

                $this->producer->produce($row['transport_name'], $event, $headers);
                $this->repository->markReplayed($row['id']);

                $output->writeln(sprintf(
                    '  <info>✔ Replayed:</info> %s  |  %s',
                    $row['id'],
                    $row['event_class']
                ));
                $replayed++;
            } catch (\Throwable $e) {
                $output->writeln(sprintf(
                    '  <error>✘ Failed:</error> %s — %s',
                    $row['id'],
                    $e->getMessage()
                ));
                $failed++;
            }
        }

        $output->writeln('');
        $output->writeln(sprintf(
            '<info>Done.</info> Replayed: <info>%d</info>  Failed: %s',
            $replayed,
            $failed > 0 ? sprintf('<error>%d</error>', $failed) : '<info>0</info>'
        ));

        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
