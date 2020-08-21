<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;

use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;
use PMRAtk\tests\phpunit\TestCase;


/**
 *
 */
class CreatedDateAndLastUpdatedTraitTest extends TestCase {

    /**
     *
     */
    public function testCreatedDateAndLastUpdated() {
        $currentDateTime = new \DateTime();
        $model = new BaseModelB(self::$app->db);
        $model->save();

        self::assertEquals(
            $currentDateTime->format(DATE_ATOM),
            $model->get('created_date')->format(DATE_ATOM)
        );
        self::assertNull($model->get('last_updated'));

        sleep(1);

        $model->set('name', 'someName');
        $model->save();
        $newDateTime = new \DateTime();

        self::assertNotEquals(
            $newDateTime->format(DATE_ATOM),
            $model->get('created_date')->format(DATE_ATOM)
        );
        $newDateTime = new \DateTime();
        self::assertEquals(
            $newDateTime->format(DATE_ATOM),
            $model->get('last_updated')->format(DATE_ATOM)
        );
    }
    /**/
}