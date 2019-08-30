<?php

namespace PMRAtk\View\Traits;

trait SubTemplateCloneDeleteTrait {
    /*
     * template region cloning:
     * For each element of the passed array a property starting with "_t"
     * is looked for. If found, clones region and sets it to property. Deletes region
     * in $this->template
     */
    public function templateCloneAndDelete(array $a, \atk4\ui\Template $template = null) {
        if($template === null) {
            $template = $this->template;
        }

        foreach ($a as $region_name) {
            $property_name = '_t' . $region_name;
            if (!property_exists($this, $property_name)) {
                throw new \atk4\data\Exception('Region '.$region_name.' not found in '.__FUNCTION__);
            }
            $this->$property_name = $template->cloneRegion($region_name);
            $template->del($region_name);
        }
    }
}