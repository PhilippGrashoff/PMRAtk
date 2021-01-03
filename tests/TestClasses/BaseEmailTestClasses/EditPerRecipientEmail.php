<?php declare(strict_types=1);

namespace PMRAtk\tests\TestClasses\BaseEmailTestClasses;

use PMRAtk\Data\Email\BaseEmail;

class EditPerRecipientEmail extends BaseEmail {
    public function loadInitialTemplate() {
        $this->set('subject', 'Bla{$testsubject}');
        $this->set('message', 'Bla{$testbody}');
    }
}