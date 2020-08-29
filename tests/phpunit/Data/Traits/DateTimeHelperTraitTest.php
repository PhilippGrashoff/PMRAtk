<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use atk4\data\Model;
use PMRAtk\Data\Traits\DateTimeHelpersTrait;
use PMRAtk\tests\phpunit\TestCase;


class DateTimeHelperTraitTest extends TestCase {

    public function testGetDiffMinutes() {
        $d1 = new \DateTime();
        $d2 = clone $d1;
        $dth = $this->getTestClass();
        $this->assertEquals(0, $dth->getDateDiffTotalMinutes($d1, $d2));
        $d2->modify('+100 Days');
        $this->assertEquals(100*24*60, $dth->getDateDiffTotalMinutes($d1, $d2));
    }

    public function testDateCasting() {
        $dth = $this->getTestClass();
        $this->assertEquals(
            (new \DateTime())->format('d.m.Y H:i:s'),
            $dth->castDateTimeToGermanString($dth->getField('datetime'))
        );
        $this->assertEquals(
            (new \DateTime())->format('d.m.Y'),
            $dth->castDateTimeToGermanString($dth->getField('date'))
        );
        $this->assertEquals(
            (new \DateTime())->format('H:i:s'),
            $dth->castDateTimeToGermanString($dth->getField('time'))
        );
        $this->assertEquals(
            '',
            $dth->castDateTimeToGermanString($dth->getField('some_other_field'))
        );
    }

    public function testNoDateTimeInterFaceValue() {
        $dth = $this->getTestClass();
        $dth->set('some_other_field', 'lala');
        $this->assertEquals(
            'lala',
            $dth->castDateTimeToGermanString($dth->getField('some_other_field'))
        );
    }

    protected function getTestClass(): Model {
        $class = new class() extends Model {

            use DateTimeHelpersTrait;

            public $table = 'some_table';

            public function init(): void
            {
                parent::init();
                $this->addField('datetime', ['type' => 'datetime']);
                $this->addField('date', ['type' => 'date']);
                $this->addField('time', ['type' => 'time']);
                $this->addField('some_other_field');

                $this->set('datetime', new \DateTime());
                $this->set('date', new \DateTime());
                $this->set('time', new \DateTime());
            }
        };

        return new $class(self::$app->db);
    }
}
