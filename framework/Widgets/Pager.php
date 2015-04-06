<?php

namespace T4\Widgets;

use T4\Mvc\Widget;

class Pager
    extends Widget
{

    const DEFAULT_PAGE_SIZE = 10;
    const HEAD_MAX_ITEMS = 3;
    const TAIL_MAX_ITEMS = 3;

    public function __construct($options = [])
    {
        parent::__construct($options);

        if (!isset($this->options->total))
            throw new Exception('Total count of objects for pager is empty!');

        if (!isset($this->options->size) || empty($this->options->size))
            $this->options->size = self::DEFAULT_PAGE_SIZE;

        if (!isset($this->options->active) || empty($this->options->active))
            $this->options->active = 1;

        if (!isset($this->options->url) || empty($this->options->url)) {
            $uri = preg_replace('~(\?)?page=\d+~', '', $_SERVER['REQUEST_URI']);
            $this->options->url = $uri . (false === strpos($uri, '?') ? '?page=%d' : '&page=%d');
        }

    }

    public function render()
    {
        $pagesCount = ceil($this->options->total / $this->options->size);
        if (!$pagesCount)
            return;

        $displayed = [
            1, 2, 3,
            $this->options->active - 1, $this->options->active, $this->options->active + 1,
            $pagesCount - 2, $pagesCount - 1, $pagesCount
        ];
        $displayed = array_unique(array_filter($displayed, function ($d) use ($pagesCount) {
            return $d >= 1 && $d <= $pagesCount;
        }));

        if (($this->options->active - 1) - 3 == 2) {
            $displayed[] = 4;
        }
        if (($pagesCount - 2) - ($this->options->active + 1) == 2) {
            $displayed[] = $pagesCount - 3;
        }
        sort($displayed);
        ?>
        <ul class="pagination">
            <li<?php echo($this->options->active == 1 ? ' class="disabled"' : ''); ?>><a
                    href="<?php printf($this->options->url, 1); ?>">&laquo;</a></li>
            <?php
            $prev = 1;
            foreach ($displayed as $page) {
                if ($page - $prev > 1) {
                    ?>
                    <li class="disabled"><a href="#">...</a></li><?php
                }
                ?>
                <li<?php echo($this->options->active == $page ? ' class="active"' : ''); ?>>
                    <a href="<?php printf($this->options->url, $page); ?>"><?php echo $page; ?></a>
                </li>
                <?php
                $prev = $page;
            }
            ?>
            <li<?php echo($this->options->active == $pagesCount ? ' class="disabled"' : ''); ?>><a
                    href="<?php printf($this->options->url, $page); ?>">&raquo;</a>
            </li>
        </ul>
    <?php
    }

}