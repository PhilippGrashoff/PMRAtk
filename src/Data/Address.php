<?php declare(strict_types=1);

namespace PMRAtk\Data;

use notificationforatk\ModelWithNotificationTrait;
use PMRAtk\Data\SecondaryModel;


class Address extends SecondaryModel
{
    use ModelWithNotificationTrait;

    public $table = 'address';
}