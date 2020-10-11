<?php declare(strict_types=1);

namespace PMRAtk\Data;

use notificationforatk\ModelWithNotificationTrait;


class Phone extends SecondaryModel
{
    use ModelWithNotificationTrait;

    public $table = 'phone';
}