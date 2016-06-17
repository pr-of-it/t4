<?php

namespace T4\Html;

use T4\Core\Std;
use T4\Core\Url;

class Sort
    extends Std
{

    public function __construct($data)
    {
        if (!empty($data['by'])) {
            parent::__construct($data);
        }

    }

    public function makeUrl($url = null, $field = 'sort')
    {
        if (null == $url) {
            $url = new Url();
        }
        if (is_string($url)) {
            $url = new Url($url);
        }
        if (!empty($this->by)) {
            $url->query[$field] = $this->toArray();
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