<?php
namespace T4\Html;

use T4\Core\Std;
use T4\Core\Url;

class Sort
  extends Std
{
    public function makeUrl(Url $url = null, $field = 'sort')
    {
        if (null == $url){
            $url = new Url();
        }
        if (!empty($this[$field])){
            $url->query[$field] = $this[$field];
        }
        
    }
}