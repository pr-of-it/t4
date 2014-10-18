<?php

require_once realpath(__DIR__ . '/../../../../framework/boot.php');

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

class Test extends PHPUnit_Extensions_Database_TestCase

{
    protected $connection;

    public function __construct()
    {
        $config = $this->getT4ConnectionConfig();
        $this->connection = new \Pdo('mysql:dbname=' . $config->dbname . ';host=' . $config->host . '', $config->user, $config->password);
        $this->connection->query('DROP TABLE `comments`');

        $migration = new mTestMigration();
        $migration->setDb($this->getT4Connection());
        $migration->up();
    }

    protected function getT4ConnectionConfig()
    {
        return new \T4\Core\Std(['driver' => 'mysql', 'host' => '127.0.0.1', 'dbname' => 't4test', 'user' => 'root', 'password' => '']);
    }

    protected function getT4Connection()
    {
        return new \T4\Dbal\Connection($this->getT4ConnectionConfig());
    }

    protected function _testDbElement($id, $lft, $rgt, $lvl, $prt)
    {
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals($lft, $res['__lft']);
        $this->assertEquals($rgt, $res['__rgt']);
        $this->assertEquals($lvl, $res['__lvl']);
        $this->assertEquals($prt, $res['__prt']);
    }

    public function testMigrationUp()
    {
        $columns = $this->connection->query('SHOW COLUMNS FROM `comments`')->fetchAll();
        $this->assertCount(6, $columns);
        $this->assertEquals('__id', $columns[0]['Field']);
        $this->assertEquals('num', $columns[1]['Field']);
        $this->assertEquals('__lft', $columns[2]['Field']);
        $this->assertEquals('__rgt', $columns[3]['Field']);
        $this->assertEquals('__lvl', $columns[4]['Field']);
        $this->assertEquals('__prt', $columns[5]['Field']);
    }

    public function testInsert()
    {
        $this->connection->query('TRUNCATE TABLE `comments`');

        CommentTestModel::setConnection($this->getT4Connection());

        $comment1 = new CommentTestModel();
        $comment1->save();
        $this->_testDbElement($comment1->getPk(), 1, 2, 0, 0);

        $comment2 = new CommentTestModel();
        $comment2->save();
        $this->_testDbElement($comment1->getPk(), 1, 2, 0, 0);
        $this->_testDbElement($comment2->getPk(), 3, 4, 0, 0);

        $comment11 = new CommentTestModel();
        $comment11->parent = $comment1;
        $comment11->save();
        $this->_testDbElement($comment1->getPk(),  1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(), 2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),  5, 6, 0, 0);

        $comment111 = new CommentTestModel();
        $comment111->parent = $comment11;
        $comment111->save();
        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment111->getPk(), 3, 4, 2, $comment11->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 8, 0, 0);
    }

    public function testDelete()
    {
        $this->connection->query('TRUNCATE TABLE `comments`');

        CommentTestModel::setConnection($this->getT4Connection());

        $comment1 = new CommentTestModel();
        $comment1->save();
            $comment11 = new CommentTestModel();
            $comment11->parent = $comment1;
            $comment11->save();
        $comment2 = new CommentTestModel();
        $comment2->save();
            $comment22 = new CommentTestModel();
            $comment22->parent = $comment2;
            $comment22->save();
                $comment222 = new CommentTestModel();
                $comment222->parent = $comment22;
                $comment222->save();
        $comment3 = new CommentTestModel();
        $comment3->save();
        $comment4 = new CommentTestModel();
        $comment4->save();
            $comment44 = new CommentTestModel();
            $comment44->parent = $comment4;
            $comment44->save();

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   5, 10, 0, 0);
        $this->_testDbElement($comment22->getPk(),  6, 9, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 7, 8, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   11, 12, 0, 0);
        $this->_testDbElement($comment4->getPk(),   13, 16, 0, 0);
        $this->_testDbElement($comment44->getPk(),  14, 15, 1, $comment4->getPk());

        $comment2->delete();

        $this->_testDbElement($comment1->getPk(),  1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(), 2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment3->getPk(),  5, 6, 0, 0);
        $this->_testDbElement($comment4->getPk(),  7, 10, 0, 0);
        $this->_testDbElement($comment44->getPk(),  8, 9, 1, $comment4->getPk());

        $comment1->delete();

        $this->_testDbElement($comment3->getPk(),  1, 2, 0, 0);
        $this->_testDbElement($comment4->getPk(),  3, 6, 0, 0);
        $this->_testDbElement($comment44->getPk(), 4, 5, 1, $comment4->getPk());

        $comment3->delete();

        $this->_testDbElement($comment4->getPk(),  1, 4, 0, 0);
        $this->_testDbElement($comment44->getPk(), 2, 3, 1, $comment4->getPk());
    }

    public function testParentChange()
    {
        $this->connection->query('TRUNCATE TABLE `comments`');

        CommentTestModel::setConnection($this->getT4Connection());

        $comment1 = new CommentTestModel();
        $comment1->num = 1;
        $comment1->save();
            $comment11 = new CommentTestModel();
            $comment11->num = 11;
            $comment11->parent = $comment1;
            $comment11->save();
        $comment2 = new CommentTestModel();
        $comment2->num = 2;
        $comment2->save();
            $comment22 = new CommentTestModel();
            $comment22->num = 22;
            $comment22->parent = $comment2;
            $comment22->save();
                $comment222 = new CommentTestModel();
                $comment222->num = 222;
                $comment222->parent = $comment22;
                $comment222->save();
        $comment3 = new CommentTestModel();
        $comment3->num = 3;
        $comment3->save();
        $comment4 = new CommentTestModel();
        $comment4->num = 4;
        $comment4->save();
            $comment44 = new CommentTestModel();
            $comment44->num = 44;
            $comment44->parent = $comment4;
            $comment44->save();

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   5, 10, 0, 0);
        $this->_testDbElement($comment22->getPk(),  6, 9, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 7, 8, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   11, 12, 0, 0);
        $this->_testDbElement($comment4->getPk(),   13, 16, 0, 0);
        $this->_testDbElement($comment44->getPk(),  14, 15, 1, $comment4->getPk());

        $comment3->parent = $comment1;
        $comment3->save();

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment3->getPk(),   4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment4->getPk(),   13, 16, 0, 0);
        $this->_testDbElement($comment44->getPk(),  14, 15, 1, $comment4->getPk());

        $comment22->parent = null;
        $comment22->save();

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment3->getPk(),   4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 8, 0, 0);
        $this->_testDbElement($comment4->getPk(),   9, 12, 0, 0);
        $this->_testDbElement($comment44->getPk(),  10, 11, 1, $comment4->getPk());
        $this->_testDbElement($comment22->getPk(),  13, 16, 0, 0);
        $this->_testDbElement($comment222->getPk(), 14, 15, 1, $comment22->getPk());
    }

    public function testMoveBefore()
    {

        $this->connection->query('TRUNCATE TABLE `comments`');

        CommentTestModel::setConnection($this->getT4Connection());

        $comment1 = new CommentTestModel();
        $comment1->num = 1;
        $comment1->save();
            $comment11 = new CommentTestModel();
            $comment11->num = 11;
            $comment11->parent = $comment1;
            $comment11->save();
        $comment2 = new CommentTestModel();
        $comment2->num = 2;
        $comment2->save();
            $comment22 = new CommentTestModel();
            $comment22->num = 22;
            $comment22->parent = $comment2;
            $comment22->save();
                $comment222 = new CommentTestModel();
                $comment222->num = 222;
                $comment222->parent = $comment22;
                $comment222->save();
        $comment3 = new CommentTestModel();
        $comment3->num = 3;
        $comment3->save();
        $comment4 = new CommentTestModel();
        $comment4->num = 4;
        $comment4->save();
            $comment44 = new CommentTestModel();
            $comment44->num = 44;
            $comment44->parent = $comment4;
            $comment44->save();

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   5, 10, 0, 0);
        $this->_testDbElement($comment22->getPk(),  6, 9, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 7, 8, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   11, 12, 0, 0);
        $this->_testDbElement($comment4->getPk(),   13, 16, 0, 0);
        $this->_testDbElement($comment44->getPk(),  14, 15, 1, $comment4->getPk());

        $comment22->insertBefore($comment11);

        $this->_testDbElement($comment1->getPk(),   1, 8, 0, 0);
        $this->_testDbElement($comment22->getPk(),  2, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment222->getPk(), 3, 4, 2, $comment22->getPk());
        $this->_testDbElement($comment11->getPk(),  6, 7, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   9, 10, 0, 0);
        $this->_testDbElement($comment3->getPk(),   11, 12, 0, 0);
        $this->_testDbElement($comment4->getPk(),   13, 16, 0, 0);
        $this->_testDbElement($comment44->getPk(),  14, 15, 1, $comment4->getPk());
    }

    /**
     * Returns the test database connection.
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection, 'mysql');
    }

    /**
     * Returns the test dataset.
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(__DIR__ . '/OrmExtTreeTest.data.xml');
    }

}