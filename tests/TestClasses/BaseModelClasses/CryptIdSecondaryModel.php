<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\Traits\CryptIdTrait;
use secondarymodelforatk\SecondaryModel;

class CryptIdSecondaryModel extends SecondaryModel {
    use CryptIdTrait;

    public $table = 'SecondaryBaseModel';
    public $counter = 0;
    public $useA = true;

    protected function _generateCryptId() {
        $this->counter ++;
        if($this->counter < 3 || $this->useA) {
            return 'a';
        }
        else {
            return $this->getRandomChar();
        }
    }
}
