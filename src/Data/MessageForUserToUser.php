<?php declare(strict_types=1);

namespace PMRAtk\Data;

use mtomforatk\MToMModel;


class MessageForUserToUser extends MToMModel {

    public $table = 'message_for_user_to_user';

    public $fieldNamesForReferencedClasses = [
        'message_for_user_id' => MessageForUser::class,
        'user_id' => User::class
    ];
}