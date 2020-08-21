<?php declare(strict_types=1);

namespace PMRAtk\Data\Email;

use PMRAtk\Data\SecondaryBaseModel;

class EmailTemplate extends SecondaryBaseModel {

    public $table = 'email_template';


    /**
     *
     */
    public function init(): void {
        parent::init();

        $this->addFields([
            ['ident',        'type' => 'string', 'system' => true],
        ]);

        $this->setOrder('ident');
    }
}
