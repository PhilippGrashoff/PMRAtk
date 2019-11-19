<?php

namespace PMRAtk\Data;

/**
 * This class represents a message for logged in users. The main concept is to display unread messages on login to
 * inform each individual user about updates.
 */
class MessageForUserToUser extends \atk4\data\Model {

    public $table = 'message_for_user_to_user';

    /*
     *
     */
    public function init() {
        parent::init();
        $this->addFields([
            ['is_read', 'type' => 'integer', 'caption' => 'wurde von Benutzer gelesen'],
        ]);

        $this->hasOne('message_for_user_id', MessageForUser::class);
        $this->hasOne('user_id', User::class);
    }
}