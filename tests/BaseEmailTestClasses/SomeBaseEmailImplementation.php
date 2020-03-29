<?php

namespace PMRAtk\tests\BaseEmailTestClasses;

use PMRAtk\Data\Email\BaseEmail;

class SomeBaseEmailImplementation extends BaseEmail {


    public function loadInitialRecipients()
    {
        $this->addRecipient('test1@easyoutdooroffice.com');
        $this->addRecipient('test2@easyoutdooroffice.com');
    }
}