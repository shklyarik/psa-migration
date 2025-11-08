<?php

namespace Psa\Migration\Cli;

class CommandRegistry
{
    public const array Commands = [
        'migrate:up'     => \Psa\Migration\Cli\Commands\MigrateUpCommand::class,
        'migrate:down'   => \Psa\Migration\Cli\Commands\MigrateDownCommand::class,
        'migrate:create' => \Psa\Migration\Cli\Commands\MigrateCreateCommand::class,
    ];
}
