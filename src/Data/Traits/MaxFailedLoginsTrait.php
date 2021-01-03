<?php

declare(strict_types=1);

namespace PMRAtk\Data\Traits;


trait MaxFailedLoginsTrait
{

    public $maxFailedLogins = 10;

    protected function _addFailedLoginsField()
    {
        $this->addField(
            'failed_logins',
            [
                'type' => 'integer',
                'caption' => 'Gescheiterte Login-Versuche seit letztem erfolgreichen Login',
                'default' => 0,
                'system' => true,
            ]
        );
    }

    public function addFailedLogin(bool $save = true)
    {
        $this->set('failed_logins', $this->get('failed_logins') + 1);
        if ($save) {
            $this->save();
        }
    }

    public function setFailedLoginsToZero(bool $save = true)
    {
        $this->set('failed_logins', 0);
        if ($save) {
            $this->save();
        }
    }

    public function hasTooManyFailedLogins(): bool
    {
        if ($this->get('failed_logins') > $this->maxFailedLogins) {
            return true;
        }

        return false;
    }

    public function getRemainingLogins(): int
    {
        return (int)$this->maxFailedLogins - $this->get('failed_logins');
    }
}