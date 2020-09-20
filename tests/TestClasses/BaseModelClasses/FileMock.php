<?php

declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseModelClasses;

use PMRAtk\Data\File;

class FileMock extends File {

    public function uploadFile($f)
    {
        $this->set('value', $f['name']);
        $this->set('path', $f['path']);
        return true;
    }
}
