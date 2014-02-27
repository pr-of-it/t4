<?php

namespace T4\Html;

use T4\Orm\Model;

class Helpers
{

    const TREE_LEVEL_SYMBOL = '-';

    static public function blockOptionInput($name, $options, $value = null, $htmlOptions=[])
    {
        switch ($options['type']) {
            case 'select:tree':
                $htmlOptions['name'] = $name;
                $options['model'] = '\\App\\Models\\'.$options['model'];
                return self::selectTreeByModel($options['model'], is_null($value) ? $options['default'] : $value, $htmlOptions);
        }
    }

    static public function select($data, $selected = 0, $htmlOptions = [], $options=[])
    {
        if (empty($options['valueColumn']))
            $options['valueColumn'] = Model::PK;
        if (empty($options['titleColumn']))
            $options['titleColumn'] = 'title';
        if (is_array($selected))
            $selected = $selected[$options['valueColumn']];
        if ($selected instanceof Model) {
            $class = get_class($selected);
            $selected = $selected->{$class::PK};
        }

        $html = '<select' .
            (isset($htmlOptions['name']) ? ' name="' . $htmlOptions['name'] . '"': '') .
            (isset($htmlOptions['id']) ? ' id="' . $htmlOptions['id'] . '"': '') .
            (in_array('disabled', $htmlOptions) ? ' disabled="disabled"': '') .
            '>' . "\n";
        foreach ($data as $item) {
            $html .=
                '<option
                    value="' . $item[$options['valueColumn']] . '"' .
                    ($item[$options['valueColumn']] == $selected ? ' selected="selected"' : '') .
                '>' .
                ( in_array('tree', $options) ? str_repeat(self::TREE_LEVEL_SYMBOL, (int)$item[$options['treeLevelColumn']]) : '' ) . ' ' . $item[$options['titleColumn']] .
                '</option>' . "\n";
        }
        $html .= '</select>';
        return $html;
    }

    static public function selectTree($data, $selected = 0, $htmlOptions = [], $options=[])
    {
        $options = array_merge($options, ['tree']);
        if (empty($options['treeLevelColumn']))
            $options['treeLevelColumn'] = '__lvl';
        return self::select($data, $selected, $htmlOptions, $options);
    }

    static public function selectTreeByModel($model, $selected = 0, $htmlOptions = [], $options=[])
    {
        $data = $model::findAllTree();
        return self::selectTree($data, $selected, $htmlOptions, $options);
    }

}