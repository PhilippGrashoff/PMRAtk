<?php declare(strict_types=1);

namespace PMRAtk\Data;

use DateTime;
use DateTimeInterFace;
use PMRAtk\Data\Traits\CryptIdTrait;

class Token extends SecondaryBaseModel {

    use CryptIdTrait;

    public $table = 'token';

    public $expiresInMinutes;

    public $tokenLength = 64;


    /*
     *
     */
    public function init(): void {
        parent::init();
        $this->addField('expires', ['type' => 'datetime']);

        //before insert, create token string
        $this->onHook('beforeSave', function($m) {
            if(!$m->get('value')) {
                $m->setCryptId('value');
            }
            //set expiration on insert
            if(!$m->get('expires') && $m->expiresInMinutes > 0) {
                $m->set('expires', (new DateTime())->modify('+'.$m->expiresInMinutes.' Minutes'));
            }
        });


        //if token is expired do not load but throw exception
        $this->onHook('afterLoad', function($m) {
            if($m->get('expires') instanceOf DateTimeInterFace
            && $m->get('expires') < new DateTime()) {
                throw new UserException('Das Token ist abgelaufen.');
            }
        });

    }


    /*
     * returns a long random token, $this->tokenLength long
     */
    protected function _generateCryptId():string {
        $return = '';
        for($i = 0; $i < $this->tokenLength; $i++) {
            $return .= $this->getRandomChar();
        }

        return $return;
    }
}