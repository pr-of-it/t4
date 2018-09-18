<?php

namespace T4\Tests\Orm\Relations\BelongsToModels {

    use T4\Orm\Model;

    class Category extends Model {
        protected static $schema = [
            'table' => 'cats',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'items' => ['type' => self::HAS_MANY, 'model' => Item::class]
            ]
        ];
    }

    class Item extends Model {
        protected static $schema = [
            'table' => 'items',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'category' => ['type' => self::BELONGS_TO, 'model' => Category::class]
            ]
        ];
    }
}

namespace T4\Tests\Orm\Relations {

    require_once realpath(__DIR__ . '/../../../framework/boot.php');

    use T4\Dbal\Query;
    use T4\Tests\Orm\Relations\BelongsToModels\Category;
    use T4\Tests\Orm\Relations\BelongsToModels\Item;

    class BelongsToSaveTest
        extends BaseTest
    {

        protected function setUp(): void
        {
            $this->getT4Connection()->execute('CREATE TABLE cats (__id SERIAL, num INT)');
            $this->getT4Connection()->execute('
              INSERT INTO cats (num) VALUES (1), (2)
            ');
            Category::setConnection($this->getT4Connection());
            $this->getT4Connection()->execute('CREATE TABLE items (__id SERIAL, num INT, __category_id BIGINT)');
            $this->getT4Connection()->execute('
              INSERT INTO items (num, __category_id) VALUES (1, 1), (2, 1), (3, 2), (4, NULL)
            ');
            Item::setConnection($this->getT4Connection());
        }

        protected function tearDown(): void
        {
            $this->getT4Connection()->execute('DROP TABLE cats');
            $this->getT4Connection()->execute('DROP TABLE items');
        }

        public function testNoChanges()
        {
            $item = Item::findByPK(1);
            $item->save();
            $item = Item::findByPK(4);
            $item->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 1],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 2],    $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4, '__category_id' => null], $data[3]);
        }


        public function testCreateWORelation()
        {
            $item = new Item;
            $item->num = 5;
            $item->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 1],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 2],    $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4, '__category_id' => null], $data[3]);
            $this->assertEquals(['__id' => 5, 'num' => 5, '__category_id' => null], $data[4]);
        }

        public function testCreateWRelation()
        {
            $item = new Item();
            $item->num = 5;
            $item->category = Category::findByPK(1);
            $item->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 1],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 2],    $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4, '__category_id' => null], $data[3]);
            $this->assertEquals(['__id' => 5, 'num' => 5, '__category_id' => 1],    $data[4]);
        }

        public function testAddExisting()
        {
            $item = Item::findByPK(4);
            $item->category = Category::findByPK(1);
            $item->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 1],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 2],    $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4, '__category_id' => 1],    $data[3]);
        }

        public function testAddNew()
        {
            $item = Item::findByPK(4);
            $item->category = new Category(['num' => 3]);
            $item->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3], $data[2]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 1],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 2],    $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4, '__category_id' => 3],    $data[3]);
        }


        public function testChange()
        {
            $item = Item::findByPK(1);
            $item->category = Category::findByPK(2);
            $item->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 2],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 2],    $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4, '__category_id' => null], $data[3]);
        }


        public function testClear()
        {
            $item = Item::findByPK(1);
            $item->category = null;
            $item->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new Query())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => null], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 2],    $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4, '__category_id' => null], $data[3]);
        }

    }

}