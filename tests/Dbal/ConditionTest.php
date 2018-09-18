<?php

namespace T4\Tests\Dbal {

    use T4\Dbal\Condition;

    require_once realpath(__DIR__ . '/../../framework/boot.php');

    class ConditionTest
        extends \PHPUnit\Framework\TestCase
    {
        
        public function testCreateUnary()
        {
            $condition = new Condition('NOT 42');
            
            $reflector = new \ReflectionObject($condition);

            $x = $reflector->getProperty('x');
            $x->setAccessible(true);
            $this->assertEquals('42', $x->getValue($condition));

            $operator = $reflector->getProperty('operator');
            $operator->setAccessible(true);
            $this->assertEquals('NOT', $operator->getValue($condition));
        }

        public function testCreateBinary()
        {
            $condition = new Condition('12 < 13');

            $reflector = new \ReflectionObject($condition);

            $x = $reflector->getProperty('x');
            $x->setAccessible(true);
            $this->assertEquals('12', $x->getValue($condition));

            $operator = $reflector->getProperty('operator');
            $operator->setAccessible(true);
            $this->assertEquals('<', $operator->getValue($condition));

            $y = $reflector->getProperty('y');
            $y->setAccessible(true);
            $this->assertEquals('13', $y->getValue($condition));
        }
        
        public function testToStringUnary()
        {
            $condition = new Condition(' NOT   42 ');
            $this->assertEquals('NOT 42', $condition->toString());

            $condition = new Condition(['operator' => 'NOT', 'x' => 13]);
            $this->assertEquals('NOT 13', $condition->__toString());
        }

        public function testToStringBinary()
        {
            $condition = new Condition(' 13 <   42 ');
            $this->assertEquals('13 < 42', $condition->toString());

            $condition = new Condition(['x' => 42, 'operator' => '>', 'y' => 13]);
            $this->assertEquals('42 > 13', $condition->__toString());
        }

    }

}