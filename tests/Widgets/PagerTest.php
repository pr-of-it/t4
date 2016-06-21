<?php

class PagerTest extends PHPUnit_Framework_TestCase
{

    public function testRenderDefaults()
    {
        $pager = new \T4\Widgets\Pager([
            'url' => '/test?page=%d',
            'total' => 25
        ]);
        $this->expectOutputString(<<<HTML
        <ul class="pagination">
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
HTML
        );
        $this->setOutputCallback(function ($data) {
            return rtrim($data);
        });
        $pager->render();
    }

    public function testRenderFull()
    {
        $pager = new \T4\Widgets\Pager([
            'url' => '/test?page=%d',
            'total' => 25,
            'size' => 14,
            'active' => 2
        ]);
        $this->expectOutputString(<<<HTML
        <ul class="pagination">
            <li><a
                    href="/test?page=1">&laquo;</a></li>
                            <li>
                    <a href="/test?page=1">1</a>
                </li>
                                <li class="active">
                    <a href="/test?page=2">2</a>
                </li>
                            <li class="disabled"><a
                    href="/test?page=2">&raquo;</a>
            </li>
        </ul>
HTML
        );
        $this->setOutputCallback(function ($data) {
            return rtrim($data);
        });
        $pager->render();
    }
    public function testRenderUrlencoded()
    {
        $pager = new \T4\Widgets\Pager([
            'url' => urlencode('/test?page=%d'),
            'total' => 25,
            'size' => 14,
            'active' => 2
        ]);
        $this->expectOutputString(<<<HTML
        <ul class="pagination">
            <li><a
                    href="/test?page=1">&laquo;</a></li>
                            <li>
                    <a href="/test?page=1">1</a>
                </li>
                                <li class="active">
                    <a href="/test?page=2">2</a>
                </li>
                            <li class="disabled"><a
                    href="/test?page=2">&raquo;</a>
            </li>
        </ul>
HTML
        );
        $this->setOutputCallback(function ($data) {
            return rtrim($data);
        });
        $pager->render();
    }
}
