<?php

class mTestMigration extends T4\Orm\Migration
{
    public function up()
    {
        $this->createTable('comments',
            [
                'num' => ['type' => 'int'],
            ],
            [],
            ['tree']
        );
    }

    public function down()
    {
        $this->dropTable('comments');
    }
}

class CommentTestModel extends T4\Orm\Model
{
    static protected $schema = [
        'table' => 'comments',
        'columns' => [
            'num' => ['type' => 'int'],
        ]
    ];
    static protected $extensions = ['tree'];
}
