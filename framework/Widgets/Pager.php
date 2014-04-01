<?php

namespace T4\Widgets;

use T4\Core\Widget;

class Pager
    extends Widget
{

    const DEFAULT_PAGE_SIZE = 10;

    public function __construct($options=[])
    {
        parent::__construct($options);

        if (!isset($this->options->total))
            throw new Exception('Total count of objects for pager is empty!');

        if (!isset($this->options->size) || empty($this->options->size))
            $this->options->size = self::DEFAULT_PAGE_SIZE;

        if (!isset($this->options->active) || empty($this->options->active))
            $this->options->active = 1;

    }

    public function render()
    {
        $pagesCount = ceil($this->options->total / $this->options->size);

        ?>
        <ul class="pagination">
            <li<?php echo ($this->options->active==1 ? ' class="disabled"' : ''); ?>><a href="#">&laquo;</a></li>
        <?php
        for ($i = 1; $i<=$pagesCount; $i++) {
            ?><li<?php echo ($this->options->active==$i ? ' class="active"' : ''); ?>><a href="#"><?php echo $i; ?></a></li><?php
        }
        ?>
            <li<?php echo ($this->options->active==$pagesCount ? ' class="disabled"' : ''); ?>><a href="#">&raquo;</a></li>
        </ul>
        <?php
    }

}