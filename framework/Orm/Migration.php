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

    abstract public function up();

    abstract public function down();

    final protected function createTable($tableName, $columns=[], $indexes=[]) {
        echo 'Creating table `'.$tableName.'` with '.count($columns).' columns and '.count($indexes).' indexes'."\n";
    }

} 