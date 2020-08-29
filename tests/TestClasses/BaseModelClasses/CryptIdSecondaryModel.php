<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\SecondaryBaseModel;
use PMRAtk\Data\Traits\CryptIdTrait;

class CryptIdSecondaryModel extends SecondaryBaseModel {
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
