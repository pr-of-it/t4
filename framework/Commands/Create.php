<?php

namespace T4\Commands;

use App\Models\User;
use T4\Console\Command;
use T4\Core\Std;

class Create
    extends Command
{

    public function actionExtension($name)
    {
        $name = ucfirst($name);
        $dirname = ROOT_PATH_PROTECTED . DS . 'Extensions' . DS . $name;
        $nameSpace = 'APP\\Extensions\\' . $name;

        \T4\Fs\Helpers::mkDir($dirname);
        $fileName = $dirname . DS . 'Extension.php';
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
        $this->writeLn('Extension ' . $name . ' is created in ' . $dirname);

    }

}