<?php

namespace PMRAtk\View\Traits;

trait SubTemplateCloneDeleteTrait {

    public $templatePropertyPrefix = '_t';
    /*
     * template region cloning:
     * For each element of the passed array a property starting with "_t"
     * is looked for. If found, clones region and sets it to property. Deletes region
     * in $this->template
     */
    public function templateCloneAndDelete(array $regionNames = [], \atk4\ui\Template $template = null) {
        if($template === null) {
            $template = $this->template;
        }

        //load available region properties if none are explicitly defined
        if(!$regionNames) {
            foreach($this as $propertyName => $value) {
                if(substr($propertyName, 0, strlen($this->templatePropertyPrefix)) !== $this->templatePropertyPrefix) {
                    continue;
                }

                $regionNames[] = substr($propertyName, strlen($this->templatePropertyPrefix));
            }
        }

        foreach ($regionNames as $regionName) {
            if(!$template->hasTag($regionName)) {
                continue;
            }
            $propertyName = $this->templatePropertyPrefix . $regionName;
            $this->$propertyName = $template->cloneRegion($regionName);
            $template->del($regionName);
        }
    }
}