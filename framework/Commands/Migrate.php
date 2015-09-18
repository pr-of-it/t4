<?php

namespace T4\Commands;

use T4\Console\Application;
use T4\Console\Command;
use T4\Console\Exception;
use T4\Dbal\QueryBuilder;
use T4\Fs\Helpers;
use T4\Orm\Migration;
use T4\Orm\Model;

class Migrate
    extends Command
{

    const TABLE_NAME = '__migrations';
    const MIGRATIONS_NAMESPACE = 'App\\Migrations';
    const CLASS_NAME_PATTERN = 'm_%010d_%s';
    const CLASS_MODULE_NAME_PATTERN = 'm_%010d_%s_%s';
    const SEARCH_FILE_NAME_PATTERN = 'm_%s_%s';

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
                $this->writeLn($migration->getName() . ' up...');
                $migration->up();
                $this->save($migration);
                $this->writeLn($migration->getName() . ' is up successfully');
            }
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
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
        $this->writeLn('Migration table `' . self::TABLE_NAME . '` is created');
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

    protected function getMigrationsPath($module = null)
    {
        if (null == $module) {
            return ROOT_PATH_PROTECTED . DS . 'Migrations';
        } else {
            return ROOT_PATH_PROTECTED . DS . 'Modules' . DS . ucfirst($module) . DS . 'Migrations';
        }

    }

    protected function getLastMigrationTime()
    {
        $query = new QueryBuilder();
        $query->select('time')->from(self::TABLE_NAME)->order(Model::PK . ' DESC')->limit(1);
        return $this->app->db->default->query($query)->fetchScalar() ?: 0;
    }

    protected function save(Migration $migration)
    {
        $query = new QueryBuilder();
        $query->insert(self::TABLE_NAME)->values(['time' => ':time'])->params([':time' => $migration->getTimestamp()]);
        $this->app->db->default->execute($query);
    }

    public function actionDown()
    {
        if (!$this->isInstalled()) {
            throw new Exception('Migrations are not installed. Use t4 /migrate command to install ones.');
        }

        $migration = $this->getLastMigration();
        if (false === $migration) {
            $this->writeLn('No migrations to down');
            $this->app->end();
        }

        try {
            $this->writeLn($migration->getName() . ' down...');
            $result = $migration->down();
            if (false !== $result) {
                $this->delete($migration);
                $this->writeLn($migration->getName() . ' is down successfully');
            } else {
                $this->writeLn($migration->getName() . ' is not downable');
            }
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
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

    public function actionCreate($name, $module = null)
    {
        $className = sprintf(self::CLASS_NAME_PATTERN, time(), $name);
        $namespace = $this->getMigrationsNamespace($module);

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

        $fileName = $this->getMigrationsPath($module) . DS . $className . '.php';

        if (!is_readable(dirname($fileName))) {
            Helpers::mkDir(dirname($fileName));
        }
        file_put_contents($fileName, $content);

        $this->writeLn('Migration ' . $className . ' is created in ' . $fileName);
    }

    protected function getMigrationsNamespace($module = null)
    {
        if (null == $module) {
            return self::MIGRATIONS_NAMESPACE;
        } else {
            return 'App\\Modules\\' . ucfirst($module) . '\\Migrations';
        }
    }

    public function actionImport($module = 'all', $name = null)
    {

        if (null == $name) {
            if ('all' == $module) {
                $modules = Helpers::listDir(ROOT_PATH_PROTECTED . DS . 'Modules');

                foreach ($modules as $module) {
                    $module = basename($module);
                    if ('.' != $module && '..' != $module && is_readable($this->getMigrationsPath($module))) {
                        $this->importMigrations($module);
                    }
                }
            } else {
                $this->importMigrations($module);
            }
        } else {
            if (!empty(glob($this->getMigrationsPath() . DS . sprintf(self::SEARCH_FILE_NAME_PATTERN, '*', ucfirst($module)) . '_' . $name . '.php'))) {
                throw new Exception('Migration ' . $name . ' is already imported');
            }

            $this->importMigration($module, $name);
        }
    }

    protected function importMigrations($module)
    {
        $migrations = [];

        $migrationsInModule = $this->getMigrations($module);

        foreach ($migrationsInModule as $migrationInModule) {

            if (!empty(glob($this->getMigrationsPath() . DS . sprintf(self::SEARCH_FILE_NAME_PATTERN, '*', ucfirst($module)) . '_' . $migrationInModule . '.php'))) {
                continue;
            }

            $migrations[] = $migrationInModule;
        }

        if (empty($migrations)) {
            $this->writeLn('All migrations is already imported');
        }

        foreach ($migrations as $migration) {
            $this->importMigration($module, $migration);
            sleep(1);
        }
    }

    protected function getMigrations($module = null)
    {
        $migrations = [];
        $migrationsDir = $this->getMigrationsPath($module);
        $pathToMigrations = Helpers::listDir($migrationsDir, \SCANDIR_SORT_ASCENDING);

        foreach ($pathToMigrations as $migration) {

            if (is_file($migration)) {
                $migrations[] = basename(preg_replace('~m_(\d+)_~', '', $migration), '.php');
            }
        }

        if (empty($migrations)) {
            $this->writeLn(ucfirst($module) . ' has no migrations');
        }

        return $migrations;
    }

    protected function importMigration($module, $name)
    {
        $module = ucfirst($module);
        $migration = glob($this->getMigrationsPath($module) . DS . sprintf(self::SEARCH_FILE_NAME_PATTERN, '*', $name) . '.php')[0];

        if (empty($migration)) {
            $this->writeLn('Migration ' . $name . ' in ' . $module . ' does not exist');
        } else {
            $extendsClassName = basename($migration, '.php');
            $className = sprintf(self::CLASS_MODULE_NAME_PATTERN, time(), ucfirst($module), $name);
            $namespace = self::MIGRATIONS_NAMESPACE;
            $content = <<<FILE
<?php

namespace {$namespace};

class {$className}
    extends \App\Modules\\{$module}\Migrations\\{$extendsClassName}
{

}
FILE;
            $fileName = $this->getMigrationsPath() . DS . $className . '.php';
            file_put_contents($fileName, $content);
            $this->writeLn('Migration ' . $className . ' is created in ' . $this->getMigrationsPath() . "\n");
        }
    }

}