<?php

namespace T4\Commands;


use T4\Console\Command;
use T4\Console\Exception;
use T4\Orm\Migration;
use T4\Orm\Model;

class Migrate
    extends Command
{

    const TABLE_NAME = 'migrations';
    const MIGRATIONS_NAMESPACE = 'App\\Migrations';
    const CLASS_NAME_PATTERN = 'm_%d_%s';
    const SEARCH_FILE_NAME_PATTERN = 'm_%s_%s';
    const NAME_PARSE_PATTERN = '~m_(\d+)~';

    public function actionDefault()
    {
        $this->actionUp();
    }

    public function actionUp()
    {
        try {
            if (!$this->isInstalled()) {
                $this->install();
            }
            $migrations = $this->getMigrationsAfter($this->getLastTime());
            foreach ($migrations as $migration) {
                echo $migration->getName() . ' up...'."\n";
                $migration->up();
                $this->save($migration);
                echo $migration->getName() . ' is up successfully'."\n";
            }
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function actionDown()
    {

    }

    public function actionCreate($name)
    {
        $className = sprintf(self::CLASS_NAME_PATTERN, time(), $name);
        $namespace = self::MIGRATIONS_NAMESPACE;
        $content = <<<FILE
<?php

namespace {$namespace};

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
        $fileName = $this->getMigrationsPath().DS.$className.'.php';
        file_put_contents($fileName, $content);
        echo 'Migration '.$className.' is created in '.$this->getMigrationsPath();
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
            `' . Model::PK . '` SERIAL,
              `time` int(10) unsigned NOT NULL,
              UNIQUE KEY `time` (`time`)
            )
        ');
        echo 'Migration table `' . self::TABLE_NAME . '` is created' . "\n";
    }

    protected function getMigrationsPath()
    {
        return ROOT_PATH_PROTECTED.DS.'Migrations';
    }

    protected function getLastTime()
    {
        $st = $this->app->db->default->query('
            SELECT `time`
            FROM `' . self::TABLE_NAME . '`
            ORDER BY `' . Model::PK . '` DESC
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
        $migrations = [];
        foreach ( glob($this->getMigrationsPath().DS.sprintf(self::SEARCH_FILE_NAME_PATTERN, '*', '*').'.php') as $fileName ) {
            if (preg_match(self::NAME_PARSE_PATTERN, $fileName, $m)) {
                $migrationTime = (int)$m[1];
                if ($migrationTime > $time) {
                    $className = self::MIGRATIONS_NAMESPACE.'\\'.pathinfo($fileName, PATHINFO_FILENAME);
                    $migrations[] = new $className;
                }
            }
        }
        return $migrations;
    }

    protected function save(Migration $migration) {
        $this->app->db->default->execute('
            INSERT INTO `' . self::TABLE_NAME . '`
            (`time`)
            VALUES (\'' . $migration->getTimestamp() . '\')
        ');
    }

}