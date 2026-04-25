<?php

declare(strict_types=1);

namespace Vortos\Messaging\Command;

use Vortos\Messaging\Registry\ConsumerRegistry;
use Vortos\Messaging\Registry\HandlerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists all registered consumers, their Kafka configuration, and their
 * registered event handlers with priorities and idempotency flags.
 * Useful for debugging handler registration and verifying MessagingConfig setup.
 */
#[AsCommand(
    name: 'vortos:consumers:list',
    description: 'List all registered consumers and their handlers'
)]
final class ListConsumersCommand extends Command
{
    public function __construct(
        private HandlerRegistry $handlerRegistry,
        private ConsumerRegistry $consumerRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumers = $this->consumerRegistry->all();

        if (empty($consumers)) {
            $output->writeln('<comment>No consumers registered.</comment>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Found %d consumer(s).</info>', count($consumers)));
        $output->writeln('');

        foreach ($consumers as $consumerName => $config) {
            $output->writeln(sprintf('<info>▶ %s</info>', $consumerName));
            $output->writeln(sprintf('  Group ID:    %s', $config['groupId']));
            $output->writeln(sprintf('  Parallelism: %s', $config['parallelism']));
            $output->writeln(sprintf('  Transport:   %s', $config['transport']));

            $eventHandlers = $this->handlerRegistry->allForConsumer($consumerName);

            if (empty($eventHandlers)) {
                $output->writeln('  <comment>No handlers registered.</comment>');
            } else {
                $output->writeln(sprintf('  Handlers (%d):', count($eventHandlers)));
                foreach ($eventHandlers as $eventClass => $descriptors) {
                    $output->writeln(sprintf('    <comment>%s</comment>', $eventClass));
                    foreach ($descriptors as $descriptor) {
                        $idempotent = $descriptor['idempotent'] ? '<info>yes</info>' : '<comment>no</comment>';
                        $output->writeln(sprintf(
                            '      - %s  priority: %d  idempotent: %s',
                            $descriptor['handlerId'],
                            $descriptor['priority'],
                            $idempotent
                        ));
                    }
                }
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
