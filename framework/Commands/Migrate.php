<?php

namespace T4\Commands;


use T4\Console\Command;
use T4\Console\Exception;
use T4\Orm\Migration;
use T4\Orm\Model;

class Migrate
    extends Command
{

    const TABLE_NAME = '__migrations';
    const MIGRATIONS_NAMESPACE = 'App\\Migrations';
    const CLASS_NAME_PATTERN = 'm_%010d_%s';
    const SEARCH_FILE_NAME_PATTERN = 'm_%s_%s';

    protected function getMigrationsPath()
    {
        return ROOT_PATH_PROTECTED . DS . 'Migrations';
    }


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
                echo $migration->getName() . ' up...' . "\n";
                $migration->up();
                $this->save($migration);
                echo $migration->getName() . ' is up successfully' . "\n";
            }
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function actionDown()
    {
        if (!$this->isInstalled()) {
            throw new Exception('Migrations are not installed. Use t4 /migrate command to install ones.');
        }

        $migration = $this->getLastMigration();
        if (false === $migration) {
            throw new Exception('No migrations to down');
        }

        try {
            echo $migration->getName() . ' down...' . "\n";
            $migration->down();
            $this->delete($migration);
            echo $migration->getName() . ' is down successfully' . "\n";
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
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
        $fileName = $this->getMigrationsPath() . DS . $className . '.php';
        file_put_contents($fileName, $content);
        echo 'Migration ' . $className . ' is created in ' . $this->getMigrationsPath();
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

    protected function getLastTime()
    {
        $st = $this->app->db->default->query('
            SELECT `time`
            FROM `' . self::TABLE_NAME . '`
            ORDER BY `' . Model::PK . '` DESC
            LIMIT 1
        ');
        $time = $st->fetchScalar() ?: 0;
        return $time;
    }

    protected function getMigrationsAfter($time)
    {
        $migrations = [];
        foreach (glob($this->getMigrationsPath() . DS . sprintf(self::SEARCH_FILE_NAME_PATTERN, '*', '*') . '.php') as $fileName) {
            $className = self::MIGRATIONS_NAMESPACE . '\\' . pathinfo($fileName, PATHINFO_FILENAME);
            $migration = new $className;
            if ($migration->getTimestamp() > $time) {
                $migrations[] = $migration;
            }
        }
        return $migrations;
    }

    /**
     * Возвращает последнюю примененную миграцию
     * @return bool
     */
    protected function getLastMigration()
    {
        $lastMigrationTime = $this->getLastTime();
        if (empty($lastMigrationTime))
            return false;

        foreach (glob($this->getMigrationsPath() . DS . sprintf(self::CLASS_NAME_PATTERN, $lastMigrationTime, '*') . '.php') as $fileName) {
            $className = self::MIGRATIONS_NAMESPACE . '\\' . pathinfo($fileName, PATHINFO_FILENAME);
            $migration = new $className;
            break;
        }

        return $migration;
    }

    protected function save(Migration $migration)
    {
        $this->app->db->default->execute('
            INSERT INTO `' . self::TABLE_NAME . '`
            (`time`)
            VALUES (\'' . $migration->getTimestamp() . '\')
        ');
    }

    protected function delete(Migration $migration)
    {
        $this->app->db->default->execute('
            DELETE FROM `' . self::TABLE_NAME . '`
            WHERE `time`=\'' . $migration->getTimestamp() . '\'
        ');
    }

}