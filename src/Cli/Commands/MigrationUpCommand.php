<?php

namespace Psa\Migration\Cli\Commands;

use Psa\Qb\Db;
use Psa\Core\Cli\App;

class MigrationUpCommand
{
    public function run(Db $db, App $app)
    {
        global $argv;
        echo "Migration Up" . PHP_EOL;

        if (!in_array('migration', $db->connect()->getColumn('SHOW TABLES'))) {
            $db->connect()->query(
                'CREATE TABLE migration (
                    version VARCHAR(255) NOT NULL,
                    apply_time INT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
            );
        }

        $migrations = glob($app->getAlias('@app/migrations/m*.php'));
        $complete_migrations = $db->from('migration')->select('version')->column() ?? [];

        $isInteractive = true;
        foreach ($argv as $arg) {
            if (preg_match('/--interactive=0/', $arg)) {
                $isInteractive = false;
                break;
            }
        }

        foreach ($migrations as $migration_file_path) {
            $class_name = basename($migration_file_path, '.php');
            if (in_array($class_name, $complete_migrations)) {
                continue;
            }

            /** @var \Psa\Migration\Migration $migration */
            $migration = require_once $migration_file_path;
            $migration->setDbInstance($db);

            echo "Ready to apply migration: {$class_name}" . PHP_EOL;
            if ($isInteractive) {
                $answer = strtolower(trim(readline("Apply this migration? (yes/no): ")));
                if ($answer !== 'yes' && $answer !== 'y') {
                    echo "Migration process stopped by user." . PHP_EOL;
                    exit(0);
                }
            }
            echo "Applying: {$class_name}" . PHP_EOL;
            $migration->up();

            $db->from('migration')->insert([
                'version'    => $class_name,
                'apply_time' => time(),
            ]);
            echo "Migration {$class_name} applied successfully." . PHP_EOL;
        }
        echo "All migrations completed." . PHP_EOL;
    }
}