<?php

use T4\Core\Collection;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class Int {
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
            [2,1],
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

}