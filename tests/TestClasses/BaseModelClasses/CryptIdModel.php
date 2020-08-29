<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use atk4\data\Model;
use PMRAtk\Data\Traits\CryptIdTrait;


class CryptIdModel extends Model {
    use CryptIdTrait;

    public $table = 'SecondaryBaseModel';
}