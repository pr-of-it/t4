<?php

use T4\Core\Collection;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class Number
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

    public function testConstruct()
    {
        $collection1 = new Collection([1, 2, 3]);
        $this->assertEquals(
            [1, 2, 3],
            $collection1->toArray()
        );
        $this->assertEquals(1, $collection1[0]);
        $this->assertEquals(2, $collection1[1]);
        $this->assertEquals(3, $collection1[2]);
    }

    public function testAppendPrependAdd()
    {
        $collection = new Collection();
        $this->assertEquals(
            [],
            $collection->toArray()
        );
        $this->assertEquals(
            0,
            count($collection)
        );

        $collection->append(1);
        $this->assertEquals(
            [1],
            $collection->toArray()
        );
        $this->assertEquals(
            1,
            count($collection)
        );

        $collection->prepend(2);
        $this->assertEquals(
            [2, 1],
            $collection->toArray()
        );
        $this->assertEquals(
            2,
            count($collection)
        );

        $collection->add(3);
        $this->assertEquals(
            [2, 1, 3],
            $collection->toArray()
        );
        $this->assertEquals(
            3,
            count($collection)
        );
    }

    public function testMerge()
    {
        $collection = new Collection([1, 2]);

        $collection->merge([3, 4]);
        $this->assertCount(4, $collection);
        $expected = new Collection([1, 2, 3, 4]);
        $this->assertEquals(array_values($expected->toArray()), array_values($collection->toArray()));

        $collection->merge(new Collection([5, 6]));
        $this->assertCount(6, $collection);
        $expected = new Collection([1, 2, 3, 4, 5, 6]);
        $this->assertEquals(array_values($expected->toArray()), array_values($collection->toArray()));
    }

    public function testSlice()
    {
        $collection = new Collection([10, 20, 30, 40, 50]);
        $this->assertEquals(
            new Collection([30, 40, 50]),
            $collection->slice(2)
        );
        $this->assertEquals(
            new Collection([40, 50]),
            $collection->slice(-2)
        );
        $this->assertEquals(
            new Collection([30, 40]),
            $collection->slice(2, 2)
        );
        $this->assertEquals(
            new Collection([40]),
            $collection->slice(-2, 1)
        );
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
        $this->assertEquals(array_values($expected->toArray()), array_values($result->toArray()));

        $result = $collection->ksort();
        $expected = new Collection(['a' => -1, 'b' => 0, 'c' => 42, 1 => '1', 10 => 1, 20 => 2, 30 => 3, 31 => '111', 32 => '11']);
        $this->assertEquals(array_keys($expected->toArray()), array_keys($result->toArray()));

        $result = $collection->uasort(function ($a, $b) { return $a < $b ? 1 : ($a > $b ? -1 : 0);});
        $expected = new Collection([31 => '111', 'c' => 42, 32 => '11', 30 => 3, 20 => 2, 10 => 1, 1 => '1', 'b' => 0, 'a' => -1]);
        $this->assertEquals(array_values($expected->toArray()), array_values($result->toArray()));

        $result = $collection->uksort(function ($a, $b) { return $a < $b ? 1 : ($a > $b ? -1 : 0);});
        $expected = new Collection([32 => '11', 31 => '111', 30 => 3, 20 => 2, 10 => 1, 1 => '1', 'c' => 42, 'b' => 0, 'a' => -1]);
        $this->assertEquals(array_keys($expected->toArray()), array_keys($result->toArray()));
    }

    public function testReverse()
    {
        $collection = new Collection([10 => 1, 30 => 3, 20 => 2, 'a' => -1, 'b' => 0, 'c' => 42, '111', '11']);

        $result = $collection->reverse();
        $expected = new Collection([32 => '11', 31 => '111', 'c' => 42, 'b' => 0, 'a' => -1, 20 => 2, 30 => 3, 10 => 1]);
        $this->assertEquals($expected->toArray(), $result->toArray());
    }

    public function testMap()
    {
        $collection = new Collection([1, 2, 3]);
        $result = $collection->map(function ($x) {return $x*2;});
        $expected = new Collection([2, 4, 6]);
        $this->assertEquals(array_values($expected->toArray()), array_values($result->toArray()));
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
            $collection->toArray()
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

    public function testReduce()
    {
        $collection = new Collection([1, 2, 3, 4]);
        $reduced = $collection->reduce(0, function($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals(10, $reduced);
    }

    public function testCall()
    {
        $collection = new Collection();
        $collection->append(new Number(1));
        $collection->append(new Number(2));
        $collection->append(new Number(3));

        $collectionExpected = new Collection();
        $collectionExpected->append(new Number(2));
        $collectionExpected->append(new Number(3));
        $collectionExpected->append(new Number(4));

        $collection->increment();
        $this->assertEquals($collectionExpected, $collection);
    }

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

}