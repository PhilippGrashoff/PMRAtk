<?php declare(strict_types=1);

namespace PMRAtk\Data;

use mtomforatk\MToMModel;


/**
 * This class represents a message for logged in users. The main concept is to display unread
 * messages on login to inform each individual user about updates.
 */
class MessageForUserToUser extends MToMModel {

    public $table = 'message_for_user_to_user';

    public $fieldNamesForReferencedClasses = [
        'message_for_user_id' => MessageForUser::class,
        'user_id' => User::class
    ];

    protected function init(): void {
        parent::init();
        $this->addFields(
            [
                [
                    'is_read',
                    'type' => 'integer',
                    'caption' => 'wurde von Benutzer gelesen'
                ],
            ]
        );
    }
}