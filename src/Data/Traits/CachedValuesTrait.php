<?php declare(strict_types=1);

namespace PMRAtk\Data\Traits;

use DateTime;
use PMRAtk\Data\CachedValue;

trait CachedValuesTrait {


    protected $_cachedValues = [];

    protected $_cachedValuesLoaded = false;


    /*
     * Load a cached value by ident
     * a timeout in seconds can be defined after which the setting becomes invalid
     * $value is usually a callable which can be used to recalculate the value in case its
     * not found or too old
     */
    public function getCachedValue(string $ident, $value, int $timeout = 0) {
        //load cached values on first time one is requested
        if(!$this->_cachedValuesLoaded) {
            foreach(new CachedValue($this->db) as $cv) {
                $this->_cachedValues[$cv->get('ident')] = clone $cv;
            }
            $this->_cachedValuesLoaded = true;
        }

        //not set? create
        if(!isset($this->_cachedValues[$ident])) {
            $this->setCachedValue($ident, $value);
            return $this->_cachedValues[$ident]->get('value');
        }
        //if a timeout is defined
        if($timeout > 0) {
            //still good?
            if($this->_cachedValues[$ident]->get('last_updated') >= (new DateTime())->modify('-'.$timeout.' Seconds')) {
                return $this->_cachedValues[$ident]->get('value');
            }
            //recalculate
            else {
                $this->setCachedValue($ident, $value);
                return $this->_cachedValues[$ident]->get('value');
            }
        }
        else {
            return $this->_cachedValues[$ident]->get('value');
        }
    }


    /*
     * set a cached value in the App
     */
    public function setCachedValue(string $ident, $value) {
        if(is_callable($value)) {
            $value = call_user_func($value);
        }
        $s = new CachedValue($this->db);
        //make sure its unique
        $s->tryLoad($ident);
        if(!$s->loaded()) {
            $s->set('ident', $ident);
        }
        $s->set('value', $value);
        $s->save();
        $this->_cachedValues[$ident] = clone $s;
    }
}