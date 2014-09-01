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
        return new \T4\Core\Std(['driver' => 'mysql', 'host' => 'localhost', 'dbname' => 't4test', 'user' => 'root', 'password' => '']);
    }

    protected function getT4Connection()
    {
        return new \T4\Dbal\Connection($this->getT4ConnectionConfig());
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

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(2, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $comment2 = new CommentTestModel();
        $comment2->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(2, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment2->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(3, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $comment11 = new CommentTestModel();
        $comment11->parent = $comment1;
        $comment11->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment11->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(3, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);
        $id = $comment2->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(5, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $comment111 = new CommentTestModel();
        $comment111->parent = $comment11;
        $comment111->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment11->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(5, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);
        $id = $comment111->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(3, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(2, $res['__lvl']);
        $this->assertEquals($comment11->getPk(), $res['__prt']);
        $id = $comment2->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(7, $res['__lft']);
        $this->assertEquals(8, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

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

        $comment2->delete();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment11->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(3, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);
        $id = $comment3->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(5, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment4->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(7, $res['__lft']);
        $this->assertEquals(10, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment44->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(8, $res['__lft']);
        $this->assertEquals(9, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment4->getPk(), $res['__prt']);

        $comment1->delete();

        $id = $comment3->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(2, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment4->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(3, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment44->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(4, $res['__lft']);
        $this->assertEquals(5, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment4->getPk(), $res['__prt']);

        $comment3->delete();

        $id = $comment4->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment44->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(3, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment4->getPk(), $res['__prt']);

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

        $comment3->parent = $comment1;
        $comment3->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment11->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(3, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);
        $id = $comment3->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(4, $res['__lft']);
        $this->assertEquals(5, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);
        $id = $comment2->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(7, $res['__lft']);
        $this->assertEquals(12, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment22->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(8, $res['__lft']);
        $this->assertEquals(11, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment2->getPk(), $res['__prt']);
        $id = $comment222->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(9, $res['__lft']);
        $this->assertEquals(10, $res['__rgt']);
        $this->assertEquals(2, $res['__lvl']);
        $this->assertEquals($comment22->getPk(), $res['__prt']);
        $id = $comment4->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(13, $res['__lft']);
        $this->assertEquals(16, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment44->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(14, $res['__lft']);
        $this->assertEquals(15, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment4->getPk(), $res['__prt']);

        $comment22->parent = null;
        $comment22->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment11->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(3, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);
        $id = $comment3->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(4, $res['__lft']);
        $this->assertEquals(5, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);
        $id = $comment2->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(7, $res['__lft']);
        $this->assertEquals(8, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment4->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(9, $res['__lft']);
        $this->assertEquals(12, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment44->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(10, $res['__lft']);
        $this->assertEquals(11, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment4->getPk(), $res['__prt']);
        $id = $comment22->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(13, $res['__lft']);
        $this->assertEquals(16, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);
        $id = $comment222->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(14, $res['__lft']);
        $this->assertEquals(15, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment22->getPk(), $res['__prt']);

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