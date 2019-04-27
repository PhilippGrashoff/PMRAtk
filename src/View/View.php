<?php

namespace PMRAtk\View;

class View extends \atk4\ui\View {

    /*
     * shorthand for
     * $this->_someVar = $this->template->cloneRegion('LALA');
     * $this->template->del('LALA');
     */
    public function cloneAndDelete(string $region_name):\atk4\ui\Template {
        $t = $this->template->cloneRegion($region_name);
        $this->template->del($region_name);
        return clone $t;
    }


    /*
     * template region cloning:
     * For each element of the passed array a property starting with "_t"
     * is looked for. If found, clones region and sets it to property. Deletes region
     * in $this->template
     */
    public function setTemplateCloneAndDelete(array $a) {
        foreach($a as $region_name) {
            $property_name = '_t'.$region_name;
            if(!property_exists($this, $property_name)) {
                continue;
            }
            $this->$property_name = $this->cloneAndDelete($region_name);
        }
    }
}
