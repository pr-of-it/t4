<?php

namespace T4\Commands;


use T4\Console\Command;

class Migrate
    extends Command
{

    const TABLE_NAME = 'migrations';
    const CLASS_NAME_PATTERN = 'm_%d_%s';

    public function actionDefault()
    {
        $this->actionUp();
    }

    public function actionUp()
    {
        if (!$this->isInstalled()) {
            $this->install();
        }
        $lastMigrationTime = $this->getLastTime();
        $migrations = $this->getMigrationsAfter($lastMigrationTime);
        foreach ($migrations as $migration) {
            $migration->up();
        }
    }

    public function actionCreate($name)
    {
        $className = sprintf(self::CLASS_NAME_PATTERN, time(), $name);
        $content = <<<FILE
<?php

namespace App\Migrations;

use T4\Orm\Migration;

class {$className}
    extends Migration
{

    public function up()
    {
    }

    public function down()
    {
    }

}
FILE;
        $fileName = ROOT_PATH_PROTECTED.DS.'Migrations'.DS.$className.'.php';
        file_put_contents($fileName, $content);
        echo 'Migration '.$className.' is created in '.dirname($fileName);
    }

    public function actionDown()
    {

    }

    protected function isInstalled()
    {
        // TODO: заменить на вызов через драйвер
        $st = $this->app->db->default->query('SHOW TABLES LIKE \'' . self::TABLE_NAME . '\'');
        if ([] == $st->fetchAll())
            return false;
        else
            return true;
    }

    protected function install()
    {
        // TODO: заменить на вызов через драйвер
        $this->app->db->default->execute('
            CREATE TABLE  `' . self::TABLE_NAME . '` (
            `__id` SERIAL,
              `time` int(10) unsigned NOT NULL,
              UNIQUE KEY `time` (`time`)
            )
        ');
        echo 'Migration table `' . self::TABLE_NAME . '` is created' . "\n";
    }

    protected function getLastTime()
    {
        $st = $this->app->db->default->query('
            SELECT `time`
            FROM `' . self::TABLE_NAME . '`
            ORDER BY `__id` DESC
            LIMIT 1
        ');
        $row = $st->fetch(\PDO::FETCH_OBJ);
        if (!empty($row))
            return $row->time;
        else
            return 0;
    }

    protected function getMigrationsAfter($time)
    {
        return [];
    }

}