<?php

namespace T4\Html;

use T4\Orm\Model;

class Helpers
{

    const TREE_LEVEL_SYMBOL = '--';

    static public function select($data, $value = Model::PK, $title = 'title', $selected = 0, $htmlOptions = [])
    {
        $html = '<select>' . "\n";
        foreach ($data as $item) {
            $html .=
                '<option value="' . $item[$value] . '"' . ($item[$value] == $selected ? ' selected="selected"' : '') . '>' .
                $item[$title] .
                '</option>' . "\n";
        }
        $html .= '</select>';
        return $html;
    }

    static public function selectTree($data, $value = Model::PK, $title = 'title', $selected = 0, $htmlOptions = [])
    {
        $html = '<select>' . "\n";
        foreach ($data as $item) {
            $html .=
                '<option value="' . $item[$value] . '"' . ($item[$value] == $selected ? ' selected="selected"' : '') . '>' .
                str_repeat(self::TREE_LEVEL_SYMBOL, $item['level']) . $item[$title] .
                '</option>' . "\n";
        }
        $html .= '</select>';
        return $html;
    }

    static public function selectTreeByModel($model, $title = 'title', $selected = 0, $htmlOptions = [])
    {
        $data = $model::findAllTree();
        return self::selectTree($data, $model::PK, $title, $selected, $htmlOptions);
    }

}