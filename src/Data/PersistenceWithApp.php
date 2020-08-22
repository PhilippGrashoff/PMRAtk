<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Persistence;
use atk4\core\AppScopeTrait;


class PersistenceWithApp extends Persistence {
    use AppScopeTrait;
}