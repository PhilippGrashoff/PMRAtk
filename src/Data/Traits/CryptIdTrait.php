<?php

namespace PMRAtk\Data\Traits;

/*
 * TODO: See if rand() is good to use for creating truly random stuff
 */
trait CryptIdTrait {

    use \PMRAtk\Data\Traits\UniqueFieldTrait;

    public $possibleChars = [
        '1','2','3','4','5','6','7','8','9',
        'a','b','c','d','e','f','g','h','i','j','k','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
        'A','B','C','D','E','F','G','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z',
    ];


    /*
     * sets crypt_id. Only does something if crypt_id is empty
     */
    public function setCryptId(string $field_name) {
        if(!$this->get($field_name)) {
            $this->_setCryptId($field_name);
            //check if another Record has the same crypt_id, if so generate a new one
            while(!$this->isFieldUnique($field_name)) {
                $this->_setCryptId($field_name);
            }
        }
    }


    /*
     *
     */
    protected function _setCryptId(string $field_name) {
        $this->set($field_name,  $this->_generateCryptId());
    }


    /*
     * Overwrite to your own needs
     */
    protected function _generateCryptId() {
        throw new \atk4\data\Exception(__FUNCTION__.' must be extended in child model');
    }


    /*
     *
     */
    public function getRandomChar() {
        return $this->possibleChars[rand(0, count($this->possibleChars)-1)];
    }
}
