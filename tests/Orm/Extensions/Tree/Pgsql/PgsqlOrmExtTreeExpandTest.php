<?php

require_once realpath(__DIR__ . '/../../../../../framework/boot.php');
require_once __DIR__ . '/../testClasses.php';
require_once __DIR__ . '/../TPgsqlDbTest.php';

class PgsqlOrmExtTreeExpandTest extends PHPUnit_Extensions_Database_TestCase
{

    use TPgsqlDbTest;

    public function testExpandTreeBeforeLft()
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
        $reflector = new ReflectionMethod($ext, 'expandTreeBeforeLft');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $connection, $table, 1, 1);

        $this->_testDbElement($comment1->getPk(),   3, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   13, 14, 0, 0);
        $this->_testDbElement($comment4->getPk(),   15, 18, 0, 0);
        $this->_testDbElement($comment44->getPk(),  16, 17, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 9, 3);

        $this->_testDbElement($comment1->getPk(),   3, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 13, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 23, 5);

        $this->_testDbElement($comment1->getPk(),   3, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 13, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());
    }

    public function testExpandTreeAfterLft()
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
        $reflector = new ReflectionMethod($ext, 'expandTreeAfterLft');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $connection, $table, 1, 1);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   13, 14, 0, 0);
        $this->_testDbElement($comment4->getPk(),   15, 18, 0, 0);
        $this->_testDbElement($comment44->getPk(),  16, 17, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 9, 3);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 23, 5);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());
    }

    public function testExpandTreeBeforeElement()
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
        $reflector = new ReflectionMethod($ext, 'expandTreeBeforeElement');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $comment1, 1);

        $this->_testDbElement($comment1->getPk(),   3, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   13, 14, 0, 0);
        $this->_testDbElement($comment4->getPk(),   15, 18, 0, 0);
        $this->_testDbElement($comment44->getPk(),  16, 17, 1, $comment4->getPk());

        $comment222->refresh();
        $reflector->invoke($ext, $comment222, 3);

        $this->_testDbElement($comment1->getPk(),   3, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 13, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());
    }

    public function testExpandTreeInElementFirst()
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
        $reflector = new ReflectionMethod($ext, 'expandTreeInElementFirst');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $comment1, 1);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   13, 14, 0, 0);
        $this->_testDbElement($comment4->getPk(),   15, 18, 0, 0);
        $this->_testDbElement($comment44->getPk(),  16, 17, 1, $comment4->getPk());

        $comment222->refresh();
        $reflector->invoke($ext, $comment222, 3);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  4, 5, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());
    }

    public function testExpandTreeBeforeRgt()
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
        $reflector = new ReflectionMethod($ext, 'expandTreeBeforeRgt');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $connection, $table, 4, 1);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   13, 14, 0, 0);
        $this->_testDbElement($comment4->getPk(),   15, 18, 0, 0);
        $this->_testDbElement($comment44->getPk(),  16, 17, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 10, 3);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 22, 5);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 28, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());
    }

    public function testExpandTreeAfterRgt()
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
        $reflector = new ReflectionMethod($ext, 'expandTreeAfterRgt');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $connection, $table, 4, 1);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   13, 14, 0, 0);
        $this->_testDbElement($comment4->getPk(),   15, 18, 0, 0);
        $this->_testDbElement($comment44->getPk(),  16, 17, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 10, 3);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());

        $reflector->invoke($ext, $connection, $table, 22, 5);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());
    }

    public function testExpandTreeAfterElement()
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
        $reflector = new ReflectionMethod($ext, 'expandTreeAfterElement');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $comment1, 1);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   13, 14, 0, 0);
        $this->_testDbElement($comment4->getPk(),   15, 18, 0, 0);
        $this->_testDbElement($comment44->getPk(),  16, 17, 1, $comment4->getPk());

        $comment222->refresh();
        $reflector->invoke($ext, $comment222, 3);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());

        $comment4->refresh();
        $reflector->invoke($ext, $comment4, 5);

        $this->_testDbElement($comment1->getPk(),   1, 4, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());
    }

    public function testExpandTreeInElementLast()
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
        $reflector = new ReflectionMethod($ext, 'expandTreeInElementLast');
        $reflector->setAccessible(true);

        $reflector->invoke($ext, $comment1, 1);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 12, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 11, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 10, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   13, 14, 0, 0);
        $this->_testDbElement($comment4->getPk(),   15, 18, 0, 0);
        $this->_testDbElement($comment44->getPk(),  16, 17, 1, $comment4->getPk());

        $comment222->refresh();
        $reflector->invoke($ext, $comment222, 3);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 22, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());

        $comment4->refresh();
        $reflector->invoke($ext, $comment4, 5);

        $this->_testDbElement($comment1->getPk(),   1, 6, 0, 0);
        $this->_testDbElement($comment11->getPk(),  2, 3, 1, $comment1->getPk());
        $this->_testDbElement($comment2->getPk(),   7, 16, 0, 0);
        $this->_testDbElement($comment22->getPk(),  8, 15, 1, $comment2->getPk());
        $this->_testDbElement($comment222->getPk(), 9, 14, 2, $comment22->getPk());
        $this->_testDbElement($comment3->getPk(),   17, 18, 0, 0);
        $this->_testDbElement($comment4->getPk(),   19, 28, 0, 0);
        $this->_testDbElement($comment44->getPk(),  20, 21, 1, $comment4->getPk());
    }

}
 