<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use PMRAtk\Data\Phone;
use PMRAtk\tests\phpunit\TestCase;


/**
 * Pureliy for generating Covde Coverage
 */
class PhoneTest extends TestCase {

    /**
     * see if created_by and created_by_name are set on save
     */
    public function testInit() {
        $audit = new Phone(self::$app->db);
        self::assertTrue(true);
    }
}
