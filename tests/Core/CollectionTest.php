<?php

use T4\Core\Collection;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class Int
{
    protected $data;

    public function __construct($x)
    {
        $this->data = $x;
    }

    public function increment()
    {
        $this->data++;
    }
}

class CollectionTest extends PHPUnit_Framework_TestCase
{

    public function testCollection()
    {
        $collection = new Collection();
        $this->assertEquals(
            [],
            $collection->getArrayCopy()
        );
        $this->assertEquals(
            0,
            count($collection)
        );

        $collection->append(1);
        $this->assertEquals(
            [1],
            $collection->getArrayCopy()
        );
        $this->assertEquals(
            1,
            count($collection)
        );

        $collection->prepend(2);
        $this->assertEquals(
            [2, 1],
            $collection->getArrayCopy()
        );
        $this->assertEquals(
            2,
            count($collection)
        );

        $collection = new Collection();
        $collection->append(new Int(1));
        $collection->append(new Int(2));
        $collection->append(new Int(3));

        $collectionExpected = new Collection();
        $collectionExpected->append(new Int(2));
        $collectionExpected->append(new Int(3));
        $collectionExpected->append(new Int(4));

        $collection->increment();

        $this->assertEquals($collectionExpected, $collection);


    }

    public function testCollect()
    {
        $i1 = new \T4\Core\Std(['id' => 1, 'title' => 'foo']);
        $i2 = new \T4\Core\Std(['id' => 2, 'title' => 'bar']);
        $i3 = new \T4\Core\Std(['id' => 3, 'title' => 'baz']);

        $collection = new Collection();
        $collection->append($i1);
        $collection->append($i2);
        $collection->append($i3);

        $this->assertEquals(
            [new \T4\Core\Std(['id' => 1, 'title' => 'foo']), new \T4\Core\Std(['id' => 2, 'title' => 'bar']), new \T4\Core\Std(['id' => 3, 'title' => 'baz'])],
            $collection->getArrayCopy()
        );

        $ids = $collection->collect('id');
        $this->assertEquals([1, 2, 3], $ids);

        $titles = $collection->collect(function ($x) {
            return $x->title;
        });
        $this->assertEquals(['foo', 'bar', 'baz'], $titles);

        $collection = new Collection([
            ['id' => 1, 'title' => 'foo'],
            ['id' => 2, 'title' => 'bar'],
            ['id' => 3, 'title' => 'baz'],
        ]);

        $ids = $collection->collect('id');
        $this->assertEquals([1, 2, 3], $ids);

        $titles = $collection->collect(function ($x) {
            return $x['title'];
        });
        $this->assertEquals(['foo', 'bar', 'baz'], $titles);
    }

}