<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class HttpHelpersTest extends PHPUnit_Framework_TestCase
{

    public function testGetUniversalDomainName()
    {
        $reflector = new ReflectionMethod('\T4\Http\Helpers', 'getUniversalDomainName');
        $reflector->setAccessible(true);

        $this->assertEquals(
            '',
            $reflector->invoke(null, 'localhost')
        );
        $this->assertEquals(
            '.mail.ru',
            $reflector->invoke(null, 'www.mail.ru')
        );
        $this->assertEquals(
            '.mail.ru',
            $reflector->invoke(null, 'mail.ru')
        );
    }

}