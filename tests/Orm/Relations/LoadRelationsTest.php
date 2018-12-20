<?php

namespace T4\Tests\Orm\Relations\Models {

    use T4\Orm\Model;

    class Category extends Model {
        protected static $schema = [
            'table' => 'cats',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'items' => ['type' => self::HAS_MANY, 'model' => Item::class],
            ]
        ];
    }

    class Tag extends Model {
        protected static $schema = [
            'table' => 'tags',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'items' => ['type' => self::MANY_TO_MANY, 'model' => Item::class],
            ]
        ];
    }

    class Property extends Model {
        protected static $schema = [
            'table' => 'props',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'item' => ['type' => self::HAS_ONE, 'model' => Item::class],
            ]
        ];
    }

    class Item extends Model {
        protected static $schema = [
            'table' => 'items',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'property' => ['type' => self::BELONGS_TO, 'model' => Category::class],
                'category' => ['type' => self::BELONGS_TO, 'model' => Property::class],
                'tags' => ['type' => self::MANY_TO_MANY, 'model' => Tag::class]
            ]
        ];
    }
}

namespace T4\Tests\Orm\Relations {

    use T4\Tests\Orm\Relations\Models\Category;
    use T4\Tests\Orm\Relations\Models\Item;
    use T4\Tests\Orm\Relations\Models\Property;
    use T4\Tests\Orm\Relations\Models\Tag;

    require_once realpath(__DIR__ . '/../../../framework/boot.php');

    class LoadRelationsTest extends BaseTest
    {
        protected function setUp(): void
        {
            $this->getT4Connection()->execute('CREATE TABLE cats (__id SERIAL, num INT)');
            $this->getT4Connection()->execute('
              INSERT INTO cats (num) VALUES (1), (2)
            ');
            Category::setConnection($this->getT4Connection());

            $this->getT4Connection()->execute('CREATE TABLE tags (__id SERIAL, num INT)');
            $this->getT4Connection()->execute('
              INSERT INTO tags (num) VALUES (1), (2)
            ');
            Tag::setConnection($this->getT4Connection());

            $this->getT4Connection()->execute('CREATE TABLE props (__id SERIAL, num INT)');
            $this->getT4Connection()->execute('
              INSERT INTO props (num) VALUES (1), (2)
            ');
            Property::setConnection($this->getT4Connection());

            $this->getT4Connection()->execute('CREATE TABLE items (__id SERIAL, num INT, __category_id BIGINT, __property_id BIGINT)');
            $this->getT4Connection()->execute('
              INSERT INTO items (num, __category_id, __property_id) VALUES (1, 1, 1), (2, NULL, NULL)
            ');
            Item::setConnection($this->getT4Connection());

            $this->getT4Connection()->execute('CREATE TABLE items_to_tags (__id SERIAL, __tag_id BIGINT, __item_id BIGINT)');
            $this->getT4Connection()->execute('
              INSERT INTO items_to_tags (__tag_id, __item_id) VALUES (1, 1), (2, 2), (2, 1), (1, 2)
            ');
        }

        protected function tearDown(): void
        {
            $this->getT4Connection()->execute('DROP TABLE cats');
            $this->getT4Connection()->execute('DROP TABLE tags');
            $this->getT4Connection()->execute('DROP TABLE props');
            $this->getT4Connection()->execute('DROP TABLE items');
            $this->getT4Connection()->execute('DROP TABLE items_to_tags');
        }

        public function testGet()
        {
            /** @var Item $item */
            $item = Item::findByPK(1);
            /** @var Category $cat */
            $cat = Category::findByPK(1);
            /** @var Property $prop */
            $prop = Property::findByPK(1);

            $this->expectException(\T4\Orm\Exception::class);
            $item->isRelationLoaded('test');

            $this->assertFalse($item->isRelationLoaded('category'));
            $this->assertFalse($item->isRelationLoaded('tags'));
            $this->assertFalse($cat->isRelationLoaded('items'));
            $this->assertFalse($prop->isRelationLoaded('item'));

            $item->category;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertFalse($item->isRelationLoaded('tags'));
            $this->assertFalse($cat->isRelationLoaded('items'));
            $this->assertFalse($prop->isRelationLoaded('item'));

            $item->tags;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertTrue($item->isRelationLoaded('tags'));
            $this->assertFalse($cat->isRelationLoaded('items'));
            $this->assertFalse($prop->isRelationLoaded('item'));

            $cat->items;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertTrue($item->isRelationLoaded('tags'));
            $this->assertTrue($cat->isRelationLoaded('items'));
            $this->assertFalse($prop->isRelationLoaded('item'));

            $prop->item;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertTrue($item->isRelationLoaded('tags'));
            $this->assertTrue($cat->isRelationLoaded('items'));
            $this->assertTrue($prop->isRelationLoaded('item'));

            $item->category = null;
            $item->tags = null;
            $cat->items = null;
            $prop->item = null;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertTrue($item->isRelationLoaded('tags'));
            $this->assertTrue($cat->isRelationLoaded('items'));
            $this->assertTrue($prop->isRelationLoaded('item'));
        }

        public function testGetEmpty()
        {
            /** @var Item $item */
            $item = Item::findByPK(2);

            $this->assertFalse($item->isRelationLoaded('category'));
            $this->assertFalse($item->isRelationLoaded('tags'));
            $this->assertFalse($item->isRelationLoaded('property'));

            $item->category;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertNull($item->category);
            $this->assertFalse($item->isRelationLoaded('property'));
            $this->assertFalse($item->isRelationLoaded('tags'));
        }

        public function testSet()
        {
            /** @var Item $item */
            $item = Item::findByPK(1);
            /** @var Category $cat */
            $cat = Category::findByPK(1);
            /** @var Property $prop */
            $prop = Property::findByPK(1);
            $item->category = null;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertFalse($item->isRelationLoaded('tags'));
            $this->assertFalse($cat->isRelationLoaded('items'));
            $this->assertFalse($prop->isRelationLoaded('item'));

            $item->tags = null;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertTrue($item->isRelationLoaded('tags'));
            $this->assertFalse($cat->isRelationLoaded('items'));
            $this->assertFalse($prop->isRelationLoaded('item'));

            $cat->items = null;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertTrue($item->isRelationLoaded('tags'));
            $this->assertTrue($cat->isRelationLoaded('items'));
            $this->assertFalse($prop->isRelationLoaded('item'));

            $prop->item = null;

            $this->assertTrue($item->isRelationLoaded('category'));
            $this->assertTrue($item->isRelationLoaded('tags'));
            $this->assertTrue($cat->isRelationLoaded('items'));
            $this->assertTrue($prop->isRelationLoaded('item'));
        }
    }
}
