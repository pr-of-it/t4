<?php

namespace T4\Tests\Orm\Relations\ManyToManyModels {

    use T4\Orm\Model;

    class Category extends Model {
        protected static $schema = [
            'table' => 'cats',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'items' => ['type' => self::MANY_TO_MANY, 'model' => Item::class]
            ]
        ];
    }

    class Item extends Model {
        protected static $schema = [
            'table' => 'items',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'categories' => ['type' => self::MANY_TO_MANY, 'model' => Category::class]
            ]
        ];
    }
}

namespace T4\Tests\Orm\Relations {

    require_once realpath(__DIR__ . '/../../../framework/boot.php');

    use T4\Core\Collection;
    use T4\Tests\Orm\Relations\ManyToManyModels\Category;
    use T4\Tests\Orm\Relations\ManyToManyModels\Item;

    class ManyToManySaveTest
        extends BaseTest
    {

        protected function setUp(): void
        {
            $this->getT4Connection()->execute('CREATE TABLE cats (__id SERIAL, num INT)');
            $this->getT4Connection()->execute('
              INSERT INTO cats (num) VALUES (1), (2), (3), (4)
            ');
            Category::setConnection($this->getT4Connection());

            $this->getT4Connection()->execute('CREATE TABLE items (__id SERIAL, num INT)');
            $this->getT4Connection()->execute('
              INSERT INTO items (num) VALUES (1), (2), (3), (4)
            ');
            Item::setConnection($this->getT4Connection());

            $this->getT4Connection()->execute('CREATE TABLE cats_to_items (__id SERIAL, __category_id BIGINT, __item_id BIGINT)');
            $this->getT4Connection()->execute('
              INSERT INTO cats_to_items (__category_id, __item_id) VALUES (1, 1), (2, 2), (2, 3), (3, 2)
            ');

        }

        protected function tearDown(): void
        {
            $this->getT4Connection()->execute('DROP TABLE cats');
            $this->getT4Connection()->execute('DROP TABLE items');
            $this->getT4Connection()->execute('DROP TABLE cats_to_items');
        }

        public function testNoChanges()
        {
            $cat = Category::findByPK(1);
            $cat->save();

            $item = Item::findByPK(1);
            $item->save();

            $this->assertSelectAll(Category::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll(Item::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll('cats_to_items', [
                ['__id' => 1, '__category_id' => 1, '__item_id' => 1],
                ['__id' => 2, '__category_id' => 2, '__item_id' => 2],
                ['__id' => 3, '__category_id' => 2, '__item_id' => 3],
                ['__id' => 4, '__category_id' => 3, '__item_id' => 2],
            ]);
        }

        public function testCreateWORelation()
        {
            $cat = new Category;
            $cat->num = 5;
            $cat->save();

            $item = new Item;
            $item->num = 5;
            $item->save();

            $this->assertSelectAll(Category::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
                ['__id' => 5, 'num' => 5],
            ]);

            $this->assertSelectAll(Item::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
                ['__id' => 5, 'num' => 5],
            ]);

            $this->assertSelectAll('cats_to_items', [
                ['__id' => 1, '__category_id' => 1, '__item_id' => 1],
                ['__id' => 2, '__category_id' => 2, '__item_id' => 2],
                ['__id' => 3, '__category_id' => 2, '__item_id' => 3],
                ['__id' => 4, '__category_id' => 3, '__item_id' => 2],
            ]);
        }

        public function testCreateWRelation1()
        {
            $cat = new Category;
            $cat->num = 5;
            $cat->items->add(Item::findByPK(3));
            $cat->items->add(Item::findByPK(4));
            $cat->save();

            $this->assertSelectAll(Category::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
                ['__id' => 5, 'num' => 5],
            ]);

            $this->assertSelectAll(Item::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll('cats_to_items', [
                ['__id' => 1, '__category_id' => 1, '__item_id' => 1],
                ['__id' => 2, '__category_id' => 2, '__item_id' => 2],
                ['__id' => 3, '__category_id' => 2, '__item_id' => 3],
                ['__id' => 4, '__category_id' => 3, '__item_id' => 2],
                ['__id' => 5, '__category_id' => 5, '__item_id' => 3],
                ['__id' => 6, '__category_id' => 5, '__item_id' => 4],
            ]);
        }

        public function testCreateWRelation2()
        {
            $cat = new Category;
            $cat->num = 5;
            $cat->items->add(new Item(['num' => 5]));
            $cat->save();

            $this->assertSelectAll(Category::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
                ['__id' => 5, 'num' => 5],
            ]);

            $this->assertSelectAll(Item::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
                ['__id' => 5, 'num' => 5],
            ]);

            $this->assertSelectAll('cats_to_items', [
                ['__id' => 1, '__category_id' => 1, '__item_id' => 1],
                ['__id' => 2, '__category_id' => 2, '__item_id' => 2],
                ['__id' => 3, '__category_id' => 2, '__item_id' => 3],
                ['__id' => 4, '__category_id' => 3, '__item_id' => 2],
                ['__id' => 5, '__category_id' => 5, '__item_id' => 5],
            ]);
        }

        public function testAdd1()
        {
            $cat = Category::findByPK(1);
            $cat->items->add(Item::findByPK(2));
            $cat->save();

            $this->assertSelectAll(Category::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll(Item::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll('cats_to_items', [
                ['__id' => 1, '__category_id' => 1, '__item_id' => 1],
                ['__id' => 2, '__category_id' => 2, '__item_id' => 2],
                ['__id' => 3, '__category_id' => 2, '__item_id' => 3],
                ['__id' => 4, '__category_id' => 3, '__item_id' => 2],
                ['__id' => 5, '__category_id' => 1, '__item_id' => 2],
            ]);
        }

        public function testAdd2()
        {
            $cat = Category::findByPK(1);
            $cat->items->add(new Item(['num' => 5]));
            $cat->save();

            $this->assertSelectAll(Category::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll(Item::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
                ['__id' => 5, 'num' => 5],
            ]);

            $this->assertSelectAll('cats_to_items', [
                ['__id' => 1, '__category_id' => 1, '__item_id' => 1],
                ['__id' => 2, '__category_id' => 2, '__item_id' => 2],
                ['__id' => 3, '__category_id' => 2, '__item_id' => 3],
                ['__id' => 4, '__category_id' => 3, '__item_id' => 2],
                ['__id' => 5, '__category_id' => 1, '__item_id' => 5],
            ]);
        }

        public function testUnset()
        {
            $cat = Category::findByPK(1);
            unset($cat->items[0]);
            $cat->save();

            $cat = Category::findByPK(2);
            unset($cat->items[1]);
            $cat->save();

            $this->assertSelectAll(Category::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll(Item::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll('cats_to_items', [
                ['__id' => 2, '__category_id' => 2, '__item_id' => 2],
                ['__id' => 4, '__category_id' => 3, '__item_id' => 2],
            ]);
        }

        public function testClear()
        {
            $cat = Category::findByPK(2);
            $cat->items = new Collection();
            $cat->save();

            $this->assertSelectAll(Category::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll(Item::getTableName(), [
                ['__id' => 1, 'num' => 1],
                ['__id' => 2, 'num' => 2],
                ['__id' => 3, 'num' => 3],
                ['__id' => 4, 'num' => 4],
            ]);

            $this->assertSelectAll('cats_to_items', [
                ['__id' => 1, '__category_id' => 1, '__item_id' => 1],
                ['__id' => 4, '__category_id' => 3, '__item_id' => 2],
            ]);
        }

    }

}