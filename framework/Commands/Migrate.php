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
    const CLASS_MODULE_NAME_PATTERN = 'm_%010d_%s_%s';

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

    public function actionCreate($name, $module = null)
    {
        $className = sprintf(self::CLASS_NAME_PATTERN, time(), $name);
        if (null !== $module) {
            $namespace = 'App\\Modules\\' . ucfirst($module) . '\\Migrations';
        } else {
            $namespace = self::MIGRATIONS_NAMESPACE;
        }
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
        if (null !== $module) {
            $fileName = ROOT_PATH_PROTECTED . DS . 'Modules' . DS . ucfirst($module) . DS . 'Migrations' . DS . $className . '.php';
            echo 'Migration ' . $className . ' is created in ' . ROOT_PATH_PROTECTED . DS . ucfirst($module) . DS . 'Migrations';
        } else {
            $fileName = $this->getMigrationsPath() . DS . $className . '.php';
            echo 'Migration ' . $className . ' is created in ' . $this->getMigrationsPath();
        }
        file_put_contents($fileName, $content);
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
        echo 'Migration table `' . self::TABLE_NAME . '` is created' . '\n';
    }

    protected function getLastMigrationTime()
    {
        $query = new QueryBuilder();
        $query->select('time')->from(self::TABLE_NAME)->order(Model::PK . ' DESC')->limit(1);
        return $this->app->db->default->query($query)->fetchScalar() ?: 0;
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
        $query = new QueryBuilder();
        $query->insert(self::TABLE_NAME)->values(['time' => ':time'])->params([':time' => $migration->getTimestamp()]);
        $this->app->db->default->execute($query);
    }

    protected function delete(Migration $migration)
    {
        $query = new QueryBuilder();
        if ($this->app->db->default->getDriverName() == 'mysql') {
            $column = '`time`';
        } else {
            $column = '"time"';
        }
        $query->delete(self::TABLE_NAME)->where($column . '=:time')->params([':time' => $migration->getTimestamp()]);
        $this->app->db->default->execute($query);
    }

    public function actionImport($module, $name = null)
    {
        $module = ucfirst($module);
        $migrations = [];
        $migrationsInApp = $this->getMigrationsInApp();
        $migrationsInModule = $this->getMigrationsInModule($module);
        if (null == $name) {
            $match = false;
            foreach ($migrationsInModule as $migrationInModule) {
                $migrationInModule = substr(strrchr($migrationInModule, '_'), 1);
                foreach ($migrationsInApp as $migrationInApp) {
                    if (0 !== preg_match('~^m_\d+_' . $module . '_' . $migrationInModule .'~', $migrationInApp)) {
                        $match = true;
                        break;
                    }
                    $match = false;
                }
                if (!$match) {
                    $migrations[] = $migrationInModule;
                }
            }
            if (empty($migrations)) {
                throw new Exception('All migrations is already imported');
            }
            foreach ($migrations as $migration) {
                $this->importMigration($module, $migration);
            }
        } else {
            foreach ($migrationsInApp as $migrationInApp) {
                if (0 !== preg_match('~^m_\d+_' . $module . '_' . $name .'~', $migrationInApp)) {
                    throw new Exception('Migration ' . $name . ' is already imported');
                }
            }
            $this->importMigration($module, $name);
        }
    }

    protected function getMigrationsInModule($module)
    {
        $module = ucfirst($module);
        $migrations = [];
        $migrationsDir = ROOT_PATH_PROTECTED . DS . 'Modules' . DS . $module . DS . 'Migrations';
        $pathToMigrations = \T4\Fs\Helpers::listDir($migrationsDir, \SCANDIR_SORT_DESCENDING);
        foreach ($pathToMigrations as $migration) {
            $migration = basename($migration, '.php');
            if (0 !== preg_match('~^m_\d+_(.+)~', $migration, $m)) {
                $migrations[] = $m[0];
            }
        }
        if (empty($migrations)) {
            throw new Exception($module . ' has no migrations');
        }
        return $migrations;
    }

    protected function getMigrationsInApp()
    {
        $migrations = [];
        $pathToMigrations = \T4\Fs\Helpers::listDir($this->getMigrationsPath(), \SCANDIR_SORT_DESCENDING);
        foreach ($pathToMigrations as $migration) {
            $migration = basename($migration, '.php');
            $migrations[] = $migration;
        }
        return $migrations;
    }

    protected function importMigration($module, $name)
    {
        $module = ucfirst($module);
        $extendsClassName = '';
        $migrations = $this->getMigrationsInModule($module);
        foreach ($migrations as $migration){
            if (0 !== preg_match('~^m_\d+_' . $name . '~', $migration, $m)) {
                $extendsClassName = $m[0];
                break;
            }
        }
        if ($extendsClassName == '') {
            throw new Exception('Migration ' . $name . ' in ' . $module . ' does not exist');
        }
        $className = sprintf(self::CLASS_MODULE_NAME_PATTERN, time(), $module, $name);
        $namespace = self::MIGRATIONS_NAMESPACE;
        $content = <<<FILE
<?php

namespace {$namespace};

class {$className}
    extends \App\Modules\\{$module}\Migrations\\{$extendsClassName}
{

    public function up()
    {
        parent::up();
    }

    public function down()
    {
        parent::down();
    }

}
FILE;
        $fileName = $this->getMigrationsPath() . DS . $className . '.php';
        file_put_contents($fileName, $content);
        echo 'Migration ' . $className . ' is created in ' . $this->getMigrationsPath() . "\n";
    }

}