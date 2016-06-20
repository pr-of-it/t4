<?php

class PagerTest extends PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $pager = new \T4\Widgets\Pager(['url' => '/test?page=%d', 'total' => 25]);
        $this->expectOutputString('        <ul class="pagination">
            <li class="disabled"><a
                    href="/test?page=1">&laquo;</a></li>
                            <li class="active">
                    <a href="/test?page=1">1</a>
                </li>
                                <li>
                    <a href="/test?page=2">2</a>
                </li>
                                <li>
                    <a href="/test?page=3">3</a>
                </li>
                            <li><a
                    href="/test?page=3">&raquo;</a>
            </li>
        </ul>
    ');
        $pager->render();
    }

}
