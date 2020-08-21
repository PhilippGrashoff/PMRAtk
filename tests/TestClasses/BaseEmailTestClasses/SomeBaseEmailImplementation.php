<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseEmailTestClasses;

use PMRAtk\Data\Email\BaseEmail;

class SomeBaseEmailImplementation extends BaseEmail {


    public function loadInitialRecipients()
    {
        $this->addRecipient('test1@easyoutdooroffice.com');
        $this->addRecipient('test2@easyoutdooroffice.com');
    }
}