<?php

namespace T4\Html\Elements;

class File
    extends Input
{

    public $buttonText = 'Choose file';

    public function render()
    {
        $this->setType('file');
        $this->setAttribute('onchange', 'this.previousSibling.value=this.value');
        $this->setAttribute('style', 'position: absolute; left: 0; top: 0; width: 100%; height: 100%; transform: scale(20); letter-spacing: 10em; -ms-transform: scale(20); opacity: 0; cursor: pointer;');

        $ret = '
        <div style="position: relative; overflow: hidden; height: 1.5em; display: inline-block">
            <button type="button" style="float: right; height: 100%;">' . $this->buttonText . '</button><input type="text" readonly="">' . parent::render() . '
        </div>
        ';
        return $ret;
    }

}