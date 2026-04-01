<?php

declare(strict_types=1);

namespace Vortos\Messaging\Command;

use Vortos\Messaging\Registry\ProducerRegistry;
use Vortos\Messaging\Registry\TransportRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists all registered transports and which producers are bound to each.
 * Useful for verifying MessagingConfig transport/producer wiring.
 */
#[AsCommand(
    name: 'vortos:transports:list',
    description: 'List all registered transports and their associated producers'
)]
final class ListTransportsCommand extends Command
{
    public function __construct(
        private TransportRegistry $transportRegistry,
        private ProducerRegistry $producerRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $transports = $this->transportRegistry->all();

        if (empty($transports)) {
            $output->writeln('<comment>No transports registered.</comment>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Found %d transport(s).</info>', count($transports)));
        $output->writeln('');

        foreach ($transports as $name => $config) {
            $output->writeln(sprintf('<info>▶ %s</info>', $name));
            $output->writeln(sprintf('  DSN:        %s', $config['dsn']));
            $output->writeln(sprintf('  Topic:      %s', $config['subscription']['topic']));
            $output->writeln(sprintf('  Serializer: %s', $config['serializer']));

            $boundProducers = [];
            foreach ($this->producerRegistry->all() as $producerName => $producer) {
                if ($producer['transport'] === $name) {
                    $boundProducers[] = $producerName;
                }
            }

            if (empty($boundProducers)) {
                $output->writeln('  Producers:  <comment>none</comment>');
            } else {
                $output->writeln(sprintf('  Producers (%d):', count($boundProducers)));
                foreach ($boundProducers as $producerName) {
                    $output->writeln(sprintf('    - %s', $producerName));
                }
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
