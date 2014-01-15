<?php

namespace T4\Commands;


use T4\Console\Command;

class Migrate
    extends Command
{

    const TABLE_NAME = 'migrations';

    public function actionDefault() {
        $this->actionUp();
    }

    public function actionUp() {
        if ( !$this->isInstalled() ) {
            $this->install();
        }
    }

    public function actionDown() {

    }

    protected function isInstalled() {
        // TODO: заменить на вызов через драйвер
        $st = $this->app->db->default->execute('SHOW TABLES LIKE \'' . self::TABLE_NAME . '\'');
        if ( [] == $st->fetchAll() )
            return false;
        else
            return true;
    }

    protected function install() {
        $this->app->db->default->execute('
            CREATE TABLE  `' . self::TABLE_NAME . '` (
            `__id` SERIAL,
              `time` int(10) unsigned NOT NULL,
              UNIQUE KEY `time` (`time`)
            )
        ');
        echo 'Migration table `' . self::TABLE_NAME . '` is created'."\n";
    }

}