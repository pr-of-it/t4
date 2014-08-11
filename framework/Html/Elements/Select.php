<?php

namespace T4\Html\Elements;

use T4\Core\Collection;
use T4\Html\Element;
use T4\Orm\Model;

class Select
    extends Element
{

    const NULL_VALUE = 0;
    const NULL_TITLE = '----';
    const TREE_LEVEL_SYMBOL = '-';

    /**
     * @param $value
     * @return $this
     */
    public function setSelected($value)
    {
        $this->options->selected = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        if (empty($this->options->valueColumn))
            $this->options->valueColumn = Model::PK;
        if (empty($this->options->titleColumn))
            $this->options->titleColumn = 'title';

        if (empty($this->options->disabled))
            $this->options->disabled = [];

        if ($this->options->selected instanceof Model) {
            $this->options->selected = $this->options->selected->{$this->options->valueColumn};
        }

        if (!empty($this->options->isTree)) {
            if (empty($this->options->treeLevelColumn))
                $this->options->treeLevelColumn = '__lvl';
        }

        $attrs = $this->getAttributesStr();
        $res = '<select' . ($attrs ? ' ' . $attrs : '') . '>' . "\n";

        if (!empty($this->options->values)) {

            if (!empty($this->options->null)) {
                $data = [self::NULL_VALUE => self::NULL_TITLE];
            } else {
                $data = [];
            }
            $levels = [];
            switch (true) {
                case is_array($this->options->values):
                    $data = $this->options->values;
                    break;
                case $this->options->values instanceof Collection:
                    foreach ($this->options->values as $model) {
                        $data[$model->{$this->options->valueColumn}] = $model->{$this->options->titleColumn};
                        if (!empty($this->options->isTree)) {
                            $levels[$model->{$this->options->valueColumn}] = $model->{$this->options->treeLevelColumn};
                        }
                    }
                    break;
            }

            foreach ($data as $value => $title) {
                $selected = !empty($this->options->selected) && ($value ==  $this->options->selected);
                $res .= '<option value="' . $value . '"' . ($selected ? ' selected="selected"' : '') . '>' .
                        ( !empty($this->options->isTree) ? str_repeat(self::TREE_LEVEL_SYMBOL, $levels[$value]) . ' ' : '') .
                        $title .
                        '</option>' . "\n";
            }

        }

        $res .= '</select>';
        return $res;
    }

}