<?php

declare(strict_types=1);

namespace App\Command\Password;

use App\Legacy\Dbal\Tables;
use Icms\Core\Password\Wrapper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;

/**
 * Wraps every legacy password hash in Argon2id at once (OpenSpec change
 * symfony-password-login, design D4), for sites where the system module
 * update would need many runs to finish within its time budget.
 */
#[AsCommand(name: 'icms:passwords:wrap', description: 'Wrap the legacy password hashes of all users in Argon2id')]
final class Wrap extends Command
{
    /**
     * @param \Closure(): \Doctrine\DBAL\Connection $connection created on first use: the
     *                                                  database settings come from mainfile.php
     */
    public function __construct(
        #[AutowireServiceClosure('doctrine.dbal.default_connection')]
        private readonly \Closure $connection,
        private readonly Tables $tables,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pdo = ($this->connection)()->getNativeConnection();
        if (!$pdo instanceof \PDO) {
            $output->writeln('<error>The database connection is not a PDO connection.</error>');

            return Command::FAILURE;
        }
        $wrapper = new Wrapper($pdo, $this->tables->name('users'));
        $output->writeln(sprintf('%d legacy password hashes to wrap.', $wrapper->pending()));

        [$wrapped, $remaining] = $wrapper->wrap(null, static function (int $done) use ($output): void {
            if ($done % 100 === 0) {
                $output->writeln(sprintf('  %d wrapped', $done));
            }
        });
        $output->writeln(sprintf('%d wrapped, %d left.', $wrapped, $remaining));

        return $remaining === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
