<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class HelpersTest extends PHPUnit_Framework_TestCase {

    public function testStrings()
    {
        $this->assertEquals(
            ['test'=>null],
            \T4\Core\Helpers::canonize('test')
        );
        $this->assertEquals(
            ['test1'=>null, 'test2'=>null],
            \T4\Core\Helpers::canonize('test1, test2')
        );
    }

}