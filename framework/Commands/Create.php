<?php

namespace T4\Commands;

use T4\Console\Command;
use T4\Core\Std;
use T4\Orm\Migration;

class Create
    extends Command
{

    public function actionApp()
    {
        $src = \T4\ROOT_PATH.DS.'Cabs'.DS.'Webapp';
        $dst = ROOT_PATH;
        echo 'Web-application install'."\n";

        $dst = $this->read('Root path for your application', $dst);

        $publicDirName = 'www';
        $publicDirName = $this->read('Public path for your application', $publicDirName);

        // TODO: починить эту мерзость в классе Std
        $settings = new Std();$settings->db = new Std();$settings->db->default = new Std();

        $settings->db->default->driver = 'mysql';
        $settings->db->default->driver = $this->read('Default DB driver', $settings->db->default->driver);

        $settings->db->default->host = 'localhost';
        $settings->db->default->host = $this->read('Default DB hostname', $settings->db->default->host);

        $settings->db->default->dbname = 't4';
        $settings->db->default->dbname = $this->read('Default DB database name', $settings->db->default->dbname);

        $settings->db->default->user = $this->read('Default DB user name');

        $settings->db->default->password = $this->read('Default DB password', '', false);

        \T4\Fs\Helpers::copyDir($src, $dst);
        echo 'Application files are copied'."\n";

        $configFileName = $dst.DS.'protected'.DS.'config.php';
        $configFile = file_get_contents($configFileName);
        foreach ($settings->db->default as $name=>$value)
            $configFile = str_replace('{{'.$name.'}}', $value, $configFile);
        file_put_contents($configFileName, $configFile);

        rename($dst.DS.'www', $dst.DS.$publicDirName);

        $migration = new app_create_migration();
        $migration->up();

        echo 'Application is installed!'."\n";

    }

    public function actionExtension($name) {
        $name = ucfirst($name);
        $dirname = ROOT_PATH_PROTECTED.DS.'Extensions'.DS.$name;
        $nameSpace = 'APP\\Extensions\\'.$name;

        \T4\Fs\Helpers::mkDir($dirname);
        $fileName = $dirname.DS.'Extension.php';
        $content = <<<FILE
<?php

namespace {$nameSpace};

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
    }

}
FILE;
        file_put_contents($fileName, $content);
        echo 'Extension ' . $name . ' is created in ' . $dirname;

    }

}

/**
 * Создание необходимых для работы веб-приложения таблиц в базе данных
 * Class app_create_migration
 * @package T4\Commands
 */
class app_create_migration extends Migration {
    public function up()
    {
        $this->createTable('blocks', [
            'section'   => ['type'=>'int'],
            'path'      => ['type'=>'string'],
            'options'   => ['type'=>'text'],
            'order'     => ['type'=>'int'],
        ], [
            ['columns'=>['section']],
            ['columns'=>['order']],
        ]
        );
    }
    public function down()
    {
        $this->dropTable('blocks');
    }
}