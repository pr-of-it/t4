<?php

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class InputTest extends PHPUnit_Framework_TestCase
{

    public function testText()
    {

        $input = new \T4\Html\Elements\Text;
        $this->assertEquals(
            '<input type="text" />',
            $input->render()
        );

        $input->setName('test');
        $this->assertEquals(
            '<input name="test" type="text" />',
            $input->render()
        );

        $input->setValue('123');
        $this->assertEquals(
            '<input name="test" type="text" value="123" />',
            $input->render()
        );

        $input->setAttribute('id', 'foo');
        $this->assertEquals(
            '<input name="test" type="text" value="123" id="foo" />',
            $input->render()
        );

    }

    public function testNumber()
    {

        $input = new \T4\Html\Elements\Number;
        $this->assertEquals(
            '<input type="number" />',
            $input->render()
        );

        $input->setName('test');
        $this->assertEquals(
            '<input name="test" type="number" />',
            $input->render()
        );

        $input->setValue('123');
        $this->assertEquals(
            '<input name="test" type="number" value="123" />',
            $input->render()
        );

        $input->setAttribute('id', 'foo');
        $this->assertEquals(
            '<input name="test" type="number" value="123" id="foo" />',
            $input->render()
        );

    }

}