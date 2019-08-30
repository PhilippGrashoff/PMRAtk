<?php

namespace PMRAtk\View;

class Template extends \atk4\ui\Template {

    use \PMRAtk\Data\Traits\DateTimeHelpersTrait;

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
    public function setTagsFromModel(\atk4\data\Model $model, array $tags = [], string $prefix = '') {
        if(!$tags) {
            $tags = array_keys($model->getFields());
        }

        foreach($tags as $tag) {
            if(!$model->hasField($tag)) {
                continue;
            }
            if(!$this->hasTag($prefix.$tag)) {
                continue;
            }

            //try converting non-scalar values
            if(!is_scalar($model->get($tag))) {
                if($model->get($tag) instanceof \DateTimeInterFace) {
                    $this->set($prefix.$tag, $this->castDateTimeToGermanString($model->get($tag), $model->getField($tag)->type, true));
                }
                else {
                    $this->set($prefix.$tag, $model->getField($tag)->toString());
                }
            }
            else {
                switch($model->getField($tag)->type) {
                    case 'text': $this->setHTML($prefix.$tag, nl2br(htmlspecialchars($this->app->ui_persistence->typecastSaveField($model->getField($tag), $model->get($tag))))); break;
                    default: $this->set($prefix.$tag, $this->app->ui_persistence->typecastSaveField($model->getField($tag), $model->get($tag))); break;
                }
            }
        }
    }
}
