<?php

use T4\Html\Elements\Textarea;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class HtmlTextareaTest extends PHPUnit_Framework_TestCase
{

    public function testRender()
    {
        $element = new Textarea;
        $this->assertEquals(
            '<textarea></textarea>',
            $element->render()
        );

        $element->setName('test');
        $this->assertEquals(
            '<textarea name="test"></textarea>',
            $element->render()
        );

        $element->setValue('foo');
        $this->assertEquals(
            '<textarea name="test">foo</textarea>',
            $element->render()
        );

        $element->setValue('foo<br />bar');
        $this->assertEquals(
            '<textarea name="test">foo&lt;br /&gt;bar</textarea>',
            $element->render()
        );

        $element->setAttribute('id', 'baz');
        $this->assertEquals(
            '<textarea name="test" id="baz">foo&lt;br /&gt;bar</textarea>',
            $element->render()
        );

    }

}