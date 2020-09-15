<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\Persistence;

use atk4\core\AppScopeTrait;
use atk4\ui\App;

class ArrayWithApp extends \atk4\data\Persistence\Array_ {

    use AppScopeTrait;


    public function __construct(App $app, array $data = [])
    {
        parent::__construct($data);
        $this->app = $app;
    }
}