<?php

namespace T4\Tests\Dbal {

    use T4\Dbal\Condition;
    use T4\Dbal\Conditions;

    require_once realpath(__DIR__ . '/../../framework/boot.php');

    class ConditionsTest
        extends \PHPUnit\Framework\TestCase
    {
        
        public function testCreate()
        {
            $conditions = new Conditions(['NOT 42', new Condition('12<13'), '42 > 13']);

            $this->assertInstanceOf(Condition::class, $conditions->where[0]);
            $this->assertInstanceOf(Condition::class, $conditions->where[1]);
            $this->assertInstanceOf(Condition::class, $conditions->where[2]);

            $this->assertEquals('NOT 42', (string)$conditions->where[0]);
            $this->assertEquals('12 < 13', (string)$conditions->where[1]);
            $this->assertEquals('42 > 13', (string)$conditions->where[2]);
        }

        public function testCount()
        {
            $conditions = new Conditions(['NOT 42', new Condition('12<13'), '42>13']);
            $this->assertEquals(3, count($conditions));

            $conditions->order('num')->offset(100)->limit(10);
            $this->assertEquals(3, count($conditions));
        }

        public function testAppendPrepend()
        {
            $conditions = new Conditions(['NOT 42', new Condition('12<13'), '42>13']);

            $conditions->append('id=1');
            $this->assertInstanceOf(Condition::class, $conditions->where[3]);
            $this->assertEquals(4, count($conditions));
            $this->assertEquals('id = 1', (string)$conditions->where[3]);

            $conditions->prepend('name = :name');
            $this->assertEquals(5, count($conditions));
            $this->assertInstanceOf(Condition::class, $conditions->where[0]);
            $this->assertEquals('name = :name', (string)$conditions->where[0]);

            $conditions->add('address LIKE %NY%');
            $this->assertInstanceOf(Condition::class, $conditions->where[5]);
            $this->assertEquals(6, count($conditions));
            $this->assertEquals('address LIKE %NY%', (string)$conditions->where[5]);
        }

        /*
        public function testMerge()
        {
            $conditions1 = new Conditions(['NOT 42', new Condition('12<13')]);
            $conditions2 = new Conditions(['42>13']);
            $conditions1->merge($conditions2);

            $this->assertInstanceOf(Condition::class, $conditions1->where[0]);
            $this->assertInstanceOf(Condition::class, $conditions1->where[1]);
            $this->assertInstanceOf(Condition::class, $conditions1->where[2]);

            $this->assertEquals('NOT 42', (string)$conditions1->where[0]);
            $this->assertEquals('12 < 13', (string)$conditions1->where[1]);
            $this->assertEquals('42 > 13', (string)$conditions1->where[2]);
        }
        */
    }

}