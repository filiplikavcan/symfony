<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Uid\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV1;
use Symfony\Component\Uid\UuidV6;

class InspectUuidCommand extends Command
{
    protected static $defaultName = 'uuid:inspect';
    protected static $defaultDescription = 'Inspect a UUID';

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('uuid', InputArgument::REQUIRED, 'The UUID to inspect'),
            ])
            ->setDescription(self::$defaultDescription)
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> displays information about a UUID.

    <info>php %command.full_name% a7613e0a-5986-11eb-a861-2bf05af69e52</info>
    <info>php %command.full_name% MfnmaUvvQ1h8B14vTwt6dX</info>
    <info>php %command.full_name% 57C4Z0MPC627NTGR9BY1DFD7JJ</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

        try {
            /** @var Uuid $uuid */
            $uuid = Uuid::fromString($input->getArgument('uuid'));
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());

            return 1;
        }

        if (-1 === $version = uuid_type($uuid)) {
            $version = 'nil';
        } elseif (0 === $version || 2 === $version || 6 < $version) {
            $version = 'unknown';
        }

        $rows = [
            ['Version', $version],
            ['Canonical (RFC 4122)', (string) $uuid],
            ['Base 58', $uuid->toBase58()],
            ['Base 32', $uuid->toBase32()],
        ];

        if ($uuid instanceof UuidV1 || $uuid instanceof UuidV6) {
            $rows[] = new TableSeparator();
            $rows[] = ['Timestamp', $uuid->getDateTime()->format('Y-m-d H:i:s.u')];
        }

        $io->table(['Label', 'Value'], $rows);

        return 0;
    }
}