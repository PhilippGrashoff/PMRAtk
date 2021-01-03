<?php declare(strict_types=1);

namespace PMRAtk\Data;

use notificationforatk\ModelWithNotificationTrait;


class Address extends SecondaryModel
{
    use ModelWithNotificationTrait;

    public $table = 'address';

    public $caption = 'Adresse';
}