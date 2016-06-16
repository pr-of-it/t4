<?php

namespace T4\Html;

use T4\Core\Std;
use T4\Core\Url;

class Sort
    extends Std
{

    public function __construct($data)
    {
        if (!empty($this->by)) {
            parent::__construct($data);
        }

    }

    public function makeUrl(Url $url = null, $field = 'sort')
    {
        if (null == $url) {
            $url = new Url();
        }
        if (!empty($this->by)) {
            $url->query[$field] = $this;
        }

        return $url;

    }

    public function getOptions($options = [])
    {
        if (!empty($this->by)) {
            $order = ['order' => $this->by];
            if (!empty($this->direction)) {
                $order['order'] .= ' ' . $this->direction;
            }
            return array_merge($options, $order);
        } else {
            return $options;
        }
    }

}