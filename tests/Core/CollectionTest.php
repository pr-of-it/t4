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

    public function testArrayable()
    {
        $collection = new Collection();
        $collection[] = 1;
        $collection[] = 2;
        $collection[] = 3;
        $this->assertEquals([1,2,3], $collection->toArray());

        $collection1 = new Collection();
        $collection1->fromArray([3,4,5]);

        $collection2 = new Collection();
        $collection2[] = 3;
        $collection2[] = 4;
        $collection2[] = 5;

        $this->assertEquals($collection1, $collection2);
    }

    public function testAppendPrependCall()
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

    public function testExistElement()
    {
        $collection = new Collection();
        $el1 = new \T4\Core\Std(['id' => 1, 'title' => 'foo', 'text' => 'FooFooFoo']);
        $collection->append($el1);
        $el2 = new \T4\Core\Std(['id' => 2, 'title' => 'bar', 'text' => 'BarBarBar']);
        $collection->append($el2);

        $this->assertTrue($collection->existsElement(['id' =>  1]));
        $this->assertFalse($collection->existsElement(['id' =>  3]));
        $this->assertTrue($collection->existsElement(['title' =>  'foo']));
        $this->assertTrue($collection->existsElement(['title' =>  'foo', 'text' => 'FooFooFoo']));
        $this->assertFalse($collection->existsElement(['title' =>  'foo', 'text' => 'BarBarBar']));
    }

    public function testSort()
    {
        $collection = new Collection([10 => 1, 30 => 3, 20 => 2, 'a' => -1, 'b' => 0, 'c' => 42, 1 => '1', '111', '11']);

        $result = $collection->asort();
        $expected = new Collection(['a' => -1, 'b' => 0, 1 => '1', 10 => 1, 20 => 2, 30 => 3, 32 => '11', 'c' => 42, 31 => '111']);
        $this->assertEquals(array_values($expected->getArrayCopy()), array_values($result->getArrayCopy()));

        $result = $collection->ksort();
        $expected = new Collection(['a' => -1, 'b' => 0, 'c' => 42, 1 => '1', 10 => 1, 20 => 2, 30 => 3, 31 => '111', 32 => '11']);
        $this->assertEquals(array_keys($expected->getArrayCopy()), array_keys($result->getArrayCopy()));

        $result = $collection->uasort(function ($a, $b) { return $a < $b ? 1 : ($a > $b ? -1 : 0);});
        $expected = new Collection([31 => '111', 'c' => 42, 32 => '11', 30 => 3, 20 => 2, 10 => 1, 1 => '1', 'b' => 0, 'a' => -1]);
        $this->assertEquals(array_values($expected->getArrayCopy()), array_values($result->getArrayCopy()));

        $result = $collection->uksort(function ($a, $b) { return $a < $b ? 1 : ($a > $b ? -1 : 0);});
        $expected = new Collection([32 => '11', 31 => '111', 30 => 3, 20 => 2, 10 => 1, 1 => '1', 'c' => 42, 'b' => 0, 'a' => -1]);
        $this->assertEquals(array_keys($expected->getArrayCopy()), array_keys($result->getArrayCopy()));
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

    public function testGroup()
    {
        $collection = new Collection([
            ['date' => '2000-01-01', 'title' => 'First'],
            ['date' => '2000-01-01', 'title' => 'Second'],
            ['date' => '2000-01-02', 'title' => 'Third'],
            ['date' => '2000-01-04', 'title' => 'Fourth'],
        ]);

        $grouped = $collection->group('date');
        $this->assertEquals([
            '2000-01-01' => new Collection([['date' => '2000-01-01', 'title' => 'First'], ['date' => '2000-01-01', 'title' => 'Second']]),
            '2000-01-02' => new Collection([['date' => '2000-01-02', 'title' => 'Third']]),
            '2000-01-04' => new Collection([['date' => '2000-01-04', 'title' => 'Fourth']]),
        ], $grouped);

        $grouped = $collection->group(function ($x) {return date('m-d', strtotime($x['date']));});
        $this->assertEquals([
            '01-01' => new Collection([['date' => '2000-01-01', 'title' => 'First'], ['date' => '2000-01-01', 'title' => 'Second']]),
            '01-02' => new Collection([['date' => '2000-01-02', 'title' => 'Third']]),
            '01-04' => new Collection([['date' => '2000-01-04', 'title' => 'Fourth']]),
        ], $grouped);
    }

}