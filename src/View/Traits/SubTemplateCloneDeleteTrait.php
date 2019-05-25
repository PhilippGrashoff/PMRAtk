<?php

namespace PMRAtk\View\Traits;

trait SubTemplateCloneDeleteTrait {
    /*
     * template region cloning:
     * For each element of the passed array a property starting with "_t"
     * is looked for. If found, clones region and sets it to property. Deletes region
     * in $this->template
     */
    public function templateCloneAndDelete(array $a)
    {
        foreach ($a as $region_name) {
            $property_name = '_t' . $region_name;
            if (!property_exists($this, $property_name)) {
                continue;
            }
            $this->$property_name = $this->template->cloneRegion($region_name);
            $this->template->del($region_name);
        }
    }
}
