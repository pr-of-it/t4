<?php

namespace T4\Html;

use T4\Core\Collection;
use T4\Core\Std;
use T4\Orm\Model;

class Helpers
{

    const TREE_LEVEL_SYMBOL = '-';

    static public function blockOptionInput($name, $options, $value = null, $htmlOptions = [])
    {
        $htmlOptions['name'] = $name;
        switch ($options['type']) {
            case 'int':
                return self::inputInt(is_null($value) ? $options['default'] : $value, $options, $htmlOptions);
            case 'select:tree':
                return self::selectTreeByModel($options['model'], is_null($value) ? $options['default'] : $value, $options, $htmlOptions);
        }
    }

    /**
     * Формирует <input type="int"> с заданным значением
     * @param int $value
     * @param array $options
     * @param array $htmlOptions
     * @return string
     */
    static public function inputInt($value = 0, $options = [], $htmlOptions = [])
    {
        $html = '<input type="number"' .
            (isset($htmlOptions['name']) ? ' name="' . $htmlOptions['name'] . '"' : '') .
            (isset($htmlOptions['id']) ? ' id="' . $htmlOptions['id'] . '"' : '') .
            (in_array('disabled', $htmlOptions) ? ' disabled="disabled"' : '') .
            ' value="' . $value . '">' . "\n";
        return $html;
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
                (in_array($item[$options['valueColumn']], $options['disabled']) ? ' disabled="disabled"' : '') .
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

}