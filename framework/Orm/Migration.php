<?php

namespace T4\Orm;


use T4\MVC\Application;

abstract class Migration
{

    protected $db;

    final public function __construct()
    {
        $this->setDb('default');
    }

    final public function setDb($db)
    {
        $app = Application::getInstance();
        $this->db = $app->db->{$db};
    }

    final public function getName() {
        $className = get_class($this);
        preg_match('~\\\\([^\\\\]+?)$~', $className, $m);
        return $m[1];
    }

    abstract public function up();

    abstract public function down();

    final protected function createTable($tableName, $columns=[], $indexes=[]) {
        echo 'Creating table `'.$tableName.'` with '.count($columns).' columns and '.count($indexes).' indexes'."\n";
    }

} 