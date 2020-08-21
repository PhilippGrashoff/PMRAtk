<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use PMRAtk\Data\CachedValue;
use PMRAtk\tests\phpunit\TestCase;

class CachedValueTest extends TestCase {

    /**
     * make sure setting is only saved once
     */
    public function testUnique() {
        $initial_count = (new CachedValue(self::$app->db))->action('count')->getOne();
        $cachedValue = new CachedValue(self::$app->db);
        $cachedValue->set('ident', 'LALA');
        $cachedValue->set('value', '1');
        $cachedValue->save();

        $cachedValue = new CachedValue(self::$app->db);
        $cachedValue->set('ident', 'LALA');
        $cachedValue->set('value', '2');
        $cachedValue->save();

        $this->assertEquals($initial_count + 1, (new CachedValue(self::$app->db))->action('count')->getOne());
    }


    /**
     * Extra check of last_updated.
     */
    public function testLastUpdatedIsUpdated() {
        $cachedValue = new CachedValue(self::$app->db);
        $cachedValue->set('ident', 'LALA');
        $cachedValue->set('value', '1');
        $cachedValue->save();
        $cachedValue->set('value', '2');
        $cachedValue->save();
        $lastUpdated = $cachedValue->get('last_updated');
        sleep(1);
        $cachedValue->set('value', '3');
        $cachedValue->save();
        $newLastUpdated = $cachedValue->get('last_updated');
        self::assertNotSame(
            $lastUpdated,
            $newLastUpdated
        );
    }
}
