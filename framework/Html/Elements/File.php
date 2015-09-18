<?php

namespace T4\Html\Elements;

class File
    extends Simplefile
{

    public $buttonText = 'Choose file';

    public function __construct($name='', $options=[], $attributes=[])
    {
        if (isset($options['text'])) {
            $this->buttonText = $options['text'];
            unset($options['text']);
        }
        parent::__construct($name, $options, $attributes);
    }

    public function render()
    {
        $this->setAttribute('onchange', 'this.previousSibling.value=this.value');
        $this->setAttribute('style', 'position: absolute; left: 0; top: 0; width: 100%; height: 100%; transform: scale(20); letter-spacing: 10em; -ms-transform: scale(20); opacity: 0; cursor: pointer;');

        $ret = '
        <div style="position: relative; overflow: hidden; height: 4ex; line-height: 3ex; display: inline-block">
            <button type="button" style="float: right; height: 3.7ex;">' . $this->buttonText . '</button><input type="text" readonly="">' . parent::render() . '
        </div>
        ';
        return $ret;
    }

}