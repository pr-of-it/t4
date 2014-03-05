<?php

namespace T4\Mvc\Tags;

use T4\Mvc\Tag;

class Section
    extends Tag
{

    protected function render()
    {
        $id = $this->params->id;
        $app = Application::getInstance();
        $blocks = App\Models\Block::findAllBySection($id);

        $ret = '<section role="section" data-section-id="' . $id . '">';
        foreach ($blocks as $block) {
            $ret .= '<article role="block" data-block-id="' . $block->getPk() . '">' .
                $app->callBlock($block->path, json_decode($block->options, true)) .
                '</article>';
        }
        return $ret . '</section>';
    }

}