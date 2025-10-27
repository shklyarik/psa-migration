<?php

namespace Psa\Migration\Cli\Commands;

use Psa\Core\Cli\App;

class MigrationCreateCommand
{
    public function run(App $app)
    {
        $name = trim(readline("Enter migration name: "));
        $name = strtr($name, [' ' => '_']);
        $migration_dir = $app->getAlias('@app/migrations');

        if (!file_exists($migration_dir)) {
            mkdir($migration_dir);
        }

        $className = 'm' . date('ymd_His') . '_' . $name;

        $file_path = $migration_dir . '/' . $className . '.php';

        file_put_contents($file_path, $this->getEmptyFileData($className));
    }

    private function getEmptyFileData($className)
    {
        $o = [
            '<?php',
            '',
            'use use Psa\Migration\Migration;',
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
}