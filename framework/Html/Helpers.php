<?php

namespace T4\Html;

use T4\Core\Collection;
use T4\Core\Std;
use T4\Html\Elements\Number;
use T4\Html\Elements\Select;
use T4\Html\Elements\Text;
use T4\Html\Elements\Textarea;
use T4\Orm\Model;

class Helpers
{

    const TREE_LEVEL_SYMBOL = '-';

    static public function element($el, $name = '', $options = [], $attrs = [])
    {
        $elClass = '\T4\Html\Elements\\' . ucfirst($el);
        $el = new $elClass($name, $options, $attrs);
        return $el->render();
    }

    static public function blockOptionInput($name, $options, $value = null, $htmlOptions = [])
    {
        $htmlOptions['name'] = $name;
        switch ($options['type']) {
            case 'int':
                $input = new Number($name, $options, $htmlOptions);
                $input->setValue(is_null($value) ? $options['default'] : $value);
                return $input->render();
            case 'string':
                $input = new Text($name, $options, $htmlOptions);
                $input->setValue(is_null($value) ? $options['default'] : $value);
                return $input->render();
            case 'text':
                $input = new Textarea($name, $options, $htmlOptions);
                $input->setValue(is_null($value) ? $options['default'] : $value);
                return $input->render();
            case 'select':
                $select = new Select($name, $options, $htmlOptions);
                $select->setOption('values', $options['values']->toArray());
                $select->setSelected(is_null($value) ? $options['default'] : $value);
                return $select->render();
            case 'select:model':
                $select = new Select($name, $options, $htmlOptions);
                $select->setOption('values', $options['model']::findAll());
                $select->setSelected(is_null($value) ? $options['default'] : $value);
                return $select->render();
            case 'select:tree':
                return self::selectTreeByModel($options['model'], is_null($value) ? $options['default'] : $value, $options, $htmlOptions);
        }
    }

    /**
     * Формирует <select> из заданных данных
     * @param \T4\Core\Collection $data Массив данных
     * @param int $selected
     * @param array $options
     * @param array $htmlOptions
     * @return string
     */
    static public function select(Collection $data, $selected = 0, $options = [], $htmlOptions = [])
    {
        if (empty($options['valueColumn']))
            $options['valueColumn'] = Model::PK;
        if (empty($options['titleColumn']))
            $options['titleColumn'] = 'title';
        if (is_array($selected))
            $selected = $selected[$options['valueColumn']];
        if ($selected instanceof Model) {
            $selected = $selected->{$options['valueColumn']};
        }
        if (empty($options['disabled']))
            $options['disabled'] = [];

        if ($options instanceof Std)
            $options = $options->getData();

        $html = '<select' .
            (isset($htmlOptions['id']) ? ' id="' . $htmlOptions['id'] . '"' : '') .
            (isset($htmlOptions['name']) ? ' name="' . $htmlOptions['name'] . '"' : '') .
            (isset($htmlOptions['class']) ? ' class="' . $htmlOptions['class'] . '"' : '') .
            (in_array('disabled', $htmlOptions) ? ' disabled="disabled"' : '') .
            '>' . "\n";
        if (isset($options['null']) && $options['null']) {
            $data->prepend([$options['valueColumn'] => 0, $options['titleColumn'] => '---']);
        }
        foreach ($data as $item) {
            $html .=
                '<option
                    value="' . $item[$options['valueColumn']] . '"' .
                ($item[$options['valueColumn']] == $selected ? ' selected="selected"' : '') .
                (in_array($item[$options['valueColumn']], $options['disabled']) ? ' disabled="disabled"' : '') .
                '>' .
                (
                    in_array('tree', $options) && isset($options['treeLevelColumn']) && isset($item[$options['treeLevelColumn']])
                        ? str_repeat(self::TREE_LEVEL_SYMBOL, (int)$item[$options['treeLevelColumn']])
                        : ''
                ) . ' ' .
                $item[$options['titleColumn']] .
                '</option>' . "\n";
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Формирует <select> из данных, с учетом их организации в виде дерева
     * @param $data
     * @param int $selected
     * @param array $htmlOptions
     * @param array $options
     * @return string
     */
    static public function selectTree(Collection $data, $selected = 0, $options = [], $htmlOptions = [])
    {
        $options = array_merge($options instanceof Std ? $options->toArray() : $options, ['tree']);
        if (empty($options['treeLevelColumn']))
            $options['treeLevelColumn'] = '__lvl';
        return self::select($data, $selected, $options, $htmlOptions);
    }

    static public function selectTreeByModel($model, $selected = 0, $options = [], $htmlOptions = [])
    {
        $data = $model::findAllTree();
        return self::selectTree($data, $selected, $options, $htmlOptions);
    }

    static public function ulTree(Collection $tree, $options = [], $htmlOptions = [])
    {
        if (0 == count($tree))
            return '';

        if (empty($options['titleColumn']))
            $options['titleColumn'] = 'title';
        if (empty($options['parent']))
            $options['parent'] = 0;

        $ret = '<ul' .
            (isset($htmlOptions['id']) ? ' id="' . $htmlOptions['id'] . '"' : '') .
            (isset($htmlOptions['class']) ? ' class="' . $htmlOptions['class'] . '"' : '') .
            ' data-parent-id="' . $options['parent'] . '">';
        $lvl = $tree[0]->__lvl;
        foreach ($tree as $index => $element) {
            if ($element->__lvl > $lvl)
                continue;
            $ret .= '<li data-id="' . $element->getPk() . '">' . $element->{$options['titleColumn']};
            if (self::hasTreeElementChildren($tree, $index)) {
                $ret .= self::ulTree(self::getAllChildrenByIndex($tree, $index), array_merge($options, ['parent' => $element->getPk()]), $htmlOptions);
            } else {
                $ret .= '<ul' .
                    (isset($htmlOptions['class']) ? ' class="' . $htmlOptions['class'] . '"' : '') .
                    ' data-parent-id="' . $element->getPk() . '"></ul>';
            }
            $ret .= '</li>';
        }
        $ret .= '</ul>';
        return $ret;
    }

    static protected function hasTreeElementChildren(Collection $tree, $index)
    {
        if (isset($tree[$index + 1]) && $tree[$index + 1]->__lvl > $tree[$index]->__lvl)
            return true;
        else
            return false;
    }

    static protected function getAllChildrenByIndex(Collection $tree, $index)
    {
        $lvl = $tree[$index]->__lvl;
        $tail = array_slice($tree->toArray(), $index + 1);
        $ret = [];
        foreach ($tail as $el) {
            if ($el->__lvl <= $lvl)
                break;
            $ret[] = $el;
        }
        return new Collection($ret);
    }

}