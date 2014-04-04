<?php

namespace T4\Widgets;

use T4\Core\Widget;

class Pager
    extends Widget
{

    const DEFAULT_PAGE_SIZE = 10;
    const HEAD_MAX_ITEMS = 3;
    const TAIL_MAX_ITEMS = 3;

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

        $displayed = array_unique([1,2,3,$this->options->active-1,$this->options->active,$this->options->active+1,$pagesCount-2,$pagesCount-1,$pagesCount]);
        $delimiters = [];

        ?>
        <ul class="pagination">
            <li<?php echo ($this->options->active==1 ? ' class="disabled"' : ''); ?>><a href="#">&laquo;</a></li>
        <?php
        for ($i = 1; $i<=$pagesCount; $i++) {
            if (in_array($i, $displayed)) {
                ?><li<?php echo ($this->options->active==$i ? ' class="active"' : ''); ?>><a href="#"><?php echo $i; ?></a></li><?php
            }
            if (in_array($i, $delimiters)) {
                ?><li class="disabled"><a href="#">...</a></li><?php
            }
        }
        ?>
            <li<?php echo ($this->options->active==$pagesCount ? ' class="disabled"' : ''); ?>><a href="#">&raquo;</a></li>
        </ul>
        <?php
    }

}

/*

Первые три показывать:
- всегда

Текущий -2 показывать:
- если он стоит на позиции 4
- иначе если он стоит на позиции >4 показать вместо него разделитель

Текущий -1 показывать:
- всегда
Текущий 0 показывать:
- всегда
Текущий +1 показывать:
- всегда

Текущий +2 показывать:
- если он на позиции последний -3
- иначе показать если он на позиции <-3 вместо него разделитель

Последние три показывать:
- всегда

 */