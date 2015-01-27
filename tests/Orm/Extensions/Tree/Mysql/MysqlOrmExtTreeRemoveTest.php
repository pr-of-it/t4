<?php

require_once realpath(__DIR__ . '/../../../../../framework/boot.php');
require_once __DIR__ . '/../testClasses.php';
require_once __DIR__ . '/../TMysqlDbTest.php';

class MysqlOrmExtTreeRemoveTest extends PHPUnit_Extensions_Database_TestCase
{

    use TMysqlDbTest;

    public function testRemoveFromTreeByLftRgt()
    {
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

        $connection = CommentTestModel::getDbConnection();
        $table = CommentTestModel::getTableName();

        $ext = new \T4\Orm\Extensions\Tree();
        $reflector = new ReflectionMethod($ext, 'removeFromTreeByLftRgt');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $connection, $table, 11, 12);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   5, 10, 0, 0);
        $this->_testDbElement($comment22->getPk(),  6, 9, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 7, 8, 2, $comment22->getPk());
        //$this->_testDbElement($comment3->getPk(),   11, 12, 0, 0);
        $this->_testDbElement($comment4->getPk(),   11, 14, 0, 0);
        $this->_testDbElement($comment44->getPk(),  12, 13, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 5, 10);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        //$this->_testDbElement($comment2->getPk(),   5, 10, 0, 0);
        //$this->_testDbElement($comment22->getPk(),  6, 9, 1, $comment2->getPk());
        //$this->_testDbElement($comment222->getPk(), 7, 8, 2, $comment22->getPk());
        //$this->_testDbElement($comment3->getPk(),   11, 12, 0, 0);
        $this->_testDbElement($comment4->getPk(),   5, 8, 0, 0);
        $this->_testDbElement($comment44->getPk(),  6, 7, 1, $comment4->getPk());
    }

    public function testRemoveFromTreeByElement()
    {
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

        $ext = new \T4\Orm\Extensions\Tree();
        $reflector = new ReflectionMethod($ext, 'removeFromTreeByElement');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $comment3);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   5, 10, 0, 0);
        $this->_testDbElement($comment22->getPk(),  6, 9, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 7, 8, 2, $comment22->getPk());
        //$this->_testDbElement($comment3->getPk(),   11, 12, 0, 0);
        $this->_testDbElement($comment4->getPk(),   11, 14, 0, 0);
        $this->_testDbElement($comment44->getPk(),  12, 13, 1, $comment4->getPk());

        $comment2->refreshTreeColumns();
        $reflector->invoke($ext, $comment2);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        //$this->_testDbElement($comment2->getPk(),   5, 10, 0, 0);
        //$this->_testDbElement($comment22->getPk(),  6, 9, 1, $comment2->getPk());
        //$this->_testDbElement($comment222->getPk(), 7, 8, 2, $comment22->getPk());
        //$this->_testDbElement($comment3->getPk(),   11, 12, 0, 0);
        $this->_testDbElement($comment4->getPk(),   5, 8, 0, 0);
        $this->_testDbElement($comment44->getPk(),  6, 7, 1, $comment4->getPk());
    }

}
 