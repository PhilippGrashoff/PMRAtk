<?php

namespace PMRAtk\View;

class Template extends \atk4\ui\Template {

    /*
     *
     */
    public function setSTDValues() {
        $this->trySet($this->app->getAllSTDSettings());
    }


    /*
     *
     */
    public function setGermanList(string $tag, array $a) {
        $string = '';
        $counter = 0;
        foreach($a as $item) {
            $counter++;
            if(empty($item)) {
                continue;
            }
            if($counter === 1) {
                $string .= $item;
            }
            elseif($counter === count($a)) {
                $string .= ' und '.$item;
            }
            else {
                $string .= ', '.$item;
            }
        }
        $this->set($tag, $string);
    }


    /*
     * Tries to set each passed tag with its value from passed model
     */
    public function setTagsFromModel(\atk4\data\Model $model, array $tags) {
        if(!$model->loaded()) {
            throw new \atk4\data\Exception('Model needs to be loaded in '.__FUNCTION__);
        }
        foreach($tags as $tag) {
            if(!$model->hasElement($tag)
            || !$model->getElement($tag) instanceof \atk4\data\Field) {
                continue;
            }
            $this->set($tag, $model->get($tag));
        }
    }
}
