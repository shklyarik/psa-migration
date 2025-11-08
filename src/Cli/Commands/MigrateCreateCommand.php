<?php

namespace Psa\Migration\Cli\Commands;

use Psa\Core\Cli\App;

class MigrateCreateCommand
{
    public function run(App $app)
    {
        $args = $this->getArguments();

        if (isset($args['name'])) {
            $name = trim($args['name']);
        } else {
            $name = trim(readline("Enter migration name: "));
        }

        $name = strtr($name, [' ' => '_']);
        $migration_dir = $app->getAlias('@app/migrations');

        if (!file_exists($migration_dir)) {
            mkdir($migration_dir, 0777, true);
        }

        $className = 'm' . date('ymd_His') . '_' . $name;
        $file_path = $migration_dir . '/' . $className . '.php';

        file_put_contents($file_path, $this->getEmptyFileData());

        echo "Migration created: $file_path\n";
    }

    private function getEmptyFileData()
    {
        $o = [
            '<?php',
            '',
            'use Psa\Migration\Migration;',
            '',
            'return new class extends Migration',
            '{',
            '    public function up()',
            '    {',
            '    }',
            '',
            '    public function down()',
            '    {',
            '    }',
            '};',
            '',
        ];

        return implode("\n", $o);
    }

    private function getArguments(): array
    {
        $args = [];
        foreach ($_SERVER['argv'] as $arg) {
            if (preg_match('/^--([^=]+)=(.*)$/', $arg, $matches)) {
                $args[$matches[1]] = $matches[2];
            }
        }
        return $args;
    }
}