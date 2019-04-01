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
}
