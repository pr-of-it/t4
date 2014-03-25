<?php

namespace T4\Html;

use T4\Core\Collection;
use T4\Orm\Model;

class Helpers
{

    const TREE_LEVEL_SYMBOL = '-';

    static public function blockOptionInput($name, $options, $value = null, $htmlOptions = [])
    {
        switch ($options['type']) {
            case 'select:tree':
                $htmlOptions['name'] = $name;
                return self::selectTreeByModel($options['model'], is_null($value) ? $options['default'] : $value, $options, $htmlOptions);
        }
    }

    /**
     * Формирует <select> из заданных данных
     * @param $data Массив данных
     * @param int $selected
     * @param array $htmlOptions
     * @param array $options
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

        $html = '<select' .
            (isset($htmlOptions['name']) ? ' name="' . $htmlOptions['name'] . '"' : '') .
            (isset($htmlOptions['id']) ? ' id="' . $htmlOptions['id'] . '"' : '') .
            (in_array('disabled', $htmlOptions) ? ' disabled="disabled"' : '') .
            '>' . "\n";
        if (isset($options['null']) && $options['null']) {
            $data->prepend([$options['valueColumn']=>0, $options['titleColumn']=>'---']);
        }
        foreach ($data as $item) {
            $html .=
                '<option
                    value="' . $item[$options['valueColumn']] . '"' .
                ($item[$options['valueColumn']] == $selected ? ' selected="selected"' : '') .
                '>' .
                (in_array('tree', $options) && isset($item[$options['treeLevelColumn']]) ? str_repeat(self::TREE_LEVEL_SYMBOL, (int)$item[$options['treeLevelColumn']]) : '') . ' ' .
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
        $options = array_merge($options, ['tree']);
        if (empty($options['treeLevelColumn']))
            $options['treeLevelColumn'] = '__lvl';
        return self::select($data, $selected, $options, $htmlOptions);
    }

    static public function selectTreeByModel($model, $selected = 0, $options = [], $htmlOptions = [])
    {
        $data = $model::findAllTree();
        return self::selectTree($data, $selected, $options, $htmlOptions);
    }

}