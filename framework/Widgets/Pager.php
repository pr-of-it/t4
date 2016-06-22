<?php

namespace T4\Widgets;

use T4\Core\QueryString;
use T4\Core\Url;
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
            $url = clone $this->app->request->url;
            $this->options->url = $url;
        }

        if (is_string($this->options->url)) {
            $this->options->url = new Url($this->options->url);
        }

        $query = $this->options->url->query;
        if (empty($query)){
            $query = new QueryString();
        }

        if (!isset($query['page']) || empty($query['page'])) {
            $query['page'] = '%d';
            $this->options->url->query = $query;
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
        $url = urldecode($this->options->url);
        ?>
        <ul class="pagination">
            <li<?php echo($this->options->active == 1 ? ' class="disabled"' : ''); ?>><a
                    href="<?php printf($url, 1); ?>">&laquo;</a></li>
            <?php
            $prev = 1;
            foreach ($displayed as $page) {
                if ($page - $prev > 1) {
                    ?>
                    <li class="disabled"><a href="#">...</a></li><?php
                }
                ?>
                <li<?php echo($this->options->active == $page ? ' class="active"' : ''); ?>>
                    <a href="<?php printf($url, $page); ?>"><?php echo $page; ?></a>
                </li>
                <?php
                $prev = $page;
            }
            ?>
            <li<?php echo($this->options->active == $pagesCount ? ' class="disabled"' : ''); ?>><a
                    href="<?php printf($url, $page); ?>">&raquo;</a>
            </li>
        </ul>
    <?php
    }

}