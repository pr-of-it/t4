<?php

namespace T4\Commands;

use T4\Console\Application;
use T4\Console\Command;
use T4\Console\Exception;
use T4\Dbal\QueryBuilder;
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
            $migrations = $this->getMigrationsAfter($this->getLastMigrationTime());
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
            $result = $migration->down();
            if (false !== $result) {
                $this->delete($migration);
                echo $migration->getName() . ' is down successfully' . "\n";
            } else {
                echo $migration->getName() . ' is not downable' . "\n";
            }
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
        $connection = Application::getInstance(true)->db->default;
        $driver = $connection->getDriver();
        return $driver->existsTable($connection, self::TABLE_NAME);
    }

    protected function install()
    {
        $connection = Application::getInstance()->db->default;
        $driver = $connection->getDriver();
        $driver->createTable($connection, self::TABLE_NAME,
            [
                Model::PK => ['type' => 'pk'],
                'time' => ['type' => 'int'],
            ],
            [
                ['type' => 'unique', 'columns' => ['time']]
            ]
        );
        echo 'Migration table `' . self::TABLE_NAME . '` is created' . "\n";
    }

    protected function getLastMigrationTime()
    {
        $connection = Application::getInstance()->db->default;
        $query = new QueryBuilder();
        $query->select('time')->from(self::TABLE_NAME)->order(Model::PK . ' DESC')->limit(1);
        return $connection->query($query)->fetchScalar() ?: 0;
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
        $lastMigrationTime = $this->getLastMigrationTime();
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
        $connection = Application::getInstance()->db->default;
        $query = new QueryBuilder();

        $query->insert(self::TABLE_NAME)->values(['time' => ':time'])->params([':time' => $migration->getTimestamp()]);
        $connection->query($query);
    }

    protected function delete(Migration $migration)
    {
        $this->app->db->default->execute('
            DELETE FROM `' . self::TABLE_NAME . '`
            WHERE `time`=\'' . $migration->getTimestamp() . '\'
        ');
    }

}