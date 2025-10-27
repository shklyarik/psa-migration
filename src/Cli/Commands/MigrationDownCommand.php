<?php

namespace Psa\Migration\Cli\Commands;

use Psa\Qb\Db;
use Psa\Core\Cli\App;
use Throwable;
use RuntimeException;

class MigrationDownCommand
{
    public function run(Db $db, App $app)
    {
        echo "Migrating down\n";

        $migration = $db->from('migration')
            ->select('version')
            ->orderBy(['apply_time' => SORT_DESC])
            ->one();

        if (!$migration) {
            echo "No migrations to roll back\n";
            return;
        }

        $className = $migration['version'];
        $migration_file_path  = $app->getAlias("@app/migrations/{$className}.php");

        if (!file_exists($migration_file_path)) {
            echo "Migration file {$migration_file_path} not found — rollback aborted\n";
            return;
        }

        $answer = strtolower(trim(readline("Rollback migration {$className}? (yes/no): ")));
        if ($answer !== 'yes' && $answer !== 'y') {
            echo "Rollback cancelled by user.\n";
            return;
        }

        try {
            /** @var \Psa\Migration\Migration $migration */
            $migration = require_once $migration_file_path;
            $migration->setDbInstance($db);

            if (!method_exists($migration, 'down')) {
                throw new RuntimeException("Method down() not found in {$className}.");
            }

            $migration->down();

            $db->from('migration')
                ->where(['version' => $className])
                ->delete();

            echo "{$className} rolled back successfully\n";
        } catch (Throwable $e) {
            echo "Rollback of {$className} failed: {$e->getMessage()}\n";
        }
    }
}
