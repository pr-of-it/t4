<?php

use T4\Core\Collection;
use T4\Html\Elements\Select;
use T4\Orm\Model;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class DummyModel extends Model {
    static protected $schema = [
        'columns' => [],
    ];
}

class HtmlSelectTest extends PHPUnit_Framework_TestCase {

    public function testRender()
    {
        $element = new Select();
        $this->assertEquals(
            "<select>\n</select>",
            $element->render()
        );

        $element->setAttribute('id', 'test');
        $this->assertEquals(
            "<select id=\"test\">\n</select>",
            $element->render()
        );

        $element->setAttribute('class', 'red');
        $this->assertEquals(
            "<select id=\"test\" class=\"red\">\n</select>",
            $element->render()
        );

        $element->setOption('values', [10=>'foo', 20=>'bar']);
        $this->assertEquals(
            "<select id=\"test\" class=\"red\">\n<option value=\"10\">foo</option>\n<option value=\"20\">bar</option>\n</select>",
            $element->render()
        );

        $element = new Select('category');
        $element->setOption('valueColumn', 'id');
        $collection = new Collection();

        $el1 = new DummyModel();
        $el1->id = 100;
        $el1->title = 'test100';
        $collection->append($el1);

        $el2 = new DummyModel();
        $el2->id = 200;
        $el2->title = 'test200';
        $collection->append($el2);

        $element->setOption('values', $collection);
        $this->assertEquals(
            "<select name=\"category\">\n<option value=\"100\">test100</option>\n<option value=\"200\">test200</option>\n</select>",
            $element->render()
        );

        $element->setAttribute('id', 'test');
        $this->assertEquals(
            "<select name=\"category\" id=\"test\">\n<option value=\"100\">test100</option>\n<option value=\"200\">test200</option>\n</select>",
            $element->render()
        );

        $element->setOption('null', true);
        $this->assertEquals(
            "<select name=\"category\" id=\"test\">\n<option value=\"0\">----</option>\n<option value=\"100\">test100</option>\n<option value=\"200\">test200</option>\n</select>",
            $element->render()
        );

        $element->setSelected(200);
        $this->assertEquals(
            "<select name=\"category\" id=\"test\">\n<option value=\"0\">----</option>\n<option value=\"100\">test100</option>\n<option value=\"200\" selected=\"selected\">test200</option>\n</select>",
            $element->render()
        );

    }

}