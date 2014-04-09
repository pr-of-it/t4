<?php

namespace App\Migrations;

use T4\Orm\Migration;

class m_0000000001_CreateWebApp
    extends Migration
{

    public function up()
    {
        if (!$this->existsTable('__blocks')) {
            $this->createTable('__blocks', [
                    'section'   => ['type'=>'int'],
                    'path'      => ['type'=>'string'],
                    'options'   => ['type'=>'text'],
                    'order'     => ['type'=>'int'],
                ], [
                    ['columns'=>['section']],
                    ['columns'=>['order']],
                ]
            );
        };

        if (!$this->existsTable('__users')) {
            $this->createTable('__users', [
                'email'     => ['type'=>'string'],
                'password'  => ['type'=>'string'],
            ], [
                ['columns' => ['email']],
            ]);
        }

    }

    public function down()
    {
        echo 'CreateWebApp migration is not down-able!';
    }

}