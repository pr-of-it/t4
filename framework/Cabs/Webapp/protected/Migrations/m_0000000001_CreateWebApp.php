<?php

namespace App\Migrations;

use T4\Orm\Migration;

class m_0000000001_CreateWebApp
    extends Migration
{

    public function up()
    {
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
    }

    public function down()
    {
        $this->dropTable('__blocks');
    }

}