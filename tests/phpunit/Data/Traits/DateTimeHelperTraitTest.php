<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use PMRAtk\Data\Traits\DateTimeHelpersTrait;
use PMRAtk\tests\phpunit\TestCase;

/**
 * Class DTHTest
 */
class DTHTest {
    use DateTimeHelpersTrait;
}


/**
 * Class DateTimeHelperTraitTest
 */
class DateTimeHelperTraitTest extends TestCase {

    /*
     *
     */
    public function testGetDiffMinutes() {
        $d1 = new \DateTime();
        $d2 = clone $d1;
        $dth = new DTHTest();
        $this->assertEquals(0, $dth->getDateDiffTotalMinutes($d1, $d2));
        $d2->modify('+100 Days');
        $this->assertEquals(100*24*60, $dth->getDateDiffTotalMinutes($d1, $d2));
    }


    /*
     *
     */
    public function testDateCasting() {
        $m = new DTHTest();
        $this->assertEquals((new \DateTime())->format('d.m.Y H:i:s'), $this->callProtected($m, 'castDateTimeToGermanString', [new \DateTime(), 'datetime']));
        $this->assertEquals((new \DateTime())->format('d.m.Y'), $this->callProtected($m, 'castDateTimeToGermanString', [new \DateTime(), 'date']));
        $this->assertEquals((new \DateTime())->format('H:i:s'), $this->callProtected($m, 'castDateTimeToGermanString', [new \DateTime(), 'time']));
        $this->assertEquals('',                                       $this->callProtected($m, 'castDateTimeToGermanString', [new \DateTime(), 'lalala']));
    }


    /*
     *
     */
    public function testNoDateTimeInterFaceValue() {
        $m = new DTHTest();
        $this->assertEquals('lala', $this->callProtected($m, 'castDateTimeToGermanString', ['lala', 'datetime']));
    }
}
