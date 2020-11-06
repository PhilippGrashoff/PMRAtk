<?php declare(strict_types=1);

namespace PMRAtk\Data;

use notificationforatk\ModelWithNotificationTrait;


class Email extends SecondaryModel
{
    use ModelWithNotificationTrait;

    public $table = 'email';

    public $caption = 'Email-Adresse';

    protected function init(): void
    {
        parent::init();

        $this->addNotificationReferenceAndHooks();
    }

    protected function _checkNotifications(): void
    {
        if(
            $this->get('value')
            && !filter_var($this->get('value'), FILTER_VALIDATE_EMAIL)
        ) {
            $this->createNotification('INCORRECT_EMAIL_FORMAT', 'Die Email ' . $this->get('value') . ' hat ein ungültiges Format!');
        }
        else {
            $this->deleteNotification('INCORRECT_EMAIL_FORMAT');
        }
    }
}