<?php

namespace Psa\Migration\Cli;

class CommandRegistry
{
    public const array Commands = [
        'migration:up'     => \Psa\Migration\Cli\Commands\MigrationUpCommand::class,
        'migration:down'   => \Psa\Migration\Cli\Commands\MigrationDownCommand::class,
        'migration:create' => \Psa\Migration\Cli\Commands\MigrationCreateCommand::class,
    ];
}
