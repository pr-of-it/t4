<?php

namespace T4\Mvc\Tags;

use T4\Core\Exception;
use T4\Core\Std;
use T4\Mvc\Application;
use T4\Mvc\Tag;

class Section
    extends Tag
{

    public function render()
    {
        $id = $this->params->id;
        $app = Application::instance();
        $blocks = \App\Models\Block::findAllBySection($id, ['order'=>'`order`']);

        $ret = '<section role="section" data-section-id="' . $id . '">' . "\n";
        foreach ($blocks as $block) {

            try {
                $content = $app->callBlock($block->path, $block->template, new Std(json_decode($block->options, true)));
            } catch (Exception $e) {
                $content = $e->getMessage();
            }

            $ret .= '<article role="block" data-block-id="' . $block->getPk() . '">' . $content . '</article>' . "\n";

        }
        return $ret . '</section>' . "\n";
    }

}