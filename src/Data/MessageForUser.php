<?php

namespace PMRAtk\Data;

/**
 * This class represents a message for logged in users. The main concept is to display unread messages on login to
 * inform each individual user about updates.
 */
class MessageForUser extends BaseModel {

    public $table = 'message_to_user';

    /*
     *
     */
    public function init() {
        parent::init();
        $this->addFields([
            ['title',   'type' => 'string',  'caption' => 'Titel'],
            ['text',    'type' => 'Text',    'caption' => 'Nachricht'],
        ]);
    }
}