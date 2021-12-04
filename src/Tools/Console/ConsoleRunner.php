<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tools\Console;

use Composer\InstalledVersions;
use Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

use function assert;

/**
 * Handles running the Console Tools inside Symfony Console context.
 */
class ConsoleRunner
{
    /**
     * Runs console with the given connection provider.
     *
     * @param array<int, Command> $commands
     *
     * @throws Exception
     */
    public static function run(ConnectionProvider $connectionProvider, array $commands = []): void
    {
        $version = InstalledVersions::getVersion('doctrine/dbal');
        assert($version !== null);

        $cli = new Application('Doctrine Command Line Interface', $version);

        $cli->setCatchExceptions(true);
        self::addCommands($cli, $connectionProvider);
        $cli->addCommands($commands);
        $cli->run();
    }

    public static function addCommands(Application $cli, ConnectionProvider $connectionProvider): void
    {
        $cli->addCommands([
            new RunSqlCommand($connectionProvider),
            new ReservedWordsCommand($connectionProvider),
        ]);
    }

    /**
     * Prints the instructions to create a configuration file
     */
    public static function printCliConfigTemplate(): void
    {
        echo <<<'HELP'
You are missing a "cli-config.php" or "config/cli-config.php" file in your
project, which is required to get the Doctrine-DBAL Console working. You can use the
following sample as a template:

<?php
use Doctrine\DBAL\Tools\Console\ConnectionProvider\SingleConnectionProvider;

// You can append new commands to $commands array, if needed

// replace with the mechanism to retrieve DBAL connection(s) in your app
// and return a Doctrine\DBAL\Tools\Console\ConnectionProvider instance.
$connection = getDBALConnection();

// in case you have a single connection you can use SingleConnectionProvider
// otherwise you need to implement the Doctrine\DBAL\Tools\Console\ConnectionProvider interface with your custom logic
return new SingleConnectionProvider($connection);

HELP;
    }
}
