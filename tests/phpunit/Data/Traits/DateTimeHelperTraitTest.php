<?php

class DTHTest {
    use \PMRAtk\Data\Traits\DateTimeHelpersTrait;
}


class DateTimeHelperTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    public function testGetDiffMinutes() {
        $d1 = new \DateTime();
        $d2 = clone $d1;

        $dth = new DTHTest();

        $this->assertEquals(0, $dth->getDateDiffTotalMinutes($d1, $d2));

        $d2->modify('+100 Days');

        $this->assertEquals(100*24*60, $dth->getDateDiffTotalMinutes($d1, $d2));
    }
}
