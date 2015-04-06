<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class Book extends \T4\Orm\Model
{
    protected static $schema = [
        'columns' => [
            'title' => ['type' => 'string'],
            'author' => ['type' => 'string'],
        ],
    ];
}

class OrmModelTest extends PHPUnit_Extensions_Database_TestCase
{

    protected $connection;

    protected function getT4ConnectionConfig()
    {
        return new \T4\Core\Std(['driver' => 'mysql', 'host' => '127.0.0.1', 'dbname' => 't4test', 'user' => 'root', 'password' => '']);
    }

    protected function getT4Connection()
    {
        return new \T4\Dbal\Connection($this->getT4ConnectionConfig());
    }

    public function __construct()
    {
        $config = $this->getT4ConnectionConfig();
        $this->connection = new \Pdo('mysql:dbname=' . $config->dbname . ';host=' . $config->host . '', $config->user, $config->password);
        $this->connection->query('DROP TABLE `books`');
        $this->connection->query('CREATE TABLE `books` (`__id` SERIAL, `title` VARCHAR(255), `author` VARCHAR(100))');
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
        return $this->createXMLDataSet(__DIR__ . '/OrmModelTest.data.xml');
    }

    public function testConnection()
    {
        $connection = $this->getT4Connection();

        $connection->execute("
            INSERT INTO `books`
              (`title`, `author`)
            VALUES
              ('Foo', 'Bar')
        ");
        $this->assertEquals(
            1,
            $connection->lastInsertId()
        );

        $res = $connection->query("SELECT COUNT(*) FROM `books`")->fetchScalar();
        $this->assertEquals(
            1,
            $res
        );

        $res = $connection->query("SELECT * FROM `books`")->fetchAll();
        $this->assertCount(1, $res);
        $this->assertEquals(
            'Foo',
            $res[0]['title']
        );
        $this->assertEquals(
            'Bar',
            $res[0]['author']
        );

        $connection->query("UPDATE `books` SET `title`='Baz' WHERE `__id`=:id", [':id' => 1]);
        $res = $connection->query("SELECT * FROM `books` WHERE `__id`=:id", [':id' => 1])->fetchObject();
        $this->assertEquals(
            'Baz',
            $res->title
        );

        $connection->execute("DELETE FROM `books` WHERE `__id`=:id", [':id' => 1]);
        $res = $connection->query("SELECT COUNT(*) FROM `books`")->fetchScalar();
        $this->assertEquals(
            0,
            $res
        );
    }

    public function testModelStaticMethods()
    {
        $connection = $this->getT4Connection();
        Book::setConnection($connection);

        $query = $connection->prepare("INSERT INTO `books` (`title`, `author`) VALUES (:title, :author)");
        $query->execute([':title' => 'Foo', ':author' => 'Bar']);
        $query->execute([':title' => 'Baz', ':author' => 'Bla']);

        $book1attributes = ['__id' => 1, 'title' => 'Foo', 'author' => 'Bar'];
        $book2attributes = ['__id' => 2, 'title' => 'Baz', 'author' => 'Bla'];

        $books = Book::findAllByQuery("SELECT * FROM `books`");
        $this->assertInstanceOf('\T4\Core\Collection', $books);
        $this->assertCount(2, $books);
        $this->assertInstanceOf('Book', $books[0]);
        $this->assertInstanceOf('Book', $books[1]);
        $this->assertEquals($book1attributes, $books[0]->toArray());
        $this->assertEquals($book2attributes, $books[1]->toArray());

        $book = Book::findByQuery("SELECT * FROM `books` WHERE `__id`=:id", [':id' => 1]);
        $this->assertInstanceOf('Book', $book);
        $this->assertEquals($book1attributes, $book->toArray());

        $books1 = Book::findAllByQuery("SELECT * FROM `books`");
        $books2 = Book::findAll();
        $this->assertEquals($books1, $books2);

        $book1 = Book::findByPK(1);
        $this->assertEquals(
            'Foo',
            $book1->title
        );
        $this->assertEquals(
            'Bar',
            $book1->author
        );

        $book1 = Book::findByColumn('__id', 2);
        $this->assertEquals(
            'Baz',
            $book1->title
        );
        $this->assertEquals(
            'Bla',
            $book1->author
        );

    }

    public function testCreateDelete()
    {
        $connection = $this->getT4Connection();
        Book::setConnection($connection);

        $book = new Book();
        $book->title = 'War and peace';
        $book->author = 'Tolstoi';

        $this->assertTrue($book->isNew());
        $this->assertFalse($book->isDeleted());

        $book->save();

        $this->assertFalse($book->isNew());
        $this->assertTrue($book->wasNew());
        $this->assertFalse($book->isDeleted());

        $this->assertEquals(
            1,
            $book->getPk()
        );

        $book1 = Book::findByPK(1);
        $this->assertEquals(
            1,
            $book1->getPk()
        );
        $this->assertEquals(
            'War and peace',
            $book1->title
        );
        $this->assertEquals(
            'Tolstoi',
            $book1->author
        );

        $book->delete();

        $this->assertFalse($book->isNew());
        $this->assertTrue($book->wasNew());
        $this->assertTrue($book->isDeleted());

        $count = $connection->query("SELECT COUNT(*) FROM `books`")->fetchScalar();
        $this->assertEquals(0, $count);

    }

}